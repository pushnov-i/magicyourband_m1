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
 * Catalog rule resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Catalogrule_Rule_Product_Price 
    extends Mage_CatalogRule_Model_Mysql4_Rule_Product_Price 
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
        return $this->getCurrencyPricingHelper()->getVersionHelper();
    }
    /**
     * Apply price rule price to price index table
     *
     * @param Varien_Db_Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param string $currency
     * @param string $websiteId
     * @param array $updateFields       the array of fields for compare with rule price and update
     * @param string $websiteDate
     * 
     * @return Mage_CatalogRule_Model_Resource_Rule_Product_Price
     */
    public function applyPriceRuleToIndexTable2(
        Varien_Db_Select $select, $indexTable, $entityId, $customerGroupId, $currency, $websiteId, 
        $updateFields, $websiteDate
    ) 
    {
        if (empty($updateFields)) {
            return $this;
        }
        if (is_array($indexTable)) {
            foreach ($indexTable as $k => $v) {
                if (is_string($k)) {
                    $indexAlias = $k;
                } else {
                    $indexAlias = $v;
                }
                break;
            }
        } else {
            $indexAlias = $indexTable;
        }
        if ($this->getVersionHelper()->isGe1600()) {
            $where = implode(' AND ', array(
                "(rp.product_id = {$entityId})", 
                "(rp.website_id = {$websiteId})", 
                "(rp.customer_group_id = {$customerGroupId})", 
                "(rp.currency = {$currency})", 
            ));
            $select->join(array('rp' => $this->getMainTable()), "rp.rule_date = {$websiteDate}", array())
                ->where($where);
        } else {
            $select->join(
                array('rp' => $this->getMainTable()),
                "rp.product_id = {$entityId} AND rp.website_id = {$websiteId}".
                " AND rp.customer_group_id = {$customerGroupId}".
                " AND rp.currency = {$currency}".
                " AND rp.rule_date = {$websiteDate}",
                array());
        }
        foreach ($updateFields as $priceField) {
            $priceCond = $this->_getWriteAdapter()->quoteIdentifier(array($indexAlias, $priceField));
            if ($this->getVersionHelper()->isGe1600()) {
                $priceExpr = $this->_getWriteAdapter()->getCheckSql("rp.rule_price < {$priceCond}", 'rp.rule_price', $priceCond);
            } else {
                $priceExpr = new Zend_Db_Expr("IF(rp.rule_price < {$priceCond}, rp.rule_price, {$priceCond})");
            }
            $select->columns(array($priceField => $priceExpr));
        }
        $query = $select->crossUpdateFromSelect($indexTable);
        $this->_getWriteAdapter()->query($query);
        return $this;
    }
}