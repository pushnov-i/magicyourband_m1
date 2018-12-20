<?php

/*
* @category    Module
* @package     Sonassi_Catfix
* @copyright   Copyright (c) 2012 Sonassi
*/

class Sonassi_CatFix_Model_Catalog_Product_Type_Configurable
  extends Mage_Catalog_Model_Product_Type_Configurable
{


    /**
     * Bug fix version to cut down on page load time of configurable items
     * Not fully tested, but does yield a 100% decrease on page load time
     */
    public function getUsedProducts($requiredAttributeIds = null, $product = null)
    {

        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);
        if (!$this->getProduct($product)->hasData($this->_usedProducts)) {
            if (is_null($requiredAttributeIds)
                and is_null($this->getProduct($product)->getData($this->_configurableAttributes))) {
                // If used products load before attributes, we will load attributes.
                $this->getConfigurableAttributes($product);
                // After attributes loading products loaded too.
                Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
                return $this->getProduct($product)->getData($this->_usedProducts);
            }

            $usedProducts = array();

            $handles = Mage::app()->getLayout()->getUpdate()->getHandles();
            if(in_array('catalog_category_view', $handles)) {
              $collection = $this->getUsedProductCollection($product)
                  ->addAttributeToSelect('stock_status')
                  ->addFilterByRequiredOptions();
            } else {
              $collection = $this->getUsedProductCollection($product)
                  ->addAttributeToSelect('*')
                  ->addFilterByRequiredOptions();

              if (is_array($requiredAttributeIds)) {
                  foreach ($requiredAttributeIds as $attributeId) {
                      $attribute = $this->getAttributeById($attributeId, $product);
                      if (!is_null($attribute))
                          $collection->addAttributeToFilter($attribute->getAttributeCode(), array('notnull'=>1));
                  }
              }
            }

            foreach ($collection as $item) {
                $usedProducts[] = $item;
            }

            $this->getProduct($product)->setData($this->_usedProducts, $usedProducts);
        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $this->getProduct($product)->getData($this->_usedProducts);
    }


    public function getSelectedAttributesInfo($product = null)
    {
        $attributes = array();
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);
        if ($attributesOption = $this->getProduct($product)->getCustomOption('attributes')) {
            $data = unserialize($attributesOption->getValue());
            $this->getUsedProductAttributeIds($product);

            $usedAttributes = $this->getProduct($product)->getData($this->_usedAttributes);

            foreach ($data as $attributeId => $attributeValue) {
                if (isset($usedAttributes[$attributeId])) {
                    $attribute = $usedAttributes[$attributeId];
                    $label = $attribute->getLabel();
                    $value = $attribute->getProductAttribute();
                    if ($value->getSourceModel()) {
                      if (!Mage::app()->getStore()->isAdmin()) {
                        $value = $value->getSource()->getNeededOptionText($attributeValue);
                      } else {
                        $value = $value->getSource()->getOptionText($attributeValue);
                      }
                    }
                    else {
                        $value = '';
                    }

                    $attributes[] = array('label'=>$label, 'value'=>$value);
                }
            }
        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $attributes;
    }

    public function isConfigurableSaleable($product = null)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

        $cpsltableName = $resource->getTableName('catalog/product_super_link');
        $csiTableName = $resource->getTableName('cataloginventory/stock_item');
        $cpeiTableName = Mage::getConfig()->getTablePrefix().'catalog_product_entity_int';

        $enabledStatus = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
        $statusAttribute = Mage::getSingleton("eav/config")->getAttribute('catalog_product', 'status');
        $storeId = Mage::app()->getStore()->getStoreId();
        $manageStockSetting = (int) Mage::getStoreConfig('cataloginventory/item_options/manage_stock');
        $backordersSetting = (int) Mage::getStoreConfig('cataloginventory/item_options/backorders');
        $minQtySetting = (int) Mage::getStoreConfig('cataloginventory/item_options/min_qty');

        $links  = array();

        $select = $read->select()
            ->from(array('cpsl' => $cpsltableName),'product_id')
            ->where('parent_id=?', $product->getId())
            ->joinLeft(array('csi' => $csiTableName), 'csi.product_id = cpsl.product_id', array())
            ->joinLeft(array('cpei' => $cpeiTableName), 'csi.product_id = cpei.entity_id', array())
            ->where('cpei.attribute_id=?', $statusAttribute->getAttributeId())
            ->where('(cpei.store_id='.$storeId.' OR  cpei.store_id=0)')
            ->where('cpei.value =?', $enabledStatus)
            ->where('   (csi.manage_stock=0 AND csi.use_config_manage_stock=0)
                     OR (csi.use_config_manage_stock=1 AND 0='.$manageStockSetting.')
                     OR (csi.is_in_stock=1 AND csi.qty>'.$minQtySetting.')
                     OR (csi.is_in_stock=1 AND (csi.backorders=1 OR (csi.use_config_backorders=1 AND 1='.$backordersSetting.')))
                     ');

        $result = $read->fetchAll($select);

        return count($result);
    }

    public function isSalable($product = null)
    {

        $salable = Mage::getModel('catalog/product_type_simple')->isSalable($product);

        if ($salable !== false) {
            $salable = false;
            if (!is_null($product)) {
                $this->setStoreFilter($product->getStoreId(), $product);
            }
            $salable = $this->isConfigurableSaleable($product);
        }

        return $salable;
    }

}
