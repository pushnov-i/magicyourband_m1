<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

/**
 * Cron class that is responsible for creating print jobs for any delayed preview
 * products that have been purchased but don't yet have a print job ID.
 */
class Gateway3D_PersonaliseIt_Model_Cron_DelayedPrintJob
	extends Gateway3D_PersonaliseIt_Model_Cron_Abstract
{
	const PRINT_JOB_ID_CUSTOM_OPTION_SKU = "printJobId";
	
	/**
	 * @var Gateway3D_PersonaliseIt_Service_Api2_PrintJob
	 */
	private $_api;
	
	public function start()
	{
		self::_log("Personalise-iT Delayed Print Job Cron Started");
		
		$this->_initApi();
		$this->_push();		
		
		self::_log("Finished");
	}
	
	private function _initApi()
	{
		$path = Mage::getStoreConfig("general/personaliseit-delayed-preview/api_path");
		
		if($path)
		{
			$this->_api = new Gateway3D_PersonaliseIt_Service_Api_P2_PrintJob($path);
		}
		else
		{
			Mage::throwException('No delayed preview API path set');
		}
	}
	
	private function _push()
	{
		$collection = self::_getOrderCollection();
		
		foreach($collection as $order)
		{
			$this->_pushOrder($order);
		}
		
		if(!count($collection))
		{
			self::_log("Nothing to do");
		}
	}
	
	private function _pushOrder(Mage_Sales_Model_Order $order)
	{
		$items = $order->getAllVisibleItems();
		
		foreach($items as $item)
		{
			$storeId = $order->getStoreId();
			$product = self::_getItemProduct($storeId, $item);
			
			if($product->personaliseit_dp_enabled && !self::_getItemPrintJobId($storeId, $item))
			{
				self::_log("Attemping to create print job for {$order->getIncrementId()}/{$item->getId()}");
				
				$printJobId = $this->_pushPrintJob($item, $product);
				
				$required = array(self::PRINT_JOB_ID_CUSTOM_OPTION_SKU);
				
				$requiredOptions = Gateway3D_PersonaliseIt_Helper_ProductCustomOptions::ensureProductHasCustomOptions($product, $required);
				$printJobOption = $requiredOptions[0];
				
				$itemOptions = $item->getProductOptions();
				$itemOptions['options'][] = array(
					'label'		=> $printJobOption->getTitle(),
					'value'		=> $printJobId,
					'option_id'	=> $printJobOption->getId(),
				);
						
				$item->setProductOptions($itemOptions);
				$item->save();
				
				self::_log("PJ: {$printJobId}");
			}
		}
	}
	
	private function _pushPrintJob(Mage_Sales_Model_Order_Item $item, Mage_Catalog_Model_Product $product)
	{
		$printJob = $this->_createPrintJob($item, $product);

		$this->_api->create($printJob, $options = array(
			Gateway3D_PersonaliseIt_Service_Api_P2_PrintJob::OPTION_IMPLICIT => true
		));
		
		return $printJob->print_job_id;
	}
	
	private function _createPrintJob(Mage_Sales_Model_Order_Item $item, Mage_Catalog_Model_Product $product)
	{
		$printJob = new Gateway3D_PersonaliseIt_Service_Dto_P2_PrintJob;
		
		$printJob->product_id = $product->personaliseit_dp_pid;
		$printJob->print_job_company_id = $product->personaliseit_company_ref_id;
		
		$printJob->items = $this->_getTextItems($item, $product);

		return $printJob;
	}

	private function _getTextItems(
		Mage_Sales_Model_Order_Item $item, 
		Mage_Catalog_Model_Product $product)
	{
		$items = array();

		foreach($product->getOptions() as $option)
		{
			$_item = $this->_getTextItem($option, $item);

			if($_item)
			{
				$items[] = array(
					'area_id'	=> $_item[0],
					'item_type'	=> 'TEXT_AREA',
					'resources'	=> $_item[1]
				);
			}
		}

		return $items;
	}

	private function _getTextItem(
		Mage_Catalog_Model_Product_Option $option,
		Mage_Sales_Model_Order_Item $item)
	{
		$itemOption = $this->_getItemOption($option, $item);

		if($itemOption)
		{
			if($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
			{
				$sku = "";

				foreach($option->getValues() as $value)
				{
					if($itemOption['option_id'] == $value->option_id)
					{
						$sku = $value->sku;
					}
				}
			}
			else
			{
				$sku = $option->getSku();
			}

			$matches = array();

			if(preg_match('/^userText([0-9]*)$/', $sku, $matches) && count($matches) == 2)
			{
				return array($matches[1], $itemOption['value']);
			}
		}

		return false;
	}

	private function _getItemOption(
		Mage_Catalog_Model_Product_Option $option,
		Mage_Sales_Model_Order_Item $item)
	{
		$itemOptions = $item->getProductOptionByCode('options');

		foreach($itemOptions as $itemOption)
		{
			if($itemOption['option_id'] == $option->getId())
			{
				return $itemOption;
			}
		}
	}
}
