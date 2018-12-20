<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
class Gateway3D_PersonaliseIt_Model_Product_Epa
	extends Gateway3D_PersonaliseIt_Model_Product_Abstract
{
	public function getData()
	{
		$data = $this->_getProductData($this->_product);
		$data['grouped'] = array();
		
		//
		if($this->_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
		{
			$cheapestPrice = INF;
			
			$associatedProducts = $this->_product
							->getTypeInstance(true)
							->getAssociatedProducts($this->_product);
		
			foreach($associatedProducts as $associatedProduct)
			{			
				$_associatedProduct = Mage::getModel('catalog/product');		
				$_associatedProduct->load($associatedProduct->getId());
				
				$data['grouped'][] = $this->_getProductData($_associatedProduct);
				
				// Grouped products don't have prices so use the cheapest
				// associated product.
				//
				// Note that we don't compare on $data['price'] because it might
				// be formatted with currency symbols etc
				if($associatedProduct->getPrice() <= $cheapestPrice)
				{
					$data['price'] = $this->_getPrice($associatedProduct);
					$cheapestPrice = $associatedProduct->getPrice();
				}
			}
		}
		
		//
		return $data;
	}
	
	private function _getProductData($product)
	{
		$tiers = array();
		$options = array();
		
		// Tier prices
		foreach($product->getTierPrice() as $tier)
		{	
			$tiers[] = array(
				'quantity'	=> $tier['price_qty'],
				'price'		=> $this->_formatPrice($tier['price'])
			);
		}
		
		// Drop down custom options
		foreach($product->getOptions() as $option) 
		{
			if($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
			{
				$values = array();

				foreach($option->getValues() as $value)
				{
					$values[] = array(
						'id'		=> $value->getId(),
						'name'		=> $value->getTitle(),
						'sku'		=> $value->getSku(),
						'price'		=> $this->_formatPrice($value->getPrice()),
						
						'minimum_quantity'	=> $product->getStockItem()->getMinSaleQty(),
						'maximum_quantity'	=> $product->getStockItem()->getMaxSaleQty(),
					);
				}

				$options[] = array(
					'id'		=> $option->getId(),
					'name'		=> $option->getTitle(),
					'values'	=> $values
				);
			}
		}
		
		// average user rating
		$storeId = Mage::app()->getStore()->getId();
		
		$summaryData = Mage::getModel('review/review_summary')
						->setStoreId($storeId)
						->load($product->getId());
	 
		$rating = $summaryData->getRatingSummary()
					? $summaryData->getRatingSummary() / 100
					: 0.0;
		
		//
		return array(
			'id'			=> $product->getId(),
			'sku'			=> $product->getSku(),
			
			'price'			=> $this->_getPrice($product),
				
			'name'			=> $product->getName(),
			'description'	=> $product->getDescription(),
			'rating'		=> $rating,
			
			'minimum_quantity'	=> $product->getStockItem()->getMinSaleQty(),
			'maximum_quantity'	=> $product->getStockItem()->getMaxSaleQty(),
			
			'is_new'		=> self::_isProductNew($product),
			
			'options'		=> $options,
			'tiers'			=> $tiers,
		);
	}
	
	private function _getPrice($product)
	{
		return $this->_formatPrice($product->getPrice());
	}
	
	private function _formatPrice($price)
	{
		$price = number_format($price,  2);
		
		return Mage::helper('core')
				->currency($price, true, false);
	}
	
	/**
	 * Calculates whether a product is new.
	 * 
	 * @param Mage_Catalog_Model_Product $product
	 * @return boolean
	 */
	private static function _isProductNew(Mage_Catalog_Model_Product $product)
	{
		if($product->getNewsFromDate()) 
		{
			return Mage::app()->getLocale()->isStoreDateInInterval(
						Mage::app()->getStore(), 
						$product->getNewsFromDate(), 
						$product->getNewsToDate()
					);
		}
		else
		{
			return false;
		}
	}
}