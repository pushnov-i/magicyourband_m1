<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
abstract class Gateway3D_PersonaliseIt_Model_Product_Abstract
{
	protected $_product;
	
	/**
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct()
	{
		return $this->_product;
	}
	
	public function load($id = null, $sku = null)
	{
		if($id)
		{
			$this->loadById($id);
		}
		else if($sku)
		{
			$this->loadBySku($sku);
		}
		else
		{
			throw new Exception('No ID or SKU specified');
		}
	}
	
	public function loadById($id)
	{
		$product = Mage::getModel('catalog/product');
		
		$product->load($id);
		
		if(!$product->getId())
		{
			throw new Exception("Invalid ID {$id}");
		}
		
		$this->_product = $product;
	}
	
	public function loadBySku($sku)
	{
		$id = Mage::getModel('catalog/product')
				->getIdBySku($sku);
		
		if(!$id)
		{
			throw new Exception("Invalid SKU {$sku}");
		}
		
		$this->loadById($id);
	}
}