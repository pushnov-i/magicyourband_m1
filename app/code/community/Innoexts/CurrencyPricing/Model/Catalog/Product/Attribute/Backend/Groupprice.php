<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the InnoExts Commercial License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://innoexts.com/commercial-license-agreement
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_CurrencyPricing
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Product group price backend attribute
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Catalog_Product_Attribute_Backend_Groupprice 
    extends Mage_Catalog_Model_Product_Attribute_Backend_Groupprice 
{
    /**
     * Get currency pricing helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Data
     */
    protected function getCurrencyPricingHelper()
    {
        return Mage::helper('currencypricing');
    }
    /**
     * Get product helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product
     */
    protected function getProductHelper()
    {
        return $this->getCurrencyPricingHelper()->getProductHelper();
    }
    /**
     * Get product price helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product_Price
     */
    protected function getProductPriceHelper()
    {
        return $this->getCurrencyPricingHelper()->getProductPriceHelper();
    }
    /**
     * Set attribute instance
     * 
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * 
     * @return self
     */
    public function setAttribute($attribute)
    {
        parent::setAttribute($attribute);
        $this->setScope($attribute);
        return $this;
    }
    /**
     * Redefine attribute scope
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * 
     * @return self
     */
    public function setScope($attribute)
    {
        $this->getCurrencyPricingHelper()
            ->getProductPriceHelper()
            ->setAttributeScope($attribute);
        return $this;
    }
    /**
     * Validate data
     * 
     * @param array $data
     * @param int $websiteId
     * @param bool $filterEmpty
     * @param bool $filterInactive
     * @param bool $filterAncestors
     * 
     * @return bool
     */
    protected function validateData($data, $websiteId, $filterEmpty = true, $filterInactive = true, $filterAncestors = true)
    {
        $priceHelper        = $this->getProductPriceHelper();
        if ($filterEmpty) {
            if (!isset($data['cust_group']) || !empty($data['delete'])) {
                return false;
            }
        }
        if ($filterInactive) {
            if ($priceHelper->isInactiveData($data, $websiteId)) {
                return false;
            }
        }
        if ($filterAncestors) {
            if ($priceHelper->isAncestorData($data, $websiteId)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Get data key
     * 
     * @param array $data
     * @param bool $allWebsites
     * 
     * @return string 
     */
    protected function getDataKey($data, $allWebsites = false)
    {
        return join('-', array(
            (($allWebsites) ? 0 : $data['website_id']), 
            $data['cust_group'], 
            (isset($data['currency']) && $data['currency']) ? $data['currency'] : null
        ));
    }
    /**
     * Get short data key
     * 
     * @param array $data
     * 
     * @return string 
     */
    protected function getShortDataKey($data)
    {
        return join('-', array(
            $data['cust_group'], 
            (isset($data['currency']) && $data['currency']) ? $data['currency'] : null
        ));
    }
    /**
     * Validate price data
     * 
     * @param Mage_Catalog_Model_Product $object
     * @throws Mage_Core_Exception
     * 
     * @return bool
     */
    public function validate($object)
    {
        $helper             = $this->getCurrencyPricingHelper();
        $productHelper      = $this->getProductHelper();
        $priceHelper        = $this->getProductPriceHelper();
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $prices             = $object->getData($attributeName);
        if (empty($prices)) {
            return true; 
        }
        if ($productHelper->isGroupPriceFixed($object)) {
            $duplicateMessage = $helper->__('Duplicate website group price customer group and currency.');
        } else {
            $duplicateMessage = $helper->__('Duplicate website group price customer group.');
        }
        $duplicates = array();
        foreach ($prices as $price) {
            if (!empty($price['delete'])) { 
                continue; 
            }
            $compare = $this->getDataKey($price);
            if (isset($duplicates[$compare])) {
                Mage::throwException($duplicateMessage);
            }
            $duplicates[$compare] = true;
        }
        if (($priceHelper->isWebsiteScope()) && $object->getStoreId()) {
            $websiteId          = $helper->getCoreHelper()->getWebsiteIdByStoreId($object->getStoreId());
            $origPrices         = $object->getOrigData($attributeName);
            foreach ($origPrices as $price) {
                if ($priceHelper->isAncestorData($price, $websiteId)) {
                    $compare        = $this->getDataKey($price);
                    $duplicates[$compare] = true;
                }
            }
        }
        $baseCurrency = $helper->getCoreHelper()
            ->getCurrencyHelper()
            ->getBaseCode();
        $rates = $this->_getWebsiteCurrencyRates();
        foreach ($prices as $price) {
            if (!empty($price['delete'])) {
                continue;
            }
            if ($price['website_id'] == 0) {
                continue;
            }
            $websiteCurrency    = $rates[$price['website_id']]['code'];
            $compare            = $this->getDataKey($price);
            $globalCompare      = $this->getDataKey($price, true);
            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                Mage::throwException($duplicateMessage);
            }
        }
        return true;
    }
    /**
     * Sort price data
     *
     * @param array $a
     * @param array $b
     * 
     * @return int
     */
    protected function _sortPriceData($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? 1 : -1;
        }
        if (isset($a['currency']) && isset($b['currency'])) {
            if ($a['currency'] != $b['currency']) {
                return $a['currency'] < $b['currency'] ? 1 : -1;
            }
        }
        return 0;
    }
    /**
     * Prepare prices data for website
     *
     * @param array $priceData
     * @param string $productTypeId
     * @param int $websiteId
     * @param string $currency
     * 
     * @return array
     */
    public function preparePriceData2(array $priceData, $productTypeId, $websiteId, $currency)
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $priceHelper            = $this->getProductPriceHelper();
        $isGroupPriceFixed      = $priceHelper->isGroupPriceFixed($productTypeId);
        $website                = $helper->getCoreHelper()->getWebsiteById($websiteId);
        $data                   = array();
        usort($priceData, array($this, '_sortPriceData'));
        foreach ($priceData as $v) {
            $v['currency'] = (isset($v['currency']) && $v['currency']) ? $v['currency'] : null;
            if (empty($v['currency'])) {
                $isEmptyCurrency = true;
                if ($v['website_id'] == 0) {
                    $v['currency'] = $helper->getCoreHelper()->getCurrencyHelper()->getBaseCode();
                } else {
                    $v['currency'] = $helper->getCoreHelper()->getWebsiteById($v['website_id'])->getBaseCurrencyCode();
                }
            } else {
                $isEmptyCurrency = false;
            }
            $key = $this->getShortDataKey($v);
            if (
                !isset($data[$key]) && (
                    ( $v['website_id'] == $websiteId ) || 
                    ( $v['website_id'] == 0 )
                ) && (
                    ($v['currency'] == $currency) || ($isEmptyCurrency)
                )
            ) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                $data[$key]['currency'] = $currency;
                if ($isGroupPriceFixed) {
                    $websiteCurrency = $website->getBaseCurrencyCode();
                    if (($websiteCurrency != $v['currency']) || $isEmptyCurrency) {
                        $rate = $helper->getCoreHelper()->getCurrencyHelper()->getRate($websiteCurrency, $v['currency']);
                        $data[$key]['price'] = $v['price'] / $rate;
                        $data[$key]['website_price'] = $v['price'] / $rate;
                    }
                }
            }
        }
        return $data;
    }
    /**
     * After load
     * 
     * @param Mage_Catalog_Model_Product $object
     * 
     * @return self
     */
    public function afterLoad($object)
    {
        $helper             = $this->getCurrencyPricingHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $resource           = $this->_getResource();
        $websiteId          = null;
        $store              = $helper->getCoreHelper()->getStoreById($object->getStoreId());
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $isEditMode         = $object->getData('_edit_mode');
        if ($priceHelper->isGlobalScope()) {
            $websiteId          = null;
        } else if ($priceHelper->isWebsiteScope() && $store->getId()) {
            $websiteId          = $helper->getCoreHelper()
                ->getWebsiteIdByStoreId($store->getId());
        }
        if ($isEditMode) {
            $currency           = null;
        } else {
            $currency           = $store->getCurrentCurrencyCode();
        }
        $data = $resource->loadPriceData2($object->getId(), $websiteId, $currency);
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
        }
        $object->setGroupPrices($data);
        $priceHelper->setGroupPrice($object);
        $object->setOrigData($attributeName, $data);
        $valueChangedKey = $attributeName.'_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);
        return $this;
    }
    /**
     * After save
     *
     * @param Mage_Catalog_Model_Product $object
     * 
     * @return self
     */
    public function afterSave($object)
    {
        $helper             = $this->getCurrencyPricingHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $resource           = $this->_getResource();
        $objectId           = $object->getId();
        $storeId            = $object->getStoreId();
        $websiteId          = $helper->getCoreHelper()->getWebsiteIdByStoreId($storeId);
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $prices             = $object->getData($attributeName);
        if (empty($prices)) {
            if ($priceHelper->isGlobalScope() || $websiteId == 0) {
                $resource->deletePriceData2($objectId);
            } else if ($priceHelper->isWebsiteScope()) {
                $resource->deletePriceData2($objectId, $websiteId);
            }
            return $this;
        }
        $old                = array();
        $new                = array();
        $origPrices         = $object->getOrigData($attributeName);
        if (!is_array($origPrices)) { 
            $origPrices = array(); 
        }
        foreach ($origPrices as $data) {
            if (!$this->validateData($data, $websiteId, false, false, true)) {
                continue;
            }
            $key = $this->getDataKey($data);
            $old[$key] = $data;
        }
        foreach ($prices as $data) {
            if (!$this->validateData($data, $websiteId, true, true, true)) {
                continue;
            }
            $key = $this->getDataKey($data);
            $useForAllGroups = $data['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;
            $new[$key] = array(
                'website_id'        => $data['website_id'], 
                'all_groups'        => $useForAllGroups ? 1 : 0, 
                'customer_group_id' => $customerGroupId, 
                'value'             => $data['price'], 
                'currency'          => (isset($data['currency']) && $data['currency']) ? $data['currency'] : null, 
            );
        }
        $delete         = array_diff_key($old, $new);
        $insert         = array_diff_key($new, $old);
        $update         = array_intersect_key($new, $old);
        $isChanged      = false;
        $productId      = $objectId;
        if (!empty($delete)) {
            foreach ($delete as $data) {
                $resource->deletePriceData2($productId, null, null, $data['price_id']);
                $isChanged = true;
            }
        }
        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new Varien_Object($data);
                $price->setEntityId($productId);
                $resource->savePriceData($price);
                $isChanged = true;
            }
        }
        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value']) {
                    $price = new Varien_Object(array('value_id' => $old[$k]['price_id'], 'value' => $v['value']));
                    $resource->savePriceData($price);
                    $isChanged = true;
                }
            }
        }
        if ($isChanged) {
            $valueChangedKey = $attributeName.'_changed';
            $object->setData($valueChangedKey, 1);
        }
        return $this;
    }
}