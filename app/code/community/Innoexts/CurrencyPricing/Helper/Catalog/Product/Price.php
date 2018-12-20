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
 * Product price helper
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Helper_Catalog_Product_Price 
    extends Innoexts_Core_Helper_Catalog_Product_Price 
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
    public function getProductHelper()
    {
        return Mage::helper('currencypricing/catalog_product');
    }
    /**
     * Get indexer helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product
     */
    public function getIndexerHelper()
    {
        return Mage::helper('currencypricing/catalog_product_price_indexer');
    }
    /**
     * Check if data is ancestor
     * 
     * @param array $data
     * @param mixed $websiteId
     * 
     * @return bool
     */
    public function isAncestorData($data, $websiteId)
    {
        if (!$this->isGlobalScope() && ($websiteId != 0)) {
            if (
                ($this->isWebsiteScope() && ((int) $data['website_id'] == 0))
            ) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if data is inactive
     * 
     * @param array $data
     * @param mixed $websiteId
     * 
     * @return bool
     */
    public function isInactiveData($data, $websiteId)
    {
        if (
            ($this->isGlobalScope() && ($data['website_id'] > 0))
        ) {
            return true;
        }
        return false;
    }
    /**
     * Load collection tier price
     *
     * @return self
     */
    public function loadCollectionTierPrice($collection)
    {
        if ($collection->getFlag('tier_price_added')) {
            return $this;
        }
        $prices         = array();
        $productIds     = array();
        foreach ($collection as $product) {
            array_push($productIds, $product->getId());
            $prices[$product->getId()] = array();
        }
        if (!count($productIds)) {
            return $this;
        }
        $coreHelper     = $this->getCoreHelper();
        $currentStoreId = $collection->getStoreId();
        $websiteId      = $coreHelper->getWebsiteIdByStoreId($currentStoreId);
        if ($this->isGlobalScope()) {
            $websiteId      = 0;
        }
        $currencyCode   = $coreHelper
                ->getStoreById($currentStoreId)
                ->getCurrentCurrencyCode();
        $adapter        = $collection->getConnection();
        $select         = $adapter->select()
            ->from(
                $coreHelper->getTable('catalog/product_attribute_tier_price'), 
                array(
                    'price_id'      => 'value_id', 
                    'website_id'    => 'website_id', 
                    'all_groups'    => 'all_groups', 
                    'cust_group'    => 'customer_group_id', 
                    'price_qty'     => 'qty', 
                    'price'         => 'value', 
                    'product_id'    => 'entity_id', 
                    'currency'      => 'currency', 
                )
            )
            ->where('entity_id IN(?)', $productIds)
            ->order(array('entity_id','qty'));
        if ($websiteId == '0') {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id IN(?)', array('0', $websiteId));
        }
        $select->where("(currency = ?) OR (currency IS NULL) OR (currency = '')", $currencyCode);
        $customerGroupAllId = Mage_Customer_Model_Group::CUST_GROUP_ALL;
        foreach ($adapter->fetchAll($select) as $item) {
            $prices[$item['product_id']][] = array(
                'website_id'     => $item['website_id'], 
                'cust_group'     => $item['all_groups'] ? $customerGroupAllId : $item['cust_group'], 
                'price_qty'      => $item['price_qty'], 
                'price'          => $item['price'], 
                'website_price'  => $item['price'], 
                'currency'       => ($item['currency']) ? $item['currency'] : null, 
            );
        }
        foreach ($collection as $product) {
            $product->setTierPrices($prices[$product->getId()]);
            $this->setTierPrice($product);
        }
        $collection->setFlag('tier_price_added', true);
        return $this;
    }
    /**
     * Set group price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param array $prices
     * 
     * @return self
     */
    public function _setGroupPrice($product, $attribute, $prices)
    {
        if (!$attribute) {
            return $this;
        }
        $backend        = $attribute->getBackend();
        if (!$backend) {
            return $this;
        }
        $coreHelper     = $this->getCoreHelper();
        $currentStoreId = ($product->getStoreId() != 0) ? $product->getStoreId() : $coreHelper->getCurrentStoreId();
        $websiteId      = $coreHelper->getWebsiteIdByStoreId($currentStoreId);
        if ($this->isGlobalScope()) {
            $websiteId      = null;
        }
        if (!empty($prices) && !$product->getData('_edit_mode')) {
            $currencyCode   = $coreHelper
                ->getStoreById($currentStoreId)
                ->getCurrentCurrencyCode();
            $prices = $backend->preparePriceData2(
                $prices, 
                $product->getTypeId(), 
                $websiteId, 
                $currencyCode
            );
        }
        $product->setFinalPrice(null);
        $product->setData($attribute->getAttributeCode(), $prices);
        return $this;
    }
    /**
     * Get default compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $priceAttributeCode
     * @param string $attributeCode
     * @param array $parameters
     * 
     * @return mixed
     */
    public function _getDefaultCompoundPrice($product, $priceAttributeCode, $attributeCode, $parameters = array())
    {
        $price          = null;
        if (!isset($parameters['currency'])) {
            return $price;
        }
        $websiteId      = (isset($parameters['website_id'])) ? $parameters['website_id'] : null;
        $currencyCode   = $parameters['currency'];
        if (!$this->isGlobalScope() && $websiteId) {
            $prices         = $product->getData($attributeCode);
            if (isset($prices[0]) && isset($prices[0][$currencyCode])) {
                $price = (float) $prices[0][$currencyCode];
            }
        }
        if (is_null($price)) {
            $price = $product->getDataUsingMethod($priceAttributeCode);
            if (!is_null($price)) {
                $price          = $this->getCoreHelper()
                    ->getWebsiteById($websiteId)
                    ->getBaseCurrency()
                    ->convert($price, $currencyCode);
            }
        }
        return $price;
    }
    /**
     * Get compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeCode
     * @param array $parameters
     * 
     * @return mixed
     */
    protected function _getCompoundPrice($product, $attributeCode, $parameters = array())
    {
        $price          = null;
        $prices         = $product->getData($attributeCode);
        if (!count($prices)) {
            return $price;
        }
        $currencyHelper = $this->getCoreHelper()->getCurrencyHelper();
        $baseCurrency   = $currencyHelper->getCurrentStoreBase();
        $currency       = $currencyHelper->getCurrent();
        $websiteId      = (isset($parameters['website_id'])) ? 
            $parameters['website_id'] : $this->getProductHelper()->getWebsiteId($product);
        $currencyCode   = (isset($parameters['currency'])) ? 
            $parameters['currency'] : $currency->getCode();
        if (
            (!$this->isGlobalScope()) && $websiteId && 
            isset($prices[$websiteId]) && 
            isset($prices[$websiteId][$currencyCode])
        ) {
            $price          = $prices[$websiteId][$currencyCode];
        }
        if (is_null($price) && isset($prices[0]) && isset($prices[0][$currencyCode])) {
            $price          = $prices[0][$currencyCode];
        }
        if (!is_null($price)) {
            $rate           = $baseCurrency->getRate($currencyCode);
            if ($rate && ($rate != 1)) {
                $price          = $price / $rate;
            }
        }
        return $price;
    }
    /**
     * Save compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function _saveCompoundPrice($product, $dataTableName, $dataAttributeCode)
    {
        if (!$product || !($product instanceof Mage_Catalog_Model_Product)) {
            return $this;
        }
        $productHelper  = $this->getProductHelper();
        $productId      = (int) $product->getId();
        $resource       = $product->getResource();
        $websiteId      = $productHelper->getWebsiteId($product);
        $dataTable      = $resource->getTable($dataTableName);
        $adapter        = $resource->getWriteConnection();
        $_data          = array();
        foreach ((array) $product->getData($dataAttributeCode) as $datum) {
            if (is_array($datum) && isset($datum['currency'])) {
                array_push($_data, $datum);
            }
        }
        if (!count($_data)) {
            return $this;
        }
        $data           = array();
        $oldData        = array();
        foreach ($_data as $item) {
            if (
                isset($item['currency']) && 
                isset($item['price'])
            ) {
                $currencyCode   = $item['currency'];
                $price          = ($item['price'] && ($item['price'] > 0)) ? 
                    round((float) ($item['price']), 2) : 0;
                $data[$currencyCode] = array(
                    'product_id'    => $productId, 
                    'website_id'    => $websiteId, 
                    'currency'      => $currencyCode, 
                    'price'         => $price, 
                );
            }
        }
        $select         = $adapter->select()->from($dataTable)->where(implode(' AND ', array(
            "(product_id = {$adapter->quote($productId)})", 
            "(website_id = {$adapter->quote($websiteId)})"
        )));
        $query = $adapter->query($select);
        while ($item = $query->fetch()) {
            $currencyCode           = $item['currency'];
            $oldData[$currencyCode] = $item;
        }
        foreach ($oldData as $item) {
            $currencyCode           = $item['currency'];
            if (!isset($data[$currencyCode])) {
                $adapter->delete($dataTable, array(
                    $adapter->quoteInto('product_id = ?', $productId), 
                    $adapter->quoteInto('website_id = ?', $websiteId), 
                    $adapter->quoteInto('currency = ?', $currencyCode)
                ));
            }
        }
        foreach ($data as $item) {
            $currencyCode           = $item['currency'];
            if (!isset($oldData[$currencyCode])) {
                $adapter->insert($dataTable, $item);
            } else {
                $adapter->update($dataTable, $item, array(
                    $adapter->quoteInto('product_id = ?', $productId), 
                    $adapter->quoteInto('website_id = ?', $websiteId), 
                    $adapter->quoteInto('currency = ?', $currencyCode)
                ));
            }
        }
        return $this;
    }
    /**
     * Load compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function _loadCompoundPrice($product, $dataTableName, $dataAttributeCode)
    {
        $this->getProductHelper()->loadChildData3(
            $product, 
            $dataTableName, 
            $dataAttributeCode, 
            'website_id', 
            'currency', 
            'price', 
            'float'
        );
        return $this;
    }
    /**
     * Load collection compound price
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function _loadCollectionCompoundPrice($collection, $dataTableName, $dataAttributeCode)
    {
        $this->getProductHelper()->loadCollectionChildData3(
            $collection, 
            $dataTableName, 
            $dataAttributeCode, 
            'website_id', 
            'currency', 
            'price', 
            'float'
        );
        return $this;
    }
    /**
     * Round
     * 
     * @param float $price
     * 
     * @return float
     */
    public function round($price)
    {
        return round($price, 8);
    }
}