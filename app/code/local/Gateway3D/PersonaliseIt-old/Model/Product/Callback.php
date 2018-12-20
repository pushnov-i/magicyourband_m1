<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
class Gateway3D_PersonaliseIt_Model_Product_Callback
	extends Gateway3D_PersonaliseIt_Model_Product_Abstract
{
	private $_data;
	
	public function __construct(array $data)
	{
		$this->_data = $data;
	}
	
	public function process()
	{		
		$this->_processCart();
	}
	
	private function _processCart()
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
				
					$cart->addProduct($product, $params);
				}
			}
		}		
		else
		{
			// Dynamically add some custom options for any string values
			// passed.
			$required = array();
			
			foreach($this->_data as $key => $value)
			{
				if($key != 'sku' && is_string($value))
				{
					$required[] = $key;
				}
			}
			
			$this->_ensureProductHasCustomOptions($this->_product, $required);
			
			$params = array(
				'options'	=> $this->_getOptions($this->_product),
				'qty'		=> $this->_data['quantity']
			);
		
			$cart->addProduct($this->_product, $params);
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
		// Compatability hack for older Magentos
		if(!class_exists('Mage_Catalog_Model_Product_Option_Api'))
		{
			return;
		}
		
		//
		$options = $product->getOptions();
		
		$printJobIdSku = Gateway3D_PersonaliseIt_Model_Cron_Sl::PRINT_JOB_ID_CUSTOM_OPTION_SKU;
		
		$required = array_merge(array(
			$printJobIdSku,
			"thumburl"
		), $required);
		
		$hasRequired = array();
		
		foreach($required as $_required)
		{
			$hasRequired[$_required] = false;
		}
		
		// determine if the product has the required custom options
		foreach($product->getOptions() as $option)
		{
			if(isset($hasRequired[$option->getSku()]))
			{
				$hasRequired[$option->getSku()] = true;
			}
		}
		
		// create missing options
		foreach($hasRequired as $sku => $has)
		{
			if(!$has)
			{
				$optionData = array(
					'title'			=> $sku,
					'type'			=> Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD,
					'is_require'	=> false,
					'sort_order'	=> 1,
					'additional_fields' => array(array(
						'price'			=> 0,
						'price_type'	=> 'fixed',
						'sku'			=> $sku
					))
				);
				
				// HACK:
				//
				//	Magento seems to only let you change product custom options
				//	if it thinks that we are in the backend.
				//
				//  See Mage_Catalog_Model_Product::getOrigData
				
				$id = Mage::app()->getStore()->getId();
				Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
				
				$m = new Mage_Catalog_Model_Product_Option_Api;			
				$m->add($product->getId(), $optionData);
				
				Mage::app()->getStore()->setId($id);
				
				// HACK:
				//
				//	Magento versions older than 1.8 seem to require us to
				//	explictly reset the product options array otherwise
				//	options will be added multiple times the next time we
				//	try to add one.
				//
				//	Presumably 1.8 and above call this line somewhere down
				//	in the call chain of Mage_Catalog_Model_Product_Option_Api.
				
				$product->getOptionInstance()
						->setProduct($product)
						->setOptions(array());
			}
		}
		
		// reload
		$product->load($product->getId());
	}
}