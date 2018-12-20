<?php

abstract class Gateway3D_PersonaliseIt_Model_Cron_Abstract
{
	const PRINT_JOB_ID_CUSTOM_OPTION_SKU = "printJobId";
	const PRINT_JOB_REF_CUSTOM_OPTION_SKU = "printJobRef";

	const EXTERNAL_URL_CUSTOM_OPTION_SKU = "external_url";

	protected static function _getOrderCollection()
	{
		return Mage::getResourceModel('sales/order_collection')
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
	}
	
	protected static function _getItemPrintJobId($storeId, Mage_Sales_Model_Order_Item $item)
	{
		return self::_getItemCustomOption($storeId, $item, self::PRINT_JOB_ID_CUSTOM_OPTION_SKU);
	}

	protected static function _getItemExternalUrl($storeId, Mage_Sales_Model_Order_Item $item)
	{
		return self::_getItemCustomOption($storeId, $item, self::EXTERNAL_URL_CUSTOM_OPTION_SKU);
	}

	protected static function _getItemCustomOption($storeId, Mage_Sales_Model_Order_Item $item, $sku)
	{
		$options = self::_getItemProduct($storeId, $item)->getOptions();
		
		foreach($options as $option)
		{
			if($option->getSku() == $sku)
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
					return $option['value'];
				}
			}
		}
		
		return null;
	}
	
	protected static function _log($message)
	{
		$message = "SL :: {$message}";
		
		if(getenv('LOG_TO_STDOUT'))
		{
			echo "{$message}\n";
		}
		
		Mage::log($message);
	}
	
	/**
	 * Load a product.
	 * 
	 * @param int $storeId
	 * @param Mage_Sales_Model_Order_Item $item
	 * @return Mage_Catalog_Model_Product
	 */
	protected static function _getItemProduct($storeId, Mage_Sales_Model_Order_Item $item)
	{
		return Mage::getModel('catalog/product')
					->setStoreId($storeId)
					->load($item->getProductId());		
	}
}
