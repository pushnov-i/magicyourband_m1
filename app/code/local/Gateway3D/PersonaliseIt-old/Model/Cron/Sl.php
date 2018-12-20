<?php

class Gateway3D_PersonaliseIt_Model_Cron_Sl
{
	const PRINT_JOB_ID_CUSTOM_OPTION_SKU = "printJobId";
	
	/**
	 * @var Gateway3D_PersonaliseIt_Model_Sl_Api
	 */
	private $_api;
	
	public function start()
	{
		self::_log("Personalise-iT Cron Started");
		
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
		$collection = Mage::getResourceModel('sales/order_collection')
						->addAttributeToSelect('*')
				
						// Include orders that have been completed because
						// orders with 100% discount codes go straight to
						// complete and bypass processing.
						->addFieldToFilter('status', array(
							array('eq' => Mage_Sales_Model_Order::STATE_PROCESSING), 
							array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE))
						)
				
						->addFieldToFilter('sent', 0)
						->addOrder('created_at', 'desc');
		
		foreach($collection as $order)
		{
			$this->_pushMageOrder($order);
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
		
		$groupedItems = $this->_createSlOrderItems($order->getStoreId(), $items);
		
		if(!count($groupedItems))
		{
			self::_log("All items have already been sent");
		}
		
		$slIds = array();
		
		// Now we create an order for each company ref ID grouping.
		foreach($groupedItems as $guid => $itemPairs)
		{
			// Extract all SL order items from the item pairs.
			$_items = array();
			
			foreach($itemPairs as $itemPair)
			{
				$_items[] = $itemPair[1];
			}
			
			// Create an SL order
			$_order = $this->_createSlOrder($order, $guid, $_items);
			
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
				
				self::_log("Pushed {$order->getIncrementId()} -> {$guid}, id:{$_order->id}, ref:{$_order->ref}");
			}
			catch(Gateway3D_PersonaliseIt_Model_Sl_Api_Exception $e)
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
	 * Creates a collection of SL order items from their Magento order item
	 * counterparts, grouped by company.
	 * 
	 * @param Mage_Sales_Model_Order_Item[] $items
	 * @return Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item[]
	 */
	private function _createSlOrderItems($storeId, array $items)
	{
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
			$sampleRef = $product->personaliseit_pod_ref;
			$isPlain = $product->personaliseit_is_plain;

			if(($printJobId || $sampleRef || $isPlain) && !$item->sl_order_id)
			{
				// We also need to group the item by the product's company ref ID.				
				$guid = $product->personaliseit_company_ref_id;
				
				if(!isset($groupedItems[$guid]))
				{
					$groupedItems[$guid] = array();
				}
				
				$groupedItems[$guid][] = array($item, $this->_createSlItem($storeId, $item, $printJobId, $sampleRef));
			}
		}
		
		return $groupedItems;
	}
	
	/**
	 * Creates a single SL order item from a Magento order item.
	 * 
	 * @param Mage_Sales_Model_Order_Item $item
	 * @param string $printJobId
	 * @param string $sampleRef
	 * @return \Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item
	 */
	private function _createSlItem($storeId, Mage_Sales_Model_Order_Item $item, $printJobId, $sampleRef)
	{
		$product = self::_getItemProduct($storeId, $item);
		
		$_item = new Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item;
				
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
		
		//
		$options = $item->getProductOptionByCode('attributes_info');
		
		foreach($options as $option)
		{
			$name = strtolower($option['label']);
			
			if($name == 'color' || $name == 'colour' || $name == 'size')
			{
				$name = $name == 'color' ? 'colour' : $name;
				
				$_item->{$name} = $option['value'];
			}
		}
		
		//
		$attributes = $product->getAttributes();
		
		foreach($attributes as $attribute)
		{
			$name = $attribute->getName();			
			
			if($name == 'color' || $name == 'colour' || $name == 'size')
			{
				$name = $name == 'color' ? 'colour' : $name;
				
				$_item->{$name} = $attribute->getFrontend()->getValue($product);
			}
		}
		
		//
		if($printJobId)
		{
			$_item->type = Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item::TYPE_PRINT_JOB;
		}
		else if($sampleRef)
		{
			$_item->type = Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item::TYPE_PRINT_ON_DEMAND;
		}
		else
		{
			$_item->type = Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item::TYPE_PLAIN;
			
			$_item->external_url = $product->getImageUrl();
			$_item->external_thumbnail_url = $product->getThumbnailUrl(100, 100);
		}

		return $_item;
	}
	
	/**
	 * Creates a single SL order from a Magento order.
	 * 
	 * @param Mage_Sales_Model_Order $order
	 * @param string $companyRefId
	 * @param Gateway3D_PersonaliseIt_Model_Sl_Data_Order_Item[] $items
	 * @return \Gateway3D_PersonaliseIt_Model_Sl_Data_Order
	 */
	private function _createSlOrder(Mage_Sales_Model_Order $order, $companyRefId, array $items)
	{
		$shippingAddress = $order->getShippingAddress();
		$billingAddress = $order->getBillingAddress();
			
		$store = Mage::app()->getStore($order->getStoreId())->getWebsite()->getName();
			
		//
		$_order = new Gateway3D_PersonaliseIt_Model_Sl_Data_Order;
			
		$_order->additional_info = php_uname('n') . " :: " . $store;
			
		$this->_setAddressFields($_order, $billingAddress, "billing_address");
		
		$_order->billing_country = trim($billingAddress->getCountry());
		$_order->billing_postcode = trim($billingAddress->getPostcode());
		
		$this->_setAddressFields($_order, $shippingAddress, "shipping_address");		
		
		$_order->shipping_country = trim($shippingAddress->getCountry());
		$_order->shipping_country_code = $shippingAddress->getCountryModel()->getIso2Code();
		$_order->shipping_postcode = trim($shippingAddress->getPostcode());
		
		list($carrier, $method) = explode(' - ', $order->getShippingDescription());
		
		$_order->shipping_carrier = $carrier;
		$_order->shipping_method = $method;
		
		$_order->customer_email = $order->getCustomerEmail();		
		$_order->customer_name = $shippingAddress->getName();
			
		$_order->external_ref = $order->getIncrementId();
		
		$order->getCreatedAtDate()->setOptions(array('format_type' => 'php'));
		$_order->sale_datetime = $order->getCreatedAtDate()->toString("Y-m-d H:i:s");
		
		$_order->purchase_complete = 1;
		$_order->payment_type = $order->getPayment()->getMethodInstance()->getTitle();
		
		$_order->company_ref_id = $companyRefId;
		
		$_order->status = Mage::getStoreConfig("general/personaliseit-sl/default_order_status");
		
		if(!Mage::getStoreConfig("general/personaliseit-sl/enable_order_status_polling"))
		{
			$_order->status_callback_url = $this->_createSlOrderStatusCallbackUrl($order);
		}
		
		$_order->items = $items;			
		
		return $_order;
	}
	
	private function _setAddressFields(
		Gateway3D_PersonaliseIt_Model_Sl_Data_Order $order, 
		Mage_Customer_Model_Address_Abstract $address, 
		$fieldPrefix)
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
			Gateway3D_PersonaliseIt_Model_Sl_Data_Order::STATUS_DISPATCHED,
			Gateway3D_PersonaliseIt_Model_Sl_Data_Order::STATUS_SUPPLIER_DISPATCHED_NOTIFIED
		);
		
		// We nw retrieve the status of each SL order. If they have all been
		// dispatched then we can ship the Mage order.
		foreach($slOrderIds as $id)
		{
			$_order = new Gateway3D_PersonaliseIt_Model_Sl_Data_Order;
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
			$this->_api = new Gateway3D_PersonaliseIt_Model_Sl_Api($path, $key);
		}
		else
		{
			throw new Exception('No SL API key / path set');
		}
	}
	
	private static function _getItemPrintJobId($storeId, Mage_Sales_Model_Order_Item $item)
	{
		$options = self::_getItemProduct($storeId, $item)->getOptions();
		
		foreach($options as $option)
		{
			if($option->getSku() == self::PRINT_JOB_ID_CUSTOM_OPTION_SKU)
			{
				$id = $option->getId();
			}
		}
		
		if(isset($id) && $id)
		{
			$options = $item->getProductOptions();
			$options = $options['options'];

			foreach($options as $option)
			{
				if($option['option_id'] == $id)
				{
					return (int)$option['value'];
				}
			}
		}
		
		return null;
	}
	
	private static function _log($message)
	{
		$message = "SL :: {$message}";
		
		if(getenv('LOG_TO_STDOUT'))
		{
			echo "{$message}\n";
		}
		
		Mage::log($message);
	}
	
	/**
	 * Shim for older versions of Magento which don't have Mage_Sales_Model_Order_Item::getProduct
	 * 
	 * @param Mage_Sales_Model_Order_Item $item
	 * @return Mage_Catalog_Model_Product
	 */
	private static function _getItemProduct($storeId, Mage_Sales_Model_Order_Item $item)
	{
		$product = $item->getProduct();
		
		return $product
				? $product
				: $product = Mage::getModel('catalog/product')
								->setStoreId($storeId)
								->load($item->getProductId());		
	}
}