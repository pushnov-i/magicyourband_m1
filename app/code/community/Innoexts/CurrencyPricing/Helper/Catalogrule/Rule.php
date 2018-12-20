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
 * Catalog rule helper
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Helper_Catalogrule_Rule 
    extends Innoexts_Core_Helper_Abstract 
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
     * Get version helper
     * 
     * @return Innoexts_Core_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getCoreHelper()->getVersionHelper();
    }
    /**
     * Get model helper
     * 
     * @return Innoexts_Core_Helper_Model
     */
    public function getModelHelper()
    {
        return $this->getCoreHelper()->getModelHelper();
    }
    /**
     * Save child data
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    protected function saveChildData2(
        $rule, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    )
    {
        $this->getModelHelper()->saveChildData2(
            $rule, 
            'Mage_CatalogRule_Model_Rule', 
            'rule_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataKeyAttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Save compound amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function saveCompoundAmount($rule, $dataTableName, $dataAttributeCode)
    {
        $this->saveChildData2($rule, $dataTableName, $dataAttributeCode, 'currency', 'amount', 'float');
        return $this;
    }
    /**
     * Save compound discount amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * 
     * @return self
     */
    public function saveCompoundDiscountAmount($rule)
    {
        return $this->saveCompoundAmount(
            $rule, 'catalogrule/compound_discount_amount', 'compound_discount_amounts'
        );
    }
    /**
     * Save compound sub discount amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * 
     * @return self
     */
    public function saveCompoundSubDiscountAmount($rule)
    {
        if ($this->getVersionHelper()->isGe1610()) {
            return $this->saveCompoundAmount(
                $rule, 'catalogrule/compound_sub_discount_amount', 'compound_sub_discount_amounts'
            );
        }
        return $this;
    }
    /**
     * Load child data
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    protected function loadChildData2(
        $rule, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    )
    {
        $this->getModelHelper()->loadChildData2(
            $rule, 
            'Mage_CatalogRule_Model_Rule', 
            'rule_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataKeyAttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Load compound amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function loadCompoundAmount($rule, $dataTableName, $dataAttributeCode)
    {
        $this->loadChildData2($rule, $dataTableName, $dataAttributeCode, 'currency', 'amount', 'float');
        return $this;
    }
    /**
     * Load compound discount amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * 
     * @return self
     */
    public function loadCompoundDiscountAmount($rule)
    {
        return $this->loadCompoundAmount(
            $rule, 'catalogrule/compound_discount_amount', 'compound_discount_amounts'
        );
        return $this;
    }
    /**
     * Load compound sub discount amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * 
     * @return self
     */
    public function loadCompoundSubDiscountAmount($rule)
    {
        if ($this->getVersionHelper()->isGe1610()) {
            return $this->loadCompoundAmount(
                $rule, 'catalogrule/compound_sub_discount_amount', 'compound_sub_discount_amounts'
            );
        }
        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    protected function loadCollectionChildData2(
        $collection, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    )
    {
        $this->getModelHelper()->loadCollectionChildData2(
            $collection, 
            'rule_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataKeyAttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Load collection compound amount
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function loadCollectionCompoundAmount($collection, $dataTableName, $dataAttributeCode)
    {
        $this->loadCollectionChildData2(
            $collection, $dataTableName, $dataAttributeCode, 'currency', 'amount', 'float'
        );
        return $this;
    }
    /**
     * Load collection compound discount amount
     * 
     * @param Mage_CatalogRule_Model_Mysql4_Rule_Collection $collection
     * 
     * @return self
     */
    public function loadCollectionCompoundDiscountAmount($collection)
    {
        $this->loadCollectionCompoundAmount(
            $collection, 'catalogrule/compound_discount_amount', 'compound_discount_amounts'
        );
        return $this;
    }
    /**
     * Load collection compound sub discount amount
     * 
     * @param Mage_CatalogRule_Model_Mysql4_Rule_Collection $collection
     * 
     * @return self
     */
    public function loadCollectionCompoundSubDiscountAmount($collection)
    {
        if ($this->getVersionHelper()->isGe1610()) {
            $this->loadCollectionCompoundAmount(
                $collection, 'catalogrule/compound_sub_discount_amount', 'compound_sub_discount_amounts'
            );
        }
        return $this;
    }
    /**
     * Remove child data
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function removeChildData($rule, $dataAttributeCode)
    {
        $this->getModelHelper()->removeChildData($rule, 'Mage_CatalogRule_Model_Rule', $dataAttributeCode);
        return $this;
    }
    /**
     * Remove compound amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function removeCompoundAmount($rule, $dataAttributeCode)
    {
        $this->removeChildData($rule, $dataAttributeCode);
        return $this;
    }
    /**
     * Remove compound discount amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * 
     * @return self
     */
    public function removeCompoundDiscountAmount($rule)
    {
        return $this->removeCompoundAmount($rule, 'compound_discount_amounts');
    }
    /**
     * Remove compound sub discount amount
     * 
     * @param  Mage_CatalogRule_Model_Rule $rule
     * 
     * @return self
     */
    public function removeCompoundSubDiscountAmount($rule)
    {
        if ($this->getVersionHelper()->isGe1610()) {
            return $this->removeCompoundAmount($rule, 'compound_sub_discount_amounts');
        }
        return $this;
    }
    /**
     * Get compound amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $attributeCode
     * @param string $actionCode
     * @param int $websiteId
     * @param string $currencyCode
     * 
     * @return float
     */
    protected function _getCompoundAmount($rule, $attributeCode, $actionCode, $websiteId, $currencyCode)
    {
        $amount             = $rule->getData($attributeCode);
        $compoundAmounts    = $rule->getData("compound_{$attributeCode}s");
        if (count($compoundAmounts) && isset($compoundAmounts[$currencyCode])) {
            $amount             = (float) $compoundAmounts[$currencyCode];
            $action             = $rule->getData($actionCode);
            if (!in_array($action, array('to_percent', 'by_percent'))) {
                $rate               = $this->getCoreHelper()
                    ->getWebsiteById($websiteId)
                    ->getBaseCurrency()
                    ->getRate($currencyCode);
                if ($rate && ($rate != 1)) {
                    $amount = $amount / $rate;
                }
            }
        }
        return $amount;
    }
    /**
     * Get compound discount amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param int $websiteId
     * @param string $currencyCode
     * 
     * @return float
     */
    public function getCompoundDiscountAmount($rule, $websiteId, $currencyCode)
    {
        return $this->_getCompoundAmount(
            $rule, 
            'discount_amount', 
            'simple_action', 
            $websiteId, 
            $currencyCode
        );
    }
    /**
     * Get compound sub discount amount
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param int $websiteId
     * @param string $currencyCode
     * 
     * @return float
     */
    public function getCompoundSubDiscountAmount($rule, $websiteId, $currencyCode)
    {
        return $this->_getCompoundAmount(
            $rule, 
            'sub_discount_amount', 
            'sub_simple_action', 
            $websiteId, 
            $currencyCode
        );
    }
    /**
     * Round price
     *
     * @param mixed $price
     * 
     * @return float
     */
    public function roundPrice($price)
    {
        return round($price, 4);
    }
}