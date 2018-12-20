<?php

/*
* @category    Module
* @package     Sonassi_Catfix
* @copyright   Copyright (c) 2012 Sonassi
*/

class Sonassi_CatFix_Helper_Data
  extends Mage_Core_Helper_data
{

  private $_collectionCache;

  function getConfigurableChildrenCollection($_product, $attributeToSelect = array())
  {

    $cacheKey = md5($_product->getId().serialize($attributeToSelect));

    if (isset($this->_collectionCache[$cacheKey])) {
      return $this->_collectionCache[$cacheKey];
    }

    $childen_ids = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($_product->getId());

    $_collection = Mage::getModel('catalog/product')
      ->getCollection()
      ->addAttributeToFilter('entity_id', array('in' => $childen_ids));

    foreach ($attributeToSelect as $attribute)
    {
      $_collection->addAttributeToSelect($attribute);
    }      
    
    /*
    // This method is ~0.004s slower than the above 
    $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
    $codes = array();
    foreach ($attributeToSelect as $attribute)
    {    
      $codes[] = $eavAttribute->getIdByCode('catalog_product', $attribute);
    }
    
    $_collection = Mage::getModel('catalog/product_type_configurable')
      ->getUsedProducts($codes,$_product);
    */
       
    /*
    // Stock information is not necessary
    Mage::getModel('cataloginventory/stock')
      ->addInStockFilterToCollection($_collection);
    */

    $this->_collectionCache[$cacheKey] = $_collection;

    return $_collection;
  }

}