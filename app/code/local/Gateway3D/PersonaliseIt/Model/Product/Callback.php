<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

/**
 * Class for processing a Personalise-iT app add to cart callback.
 */
class Gateway3D_PersonaliseIt_Model_Product_Callback
	extends Gateway3D_PersonaliseIt_Model_Product_Abstract
{
	private $_data;
	
	public function __construct(array $data)
	{
		$this->_data = $data;
	}
	
	public function process($updateCartItemId = false)
	{
		$this->_handleBundleRefs();

		if(isset($this->_data['dynamic_pricing']) && is_array($this->_data['dynamic_pricing']))
		{
			$this->_marshalDynamicPricing();
		}

		$this->_processCart($updateCartItemId);
	}

	/**
	 * Marshal the dynamic_pricing structure into a form that can be understood
	 * by _processCart
	 *
	 * @return void
	 */
	private function _marshalDynamicPricing()
	{
		$dynamicPricing = $this->_data['dynamic_pricing'];

		if(!isset($dynamicPricing['attributes']) || !is_array($dynamicPricing['attributes']))
		{
			$dynamicPricing['attributes'] = array();
		}

		$this->_marshalDpQuantityElementToAttribute(
			$dynamicPricing, 'image_colours', 'Number of Image Colours');

		$this->_marshalDpQuantityElementToAttribute(
			$dynamicPricing, 'print_areas', 'Number of Print Areas');

		$this->_marshalDpQuantityElementToAttribute(
			$dynamicPricing, 'stitching', 'Number of Stitches (1000s)');

		if(isset($dynamicPricing['attributes']))
		{
			$this->_marshalDynamicPricingToDropdowns($dynamicPricing['attributes']);
		}

		if(isset($dynamicPricing['aspect_options']))
		{
			$this->_marshalDynamicPricingToDropdowns($dynamicPricing['aspect_options']);
		}

		if(isset($dynamicPricing['print_size']))
		{
			$this->_marshalDynamicPricingToDropdowns(array($dynamicPricing['print_size']));
		}

		if(isset($dynamicPricing['colours']))
		{
			$this->_marshalDynamicPricingToMultiples('Colours', $dynamicPricing['colours']);
		}

		if(isset($dynamicPricing['effects']))
		{
			$this->_marshalDynamicPricingToMultiples('Effects', $dynamicPricing['effects']);
		}

		if(isset($dynamicPricing['print_areas']['used']))
		{
			$this->_marshalDynamicPricingToMultiples('Print Areas', $dynamicPricing['print_areas']['used']);
		}

		if(isset($dynamicPricing['image_areas']['used']))
		{
			$this->_marshalDynamicPricingToMultiples('Image Areas', $dynamicPricing['image_areas']['used']);
		}

		if(isset($dynamicPricing['text_areas']['used']))
		{
			$this->_marshalDynamicPricingToMultiples('Text Areas', $dynamicPricing['text_areas']['used']);
		}
	}

	private function _marshalDpQuantityElementToAttribute(array &$dp, $element, $name)
	{
		if(isset($dp[$element]) && is_array($dp[$element]))
		{
			$dp['attributes'][] = array(
				'name'	=> $name,
				'value'	=> @$dp[$element]['quantity'] ?: 1,
				'cost'	=> $dp[$element]['cost']
			);
		}
	}

	/**
	 * Ensure that a valid "multiple" custom option exists with the given title
	 * and with the given values.
	 *
	 * This method also adds to _data['options'] for later processing.
	 *
	 * @param string $name Custom option title.
	 * @param array $values Custom option values.
	 * @return void
	 */
	private function _marshalDynamicPricingToMultiples($name, array $values)
	{
		if(count($values))
		{
			$this->_data['options'][$name] = array_map(function($a)
			{
				return $a['name'];
			}, $values);

			// Search for a matching "dropdown" custom option.
			$multiples = array_filter(
							$this->_product->getOptions(), 
							array($this, '_filterOptionMultiple'));

			$matchingOptions = array_values(array_filter($multiples, function($o) use ($name)
			{
				return $o->getTitle() == $name;
			}));
			
			// Create the custom option if it doesn't already exist.
			if(count($matchingOptions) == 0)
			{
				$optionData = array(
					'title'		=> $name,
					'type'		=> Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE,
					'is_require'=> false,
					'sort_order'=> 1,

					'additional_fields' => array()
				);

				// Create values
				foreach($values as $attribute)
				{
					$optionData['additional_fields'][] = array(
						'title'		=> $attribute['name'],
						'price'		=> $attribute['cost'],
						'price_type'=> 'fixed'
					);
				}

				Gateway3D_PersonaliseIt_Helper_ProductCustomOptions::addOption($this->_product, $optionData);
			}

			// A matching custom option exists so let's check that the correct
			// values also exist and create them if not.
			else
			{
				$matchingOption = $matchingOptions[0];

				$optionData = array(
					'additional_fields' => array()
				);

				foreach($values as $attribute)
				{
					// Check whether a value already exists.
					$matchingValues = array_filter($matchingOption->getValues(), function($value) use ($attribute)
					{
						return $value->getTitle() == $attribute['name'];
					});

					// Create a value if one doesn't already exist.
					if(count($matchingValues) == 0)
					{
						$optionData['additional_fields'][] = array(
							'title'		=> $attribute['name'],
							'price'		=> $attribute['cost'],
							'price_type'=> 'fixed'
						);

						Gateway3D_PersonaliseIt_Helper_ProductCustomOptions::update($matchingOption, $optionData);
					}
				}
			}
		}
	}

	/**
	 * Ensure that valid "dropdown" custom options exist for the given collection
	 * of attributes.
	 *
	 * @param array $attributes
	 * @return void
	 */
	private function _marshalDynamicPricingToDropdowns(array $attributes)
	{
		$dropdowns = array_filter(
						$this->_product->getOptions(), 
						array($this, '_filterOptionDropdown'));

		foreach($attributes as $attribute)
		{
			$name = $attribute['name'];
			$value = $attribute['value'];

			$this->_data['options'][$name] = $value;
			
			$matchingOptions = array_values(array_filter($dropdowns, function($o) use ($attribute)
			{
				return $o->getTitle() == $attribute['name'];
			}));

			// Create if doesn't exist		
			if(count($matchingOptions) == 0)
			{
				$optionData = array(
					'title'		=> $attribute['name'],
					'type'		=> Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN,
					'is_require'=> false,
					'sort_order'=> 1,

					'additional_fields' => array()
				);

				$optionData['additional_fields'][] = array(
					'title'		=> $attribute['value'],
					'price'		=> $attribute['cost'],
					'price_type'=> 'fixed'
				);

				Gateway3D_PersonaliseIt_Helper_ProductCustomOptions::addOption($this->_product, $optionData);
			}

			else
			{
				$matchingOption = $matchingOptions[0];

				// Check that a matching value exists
				$matchingValues = array_keys(array_filter($matchingOption->getValues(), function($v) use ($attribute)
				{
					return $v->getTitle() == $attribute['value'];
				}));
				
				if(count($matchingValues) == 0)
				{
					$data = array(
						'additional_fields' => array(array(
							'title'		=> $attribute['value'],
							'price'		=> $attribute['cost'],
							'price_type'=> 'fixed'
						))
					);

					Gateway3D_PersonaliseIt_Helper_ProductCustomOptions::update($matchingOption, $data);
				}
			}
		}
	}

	/**
	 * Check whether a given custom option is a "dropdown".
	 *
	 * @param Mage_Catalog_Model_Product_Option $o
	 * @return bool True if the given option is a "dropdown".
	 */
	private function _filterOptionDropdown(Mage_Catalog_Model_Product_Option $o)
	{
		return $o->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN;
	}
	
	/**
	 * Check whether a given custom option is a "multiple".
	 *
	 * @param Mage_Catalog_Model_Product_Option $o
	 * @return bool True if the given option is a "multiple".
	 */
	private function _filterOptionMultiple(Mage_Catalog_Model_Product_Option $o)
	{
		return $o->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE;
	}	

	/**
	 * Add the current product or it's associated group products to the cart.
	 *
	 * @return void
	 */
	private function _processCart($updateItemId)
	{
		$cart = Mage::getModel("checkout/cart");
		
		if($this->_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
		{			
			$associatedProducts = $this->_product
							->getTypeInstance(true)
							->getAssociatedProducts($this->_product);
		
			foreach($this->_data['grouped'] as $item)
			{
				if($item['quantity'] > 0)
				{
					$product = Mage::getModel('catalog/product');		
					$product->load($item['id']);
				
					$this->_ensureProductHasCustomOptions($product);
					
					$params = array(
						'options'	=> $this->_getOptions($product, $item),
						'qty'		=> $item['quantity']
					);
	
					if($updateItemId)
					{
						$cart->getQuote()->updateItem($updateItemId, $params);
					}
					else
					{
						$cart->addProduct($product, $params);
					}
				}
			}
		}		
		else
		{
			// Dynamically add custom options for specific fields
			$required = array();

			foreach($this->_data as $key => $value)
			{
				if(preg_match("/^user(Text|Image)[0-9]+$/", $key))
				{
					$required[] = $key;
				}
			}
			
			$product = Mage::getModel('catalog/product')->load($this->_product->getId());
					
			$this->_ensureProductHasCustomOptions($product, $required);
			
			$params = array(
				'options'	=> $this->_getOptions($product),
				'qty'		=> @$this->_data['quantity'] ?: 1
			);
	
			if($updateItemId)
			{
				$cart->getQuote()->updateItem($updateItemId, $params);
			}
			else
			{
				$cart->addProduct($product, $params);
			}
		}
		
		//		
		$cart->save();
	}
	
	private function _getOptions(Mage_Catalog_Model_Product $product, $additionalData = array())
	{
		// Note: 
		//		additionalData has precedence over _data
		//		So the ordering of the if statements is important!
		
		$options = array();
		
		foreach($product->getOptions() as $option)
		{
			$id = $option->getId();
			$sku = $option->getSku();

			if($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
			{
				if(isset($additionalData['options'][$id]))
				{
					$options[$id] = $additionalData['options'][$id];
				}
				else if(isset($this->_data['options'][$id]))
				{
					$options[$id] = $this->_data['options'][$id];
				}
				else if(isset($this->_data['options'][$option->getTitle()]))
				{
					$_value = $this->_data['options'][$option->getTitle()];

					foreach($option->getValues() as $value)
					{						
						if($value->getTitle() == $_value)
						{
							$options[$option->getId()] = $value->getId();
						}
					}
				}
			}
			else if($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE)
			{
				if(isset($this->_data['options'][$option->getTitle()]))
				{
					$_values = $this->_data['options'][$option->getTitle()];

					$values = array_filter($option->getValues(), function($value) use ($_values)
					{
						return in_array($value->getTitle(), $_values);
					});

					$ids = array_map(function($value) { return $value->getId(); }, $values);

					$options[$option->getId()] = $ids;
				}
			}
			else if(isset($additionalData[$sku]))
			{
				$options[$id] = $additionalData[$sku];	
			}
			else if(isset($this->_data[$sku]))			
			{
				$options[$id] = $this->_data[$sku];
			}			
		}

		return $options;
	}
	
	private function _ensureProductHasCustomOptions(Mage_Catalog_Model_Product $product, $required = array())
	{	
		$printJobIdSku = Gateway3D_PersonaliseIt_Model_Cron_Sl::PRINT_JOB_ID_CUSTOM_OPTION_SKU;
		$printJobRefSku = Gateway3D_PersonaliseIt_Model_Cron_Sl::PRINT_JOB_REF_CUSTOM_OPTION_SKU;
		
		$required = array_merge(array(
			$printJobIdSku,
			$printJobRefSku,
			"thumburl"
		), $required);
		
		Gateway3D_PersonaliseIt_Helper_ProductCustomOptions::ensureProductHasCustomOptions($product, $required);
	}

	private function _handleBundleRefs()
	{
		if(isset($this->_data['bundle_ref']))
		{
			$cart = Mage::getModel('checkout/cart')->getQuote();
			$items = $cart->getAllItems();

			$refs = array();

			foreach($items as $item)
			{
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				$options = $product->getProductOptionsCollection();

				$bundleOptionId = false;

				foreach($options as $option)
				{
					if($option->getSku() == 'bundle_ref')
					{
						$bundleOptionId = $option->getId();
						break;
					}
				}

				if($bundleOptionId)
				{
					$orderOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

					foreach($orderOptions['options'] as $option)
					{
						if($option['option_id'] == $bundleOptionId)
						{
							$refs[] = $option['option_value'];
						}
					}
				}
			}
			
			$refs = array_unique($refs);
			$next = count($refs) + 1;
			
			$this->_data['bundle_ref'] = $next;
		}
	}
}