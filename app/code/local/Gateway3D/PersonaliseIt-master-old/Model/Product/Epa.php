<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Model_Product_Epa
	extends Gateway3D_PersonaliseIt_Model_Product_Abstract
{
	public function getData()
	{
		$data = $this->_getProductData($this->_product);

		$data['dynamic_pricing'] = $this->_getProductDynamicPricing($this->_product);
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

				$data['grouped'][] = $this->_getProductData($_associatedProduct) + array(
					'dynamic_pricing' => $this->_getProductDynamicPricing($_associatedProduct)
				);
				
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

			if($this->_getIsMagecompMqgpEnabled())
			{
				$data['minimum_quantity_combined'] = (int)$this->_product->getCustomminqty();
			}
		}

		//
		return $data;
	}

	private function _getIsMagecompMqgpEnabled()
	{
		return
			Mage::helper('core')->isModuleEnabled('Magecomp_Mqgp') && 
			Mage::getStoreConfig('minqty/general/enabled', Mage::app()->getStore());
	}
	
	/**
	 * Get pricing information used for dynamic pricing.
	 *
	 * @param Mage_Core_Catalog_Model_Product $product
	 * @return array
	 */
	private function _getProductDynamicPricing(Mage_Catalog_Model_Product $product)
	{
		$tiers = array();

		foreach($product->getTierPrice() as $tier)
		{
			$tiers[] = array(
				'quantity'	=> $tier['price_qty'],
				'price'		=> $this->_currency($tier['price'])
			);
		}

		return array(
			'currency'				=> Mage::app()->getStore()->getCurrentCurrencyCode(),

			'base'					=> $this->_currency($product->getPrice()),

			'tiers'					=> $tiers,

			'per_print_area'		=> $this->_getProductDynamicPricingField(
										$product, 'Number of Print Areas', Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN),

			'per_detected_colour'	=> $this->_getProductDynamicPricingField(
										$product, 'Number of Image Colours', Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN),

			'per_stitch'			=> $this->_getProductDynamicPricingField(
										$product, 'Number of Stitches', Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN),

			'effects'				=> $this->_getProductDynamicPricingField(
										$product, 'Effects', Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE),

			'print_areas'			=> $this->_getProductDynamicPricingField(
										$product, 'Print Areas', Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE),

			'text_areas'			=> $this->_getProductDynamicPricingField(
										$product, 'Text Areas', Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE),
			
			'image_areas'			=> $this->_getProductDynamicPricingField(
										$product, 'Image Areas', Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE),

			'attributes'			=> $this->_getProductAttributes($product)
		);
	}

	private function _getProductAttributes(Mage_Catalog_Model_Product $product)
	{
		$attributes = array();

		foreach($product->getOptions() as $option)
		{
			if($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
			{
				foreach($option->getValues() as $value)
				{
					$attributes[] = array(
						'name'		=> $option->getTitle(),
						'value'		=> $value->getTitle(),
						'price'		=> $this->_currency($value->getPrice())
					);
				}
			}
		}

		return $attributes;
	}

	/**
	 * Gets a pricing structure for a matching custom option.
	 *
	 * @param Mage_Core_Catalog_Model_Product $product
	 * @param string $name Custom option title.
	 * @param string $type Custom option type.
	 * @return array|bool
	 */
	private function _getProductDynamicPricingField(Mage_Catalog_Model_Product $product, $name, $type)
	{
		foreach($product->getOptions() as $option)
		{
			$doesTitleMatch = $option->getTitle() == $name;
			$doesTypeMatch = $option->getType() == $type;

			$isDropDown = $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN;
			$isMulti = $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE;

			// In this case of a dropdown custom option, we assume that each 
			// option is a quantity and therefore only return a single value.
			if($doesTypeMatch && $doesTitleMatch && $isDropDown)
			{
				foreach($option->getValues() as $value)
				{
					$title = $value->getTitle();
					
					if(is_numeric($title) && $title > 0)
					{
						return $this->_currency($value->getPrice() / $title);
					}
				}
			}

			// In the case of a multiple custom option, we return the price 
			// of each possible value.
			else if($doesTypeMatch && $doesTitleMatch && $isMulti)
			{
				$options = array();

				foreach($option->getValues() as $value)
				{
					$options[] = array(
						'name'	=> $value->getTitle(),						
						'price'	=> $this->_currency($value->getPrice())
					);
				}

				return array_values($options);
			}
		}

		return false;
	}

	/**
	 * Gets the pricing structure for a given product.
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return array
	 */
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
			
			'increment_quantity'=> $product->getStockItem()->getEnableQtyIncrements()
									? $product->getStockItem()->getQtyIncrements()
									: 1,
			
			'is_new'		=> self::_isProductNew($product),
			
			'options'		=> $options,
			'tiers'			=> $tiers,
		);
	}
	
	/**
	 * Gets a formatted price for a product.
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return string
	 */
	private function _getPrice($product)
	{
		return $this->_formatPrice($product->getFinalPrice());
	}
	
	/**
	 * Gets a price formatted according to Magento's current currency.
	 *
	 * @param mixed $price
	 * @return string
	 */
	private function _formatPrice($price)
	{
		return $this->_currency($price, true);
	}

	private function _currency($price, $format = false)
	{
		return Mage::helper('core')->currency($price, $format, false);
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
