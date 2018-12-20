<?php

class Gateway3D_PersonaliseIt_Model_ImportOptions
	extends Mage_Core_Helper_Abstract
{
	const TYPE_SAMPLE	= "su";
	const TYPE_PRODUCT	= "p";

	private $_product = null;
	
	private $_existingProductOptionSkus = array();
	private $_existingProductOptionTitles = array();

	private $_cppProduct = null;

	private $_type = "";
	private $_id = 0;

	private $_optionData = array();

	public function __construct(Mage_Catalog_Model_Product $product)
	{
		$this->_product = $product;

		foreach($product->getOptions() as $option)
		{
			if($option->getSku())
			{
				$this->_existingProductOptionSkus[] = $option->getSku();
			}
			else
			{
				$values = $option->getValues();

				foreach($option->getValues() as $value)
				{
					$this->_existingProductOptionSkus[] = $value->getSku();
				}
			}
		}
	}

	public function import()
	{
		$this->_parseIframeUrls();

		if($this->_type == self::TYPE_SAMPLE)
		{
			$this->_loadSample();
		}

		$this->_loadProduct();
		$this->_determineOptions();

		$m = new Mage_Catalog_Model_Product_Option_Api;

		foreach($this->_optionData as $optionData)
		{
			$skuExists = false;

			foreach($optionData['additional_fields'] as $value)
			{
				$skuExists |= in_array($value['sku'], $this->_existingProductOptionSkus);
			}

			if(!$skuExists)
			{
				$m->add($this->_product->getId(), $optionData);
			}
		}
	}

	private function _determineOptions()
	{
		$this->_determineOptionsFromCppTextAreas();
		$this->_determineOptionsFromCppAttributes();	
	}

	private function _determineOptionsFromCppTextAreas()
	{
		foreach($this->_cppProduct->text_areas as $area)
		{
			if($area['options'])
			{
				$type = Gateway3D_PersonaliseIt_Model_Catalog_Product_Option::OPTION_TYPE_DROP_DOWN;

				$options = explode(PHP_EOL, $area['options']);
				$fields = array_map(function($o) use ($area)
				{
					return array(
						'title'			=> trim($o),
						'sku'			=> "userText{$area['template_text_area_id']}",
						'price_type'	=> 'fixed',
					);
				}, $options);
			}
			else
			{
				$type = $area['allow_input']
							? Gateway3D_PersonaliseIt_Model_Catalog_Product_Option::OPTION_TYPE_FIELD
							: Gateway3D_PersonaliseIt_Model_Catalog_Product_Option::OPTION_TYPE_HIDDEN; 

				$maxCharacters = $area['character_limit'];
				$maxLines = $area['max_lines'];

				if($maxLines && $maxCharacters)
				{
					$maxCharacters *= $maxLines;

					// EOL
					$maxCharacters += ($maxLines * 2);
				}

				$fields = array(array(
					'price'			=> @$area['price'] ?: 0.00,
					'price_type'	=> 'fixed',
					'sku'			=> "userText{$area['template_text_area_id']}",
				));

				if($maxCharacters)
				{
					$fields[0]['max_characters'] = $maxCharacters;
				}
			}

			$this->_optionData[] = array(
				'title'			=> @$area['name'] ?: '',
				'type'			=> $type,
				'is_require'	=> false,
				'sort_order'	=> 1,

				'additional_fields'	=> $fields
			);
		}
	}

	private function _determineOptionsFromCppAttributes()
	{
		// First pass - organise attributes into groups.
		$groups = array();

		foreach($this->_cppProduct->product_attributes as $attribute)
		{
			$key = $attribute['group_name'];

			if(!isset($groups[$key]))
			{
				$groups[$key] = array();
			}

			$groups[$key][] = $attribute;
		}

		// Second pass - marshal to Mage optionData structures
		foreach($groups as $name => $attributes)
		{
			$optionData = array(
				'title'			=> $name,
				'type'			=> Gateway3D_PersonaliseIt_Model_Catalog_Product_Option::OPTION_TYPE_DROP_DOWN,
				'is_require'	=> false,
				'sort_order'	=> 1,
				
				'additional_fields' => array()
			);

			foreach($attributes as $attribute)
			{
				$optionData['additional_fields'][] = array(
					'title'			=> @$attribute['attribute_name'] ?: '',
					'price'			=> @$attribute['price'] ?: 0.00,
					'price_type'	=> 'fixed',
					'sku'			=> @$attribute['sku'] ?: "userAttribute{$attribute['attribute_id']}"
				);
			}

			$this->_optionData[] = $optionData;
		}
	}

	private function _doesAnOptionAlreadyExistForCppAttributeGroup($attribute)
	{
			
	}

	private function _loadSample()
	{
		$url = Mage::getStoreConfig("general/personaliseit-cpp/api_path") . "/p/1/sample";	
		$url = Mage::helper('personaliseit')->reduceUrlSlashes($url);

		$service = new Gateway3D_PersonaliseIt_Service_Api_P1_Sample($url);
		$sample = $service->retrieve($this->_id);

		$this->_id = $sample->product_id;
		$this->_type = self::TYPE_PRODUCT;
	}

	private function _loadProduct()
	{
		$url = Mage::getStoreConfig("general/personaliseit-cpp/api_path") . "/p/1/product";
		$url = Mage::helper('personaliseit')->reduceUrlSlashes($url);

		$service = new Gateway3D_PersonaliseIt_Service_Api_P1_Product($url);
		$this->_cppProduct = $service->retrieve($this->_id);
	}

	private function _parseIframeUrls()
	{
		$fields = array(
			'easypromo3d_url',
			'personaliseit_iframe_url',
			'personaliseit_m_iframe_url',
			'personaliseit_fl_iframe_url',
			'personaliseit_gl_iframe_url'
		);

		foreach($fields as &$field)
		{
			if($this->_product->{$field})
			{
				$count = preg_match("/(su|p)=([0-9]*)/", $this->_product->{$field}, $matches);

				if($count && count($matches) >= 3)
				{
					$this->_type = $matches[1];
					$this->_id = $matches[2];

					return;
				}
			}
		}
	}
}
