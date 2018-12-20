<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

/**
 * Cron class that is responsible for inserting orders into the Gateway3D
 * Order-iT system using the Supplier Link API.
 */
class Gateway3D_PersonaliseIt_Model_Cron_Sl
	extends Gateway3D_PersonaliseIt_Model_Cron_Abstract
{
	/**
	 * @var Gateway3D_PersonaliseIt_Service_Api_Sl_Order
	 */
	private $_api;

	public function start()
	{
		self::_log("Personalise-iT SL Cron Started");

		$this->_checkOtherSupplierLinks();
		$this->_initApi();

		$this->_push();

		if(Mage::getStoreConfig("general/personaliseit-sl/enable_order_status_polling"))
		{
			$this->_pull();
		}

		self::_log("Finished");
	}

	/**
	 * Pushes all possible Magento orders via the Supplier Link API.
	 */
	private function _push()
	{
		$collection = self::_getOrderCollection();

		foreach($collection as $order)
		{
			$order->setPersonaliseItOrderCanBeSent(true);

			Mage::dispatchEvent('personaliseit_before_order_sent', array(
				'order' => $order
			));

			if($order->getPersonaliseItOrderCanBeSent())
			{
				$this->_pushMageOrder($order);

				Mage::dispatchEvent('personaliseit_after_order_sent', array(
					'order' => $order
				));
			}
			else
			{
				self::_log("skipping {$order->getIncrementId()} due to event");
			}
		}

		if(!count($collection))
		{
			self::_log("Nothing to push");
		}
	}

	/**
	 * Pulls statuses from the Supplier Link API for all possible Magento orders.
	 */
	private function _pull()
	{
		$collection = Mage::getResourceModel('sales/order_collection')
						->addAttributeToSelect('*')
						->addFieldToFilter('shipped', 0)
						->addFieldToFilter('sent', 1)
						->addOrder('created_at', 'desc');

		foreach($collection as $order)
		{
			$this->_pullMageOrder($order);
		}

		if(!count($collection))
		{
			self::_log("Nothing to pull");
		}
	}

	/**
	 * Push a single Magento order through the Supplier Link API.
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
	private function _pushMageOrder(Mage_Sales_Model_Order $order)
	{
		self::_log("Pushing {$order->getIncrementId()}");

		$items = $order->getAllVisibleItems();

		// Check that any delayed preview items have had a print job generated
		if(!$this->_doAllDelayedPreviewItemsHavePrintJobIds($order, $items))
		{
			$message = "Order has a delayed preview item that does not yet have a print job ID";

			self::_log($message);
			$order->addStatusHistoryComment("Personalise-iT: {$message}");
			$order->save();

			return;
		}

		//
		$groupedItems = $this->_createSlOrderItems($order, $items);

		if(!count($groupedItems))
		{
			self::_log("All items have already been sent");
		}

		$slIds = array();

		// Now we create an order for each company ref ID grouping.
		foreach($groupedItems as $combinedCompanyRefs => $itemPairs)
		{
			// Extract all SL order items from the item pairs.
			$_items = array();

			foreach($itemPairs as $itemPair)
			{
				$_items[] = $itemPair[1];
			}

			$guid = $itemPairs[0][2];
			$companyRefIdTwo = $itemPairs[0][3];

			// Create an SL order
			$_order = $this->_createSlOrder($order, $guid, $companyRefIdTwo, $_items);

			try
			{
				$this->_api->create($_order);

				// Update Magento order items
				foreach($itemPairs as $itemPair)
				{
					$itemPair[0]->sl_order_id = $_order->id;
					$itemPair[0]->save();
				}
				$order->addStatusHistoryComment("Personalise-iT Order Ref: {$_order->ref} ({$guid})");
				$order->save();
				self::_log("Pushed {$order->getIncrementId()} -> {$guid}, id:{$_order->id}, ref:{$_order->ref}, status:{$_order->status}");
			}
			catch(Exception $e)
			{
				$message = "Personalise-iT Error: {$e->getMessage()} ({$guid})";

				$order->addStatusHistoryComment($message);

				// don't retry the order later
				$order->sent = 1;

				$order->save();

				self::_log($message);

				// bail out
				return;

			}
		}

		// Update the magento order
		$order->sent = 1;
		$order->save();
	}

	/**
	 * Determines whether an order's delayed preview items ALL have print job IDs.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param array $items
	 * @return boolean True if all delayed preview items have print job IDs.
	 */
	private function _doAllDelayedPreviewItemsHavePrintJobIds(Mage_Sales_Model_Order $order, array $items)
	{
		foreach($items as $item)
		{
			$storeId = $order->getStoreId();
			$product = self::_getItemProduct($storeId, $item);

			if($product->personaliseit_dp_enabled && !self::_getItemPrintJobId($storeId, $item))
			{
				return false;
			}
		}

		return true;
	}


	/**
	 * Creates a collection of SL order items from their Magento order item
	 * counterparts, grouped by company.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param Mage_Sales_Model_Order_Item[] $items
	 * @return Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item[]
	 */
	private function _createSlOrderItems(Mage_Sales_Model_Order $order, array $items)
	{
		$storeId = $order->getStoreId();

		$groupedItems = array();

		foreach($items as $item)
		{
			$product = self::_getItemProduct($storeId, $item);

			// We only send the item if:
			//
			//		1) The item has a print job ID custom option, or,
			//		2) The item's product has a POD sample ref set.
			//		3) The item is has plain stock set to 'Yes'
			//
			//		AND
			//
			//		4) The item has not already been sent

			$printJobId = self::_getItemPrintJobId($storeId, $item);
			$externalUrl = self::_getItemExternalUrl($storeId, $item);

			$sampleRef = $product->personaliseit_pod_ref;
			$isPlain = $product->personaliseit_is_plain;

			if(($printJobId || $externalUrl || $sampleRef || $isPlain) && !$item->sl_order_id)
			{
				// We also need to group the item by the product's company ref ID.
				$guids = array_values(array_filter(array(
					Mage::getModel('customer/customer')->load($order->getCustomerId())->override_company_ref_id,
					$customer->override_company_ref_id,
					Mage::getStoreConfig("general/personaliseit-sl/override_company_ref_id", $storeId),
					$product->personaliseit_company_ref_id)));


				$guid = $guids ? $guids[0] : '';

				//need to group product by both primary and secondary so combine tehm into one value and group by that
				$companyRefIdTwo = $product->personaliseit_company_ref_id_2;

				$combinedCompanyRefs = $guid . $companyRefIdTwo;

				if(!isset($groupedItems[$combinedCompanyRefs]))
				{
					$groupedItems[$combinedCompanyRefs] = array();
				}

				$groupedItems[$combinedCompanyRefs][] = array($item, $this->_createSlItem($storeId, $item, $printJobId, $externalUrl, $sampleRef), $guid, $companyRefIdTwo);
			}
		}

		return $groupedItems;
	}

	/**
	 * Creates a single SL order item from a Magento order item.
	 *
	 * @param Mage_Sales_Model_Order_Item $item
	 * @param string $printJobId
	 * @param string $externalUrl
	 * @param string $sampleRef
	 * @return \Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item
	 */
	private function _createSlItem($storeId, Mage_Sales_Model_Order_Item $item, $printJobId, $externalUrl, $sampleRef)
	{
		$product = self::_getItemProduct($storeId, $item);

		$_item = new Gateway3D_PersonaliseIt_Service_Dto_Sl_Order_Item;

		if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
		{
			$_item->description = $item->getName();
			$_item->sku = $item->getSku();
		}
		else
		{
			$_item->description = $product->getName();
			$_item->sku = $product->getSku();
		}

		$_item->quantity = $item->getQtyOrdered();
		$_item->print_job_id = $printJobId;
		$_item->print_on_demand_ref = $sampleRef;
		$_item->external_ref = "{$item->getOrder()->getIncrementId()}-{$item->getId()}";

		$_item->unit_sale_price = $item->getPrice();
		$_item->unit_sale_price_inc_tax = $item->getPriceInclTax();
		$_item->sale_vat_rate = $item->getTaxPercent() / 100;

		//
		$options = array_merge(
			$item->getProductOptionByCode('attributes_info') ?: array(),
			$item->getProductOptionByCode('options'));

		if($options)
		{
			foreach($options as $option)
			{
				$name = strtolower($option['label']);

				if($name == 'color' || $name == 'colour' || $name == 'size')
				{
					$name = $name == 'color' ? 'colour' : $name;

					$_item->{$name} = html_entity_decode($option['value']);
				}
			}
		}

		//
		$attributes = $this->_getInterestingItemAttributes($storeId, $item, $product);

		foreach($attributes as $name => $value)
		{
			$_item->{$name} = html_entity_decode($value);
		}

		$bundleRef = self::_getItemCustomOption($storeId, $item, 'bundle_ref');
		$bundleSku = self::_getItemCustomOption($storeId, $item, 'bundle_sku');

		$parts = array_filter(array($bundleSku, $bundleRef));

		$_item->bundle_ref = implode('-', $parts);

		//
		if($printJobId)
		{
			$_item->type = Gateway3D_PersonaliseIt_Service_Dto_Sl_Order_Item::TYPE_PRINT_JOB;
		}
		else if($externalUrl)
		{
			$_item->type = Gateway3D_PersonaliseIt_Service_Dto_Sl_Order_Item::TYPE_EXTERNAL_URL;
			$_item->external_url = $externalUrl;
			$_item->external_thumbnail_url = $externalUrl;
		}
		else if($sampleRef)
		{
			$_item->type = Gateway3D_PersonaliseIt_Service_Dto_Sl_Order_Item::TYPE_PRINT_ON_DEMAND;
		}
		else
		{
			$_item->type = Gateway3D_PersonaliseIt_Service_Dto_Sl_Order_Item::TYPE_PLAIN;

			list($_item->external_url, $_item->external_thumbnail_url) = $this->_resolveItemImages($item, $product);
		}

		return $_item;
	}

	/**
	 * Gets any attributes from the item that we're interested in (i.e. colour and size).
	 *
	 * @param int $storeId
	 * @param Mage_Sales_Model_Order_Item $item
	 * @param Mage_Catalog_Model_Product $product
	 * @return array
	 */
	private function _getInterestingItemAttributes($storeId, Mage_Sales_Model_Order_Item $item, Mage_Catalog_Model_Product $product)
	{
		// For configurable products, we need to get the attributes from the
		// relavent simple.
		if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
		{
			$children = $item->getChildrenItems();

			if(count($children))
			{
				$product = self::_getItemProduct($storeId, $children[0]);
			}
		}

		$attributes = $product->getAttributes();
		$flatAttributes = array();

		foreach($attributes as $attribute)
		{
			$name = $attribute->getName();

			if($name == 'color' || $name == 'colour' || $name == 'size')
			{
				// Force UK spellings
				$name = $name == 'color' ? 'colour' : $name;

				$flatAttributes[$name] = $attribute->getFrontend()->getValue($product);

				// Magento will return "No" if an attribute exists on a product but is not
				// set. Therefore lets try and filter them out
				if($flatAttributes[$name] == Mage::helper('personaliseit')->__('No'))
				{
					unset($flatAttributes[$name]);
				}
			}
		}

		return $flatAttributes;
	}

	/**
	 * Resolves the images to use for a plain stock item.
	 *
	 * @param Mage_Sales_Model_Order_Item $item
	 * @param Mage_Catalog_Model_Product $product
	 * @return array($imageUrl, $thumbnailUrl)
	 */
	private function _resolveItemImages(Mage_Sales_Model_Order_Item $item, Mage_Catalog_Model_Product $product)
	{
		$isConfigurable = $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;

		$configKey = Mage_Checkout_Block_Cart_Item_Renderer_Configurable::CONFIGURABLE_PRODUCT_IMAGE;
		$useParentImage = Mage::getStoreConfig($configKey) == Mage_Checkout_Block_Cart_Item_Renderer_Configurable::USE_PARENT_IMAGE;

		$children = $item->getChildrenItems();

		if($isConfigurable && !$useParentImage && count($children))
		{
			$child = $children[0]->getProduct();
			$thumbnail = $child->getData('thumbnail');

			if($thumbnail && $thumbnail != 'no_selection')
			{
				$product = $child;
			}
		}

		return array(
			$product->getImageUrl(),
			$product->getThumbnailUrl(100, 100)
		);
	}

	/**
	 * Creates a single SL order from a Magento order.
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param string $companyRefId
	 * @param Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item[] $items
	 * @return \Gateway3D_PersonaliseIt_Model_Sl_Data_Order
	 */
	private function _createSlOrder(Mage_Sales_Model_Order $order, $companyRefId, $companyRefIdTwo, array $items)
	{
		$shippingAddress = $order->getShippingAddress();
		$billingAddress = $order->getBillingAddress();

		$store = Mage::app()->getStore($order->getStoreId())->getWebsite()->getName();

		//
		$_order = new Gateway3D_PersonaliseIt_Service_Dto_Sl_Order;

		$_order->additional_info = php_uname('n') . " :: " . $store;

		if($billingAddress)
		{
			$this->_setAddressFields($_order, $billingAddress, "billing_address", $order->getStoreId());

			$_order->billing_country = trim($billingAddress->getCountry());
			$_order->billing_postcode = trim($billingAddress->getPostcode());

			$_order->billing_customer_name = $billingAddress->getName();
			$_order->billing_customer_telephone = $billingAddress->getTelephone();
		}

		if($shippingAddress)
		{
			$this->_setAddressFields($_order, $shippingAddress, "shipping_address", $order->getStoreId());

			$_order->shipping_country = trim($shippingAddress->getCountry());
			$_order->shipping_country_code = $shippingAddress->getCountryModel()->getIso2Code();
			$_order->shipping_postcode = trim($shippingAddress->getPostcode());

			$_order->customer_name = $shippingAddress->getName();
			$_order->customer_telephone = $shippingAddress->getTelephone();
		}

		list($carrier, $method) = explode(' - ', $order->getShippingDescription());

		$_order->shipping_carrier = $carrier;
		$_order->shipping_method = $method;

		$_order->shipping_price = $order->shipping_amount;
		$_order->shipping_price_inc_tax = $order->shipping_incl_tax;
		$_order->shipping_tax_rate = ($order->shipping_incl_tax / $order->shipping_amount) - 1;

		$_order->customer_email = $order->getCustomerEmail();
		$_order->billing_customer_email = $order->getCustomerEmail();

		$_order->external_ref = $order->getIncrementId();

		$order->getCreatedAtDate()->setOptions(array('format_type' => 'php'));
		$_order->sale_datetime = $order->getCreatedAtDate()->toString("Y-m-d H:i:s");

		$payment = $order->getPayment();

		$_order->payment_trans_id = $payment->getPoNumber();
		$_order->payment_type = $payment->getMethodInstance()->getTitle();

		$_order->company_ref_id = $companyRefId;
		$_order->secondary_company_ref_id = $companyRefIdTwo;

		$_order->status = Mage::getStoreConfig("general/personaliseit-sl/default_order_status");
		$_order->tag = Mage::getStoreConfig("general/personaliseit-sl/default_order_tag", $order->getStoreId());

		if(!Mage::getStoreConfig("general/personaliseit-sl/enable_order_status_polling"))
		{
			$_order->status_callback_url = $this->_createSlOrderStatusCallbackUrl($order);
		}

		$_order->items = $items;

		return $_order;
	}

	private function _setAddressFields(
		Gateway3D_PersonaliseIt_Service_Dto_Sl_Order $order,
		Mage_Customer_Model_Address_Abstract $address,
		$fieldPrefix,
		$storeId)
	{
		if(Mage::getStoreConfig("general/personaliseit-sl/strict_addresses", $storeId))
		{
			$fieldPrefix = preg_match("/^shipping/", $fieldPrefix) ? "shipping" : "billing";

			$order->{"{$fieldPrefix}_company"} = $address->getCompany();
			$order->{"{$fieldPrefix}_address_1"} = $address->getStreet1();
			$order->{"{$fieldPrefix}_address_2"} = $address->getStreet2();

			$extra = array($address->getStreet3(), $address->getStreet4());
			$order->{"{$fieldPrefix}_address_3"} = implode(', ', array_filter($extra));

			$order->{"{$fieldPrefix}_address_4"} = $address->getCity();
			$order->{"{$fieldPrefix}_address_5"} = $address->getRegion();
		}
		else
		{
			$lines = $address->getStreet();

			array_unshift($lines, $address->getCompany());
			array_push($lines, $address->getCity());
			array_push($lines, $address->getRegion());

			for($i = 0; $i < count($lines) && $i < 5; $i++)
			{
				if($lines[$i])
				{
					$_i = $i + 1;
					$key = "{$fieldPrefix}_{$_i}";

					$order->{$key} = trim($lines[$i]);
				}
			}
		}
	}

	private function _createSlOrderStatusCallbackUrl(Mage_Sales_Model_Order $order)
	{
		$ref = sha1(uniqid(time(), true));

		$order->status_callback_ref = $ref;

		$url = Mage::getUrl('personaliseit/status/callback/', array(
			'_store'	=> $order->getStoreId(),
			'ref'		=> $ref
		));

		return $url;
	}

	/**
	 *
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
	private function _pullMageOrder(Mage_Sales_Model_Order $order)
	{
		self::_log("Pulling {$order->getIncrementId()}");

		// First we need to get all of the SL order ids that might have been
		// associated with this Magento order.
		$items = $order->getAllVisibleItems();

		$slOrderIds = array();

		foreach($items as $item)
		{
			if($item->sl_order_id)
			{
				$slOrderIds[] = (int)$item->sl_order_id;
			}
			else
			{
				// Bail out early because the order has items that have not been
				// sent via the SL API thus we can't accurately say whether the
				// order should be shipped.

				return;
			}
		}

		$slOrderIds = array_unique($slOrderIds);

		//
		$dispatched = 0;
		$dispatchedStatuses = array(
			Gateway3D_PersonaliseIt_Service_Dto_Sl_Order::STATUS_DISPATCHED,
			Gateway3D_PersonaliseIt_Service_Dto_Sl_Order::STATUS_SUPPLIER_DISPATCHED_NOTIFIED
		);

		// We nw retrieve the status of each SL order. If they have all been
		// dispatched then we can ship the Mage order.
		foreach($slOrderIds as $id)
		{
			$_order = new Gateway3D_PersonaliseIt_Service_Dto_Sl_Order;
			$_order->id = $id;

			$this->_api->retrieve($_order);

			if(in_array($_order->status, $dispatchedStatuses))
			{
				$dispatched++;
			}
		}

		//
		if($dispatched == count($items))
		{
			$this->_ship($order);
		}
	}

	private function _ship(Mage_Sales_Model_Order $order)
	{
		if(!$order->canShip())
		{
			self::_log("Order {$order->getIncrementId()} cannot be shipped");
		}
		else
		{
			$qtys = array();

			foreach($order->getAllItems() as $item)
			{
				$qtys[$item->getId()] = $item->getQtyToShip();
			}

			$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($qtys);

			$shipment->register();

			$shipment->setEmailSent(true);

			$shipment->getOrder()->setCustomerNoteNotify(true);

			$shipment->getOrder()->setIsInProcess(true);

			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($shipment)
				->addObject($shipment->getOrder())
				->save();

			$shipment->sendEmail(true, '');

			$order->shipped = true;
			$order->save();
		}
	}

	/**/
	private function _checkOtherSupplierLinks()
	{
		if(@class_exists('Gateway3D_SupplierLink_Model_Observer') || @class_exists('BNetCentric_SupplierLink_Model_Observer'))
		{
			throw new Exception('Please disable and remove older versions of Supplier Link');
		}
	}

	private function _initApi()
	{
		$path = Mage::getStoreConfig("general/personaliseit-sl/api_path");
		$key = Mage::getStoreConfig("general/personaliseit-sl/api_key");

		if($path && $key)
		{
			$this->_api = new Gateway3D_PersonaliseIt_Service_Api_Sl_Order($path, $key);
		}
		else
		{
			throw new Exception('No SL API key / path set');
		}
	}
}
