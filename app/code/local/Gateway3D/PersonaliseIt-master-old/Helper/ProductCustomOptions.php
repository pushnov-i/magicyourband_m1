<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Helper_ProductCustomOptions
{
	public static function ensureProductHasCustomOptions(Mage_Catalog_Model_Product $product, $required = array())
	{
		// Compatability hack for older Magentos
		if(!class_exists('Mage_Catalog_Model_Product_Option_Api'))
		{
			return;
		}

		// determine if the product has the required custom options
		$options = $product->getOptions();
		$coSkus = array_map(function($o) { return $o->getSku(); }, $options);
		$coTitles = array_map(function($o) { return $o->getTitle(); }, $options);
		$coSkusAndTitles = $coSkus + $coTitles;

		$missing = array_unique(array_diff($required, $coSkusAndTitles));

		// create missing options
		foreach($missing as $sku)
		{
			$optionData = array(
				'title'			=> $sku,
				'type'			=> Gateway3D_PersonaliseIt_Model_Catalog_Product_Option::OPTION_TYPE_HIDDEN,
				'is_require'	=> false,
				'sort_order'	=> 1,
				'additional_fields' => array(array(
					'price'			=> 0,
					'price_type'	=> 'fixed',
					'sku'			=> $sku
				))
			);
				
			self::addOption($product, $optionData);
		}
			
		// reload
		$product->load($product->getId());
		
		return array_values(array_filter($product->getOptions(), function($o) use ($required)
		{
			return in_array($o->getSku(), $required);
		}));
	}

	static function addOption(Mage_Catalog_Model_Product $product, array $optionData)
	{
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

	static function update(Mage_Catalog_Model_Product_Option $option, array $data)
	{
		$id = Mage::app()->getStore()->getId();
		Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
	
		$m = new Mage_Catalog_Model_Product_Option_Api;
		$m->update($option->getId(), $data);

		Mage::app()->getStore()->setId($id);
	}
}
