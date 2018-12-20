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
 * Rule product price
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Catalogrule_Rule_Product_Price 
    extends Mage_CatalogRule_Model_Rule_Product_Price 
{
    /**
     * Apply price rule price to price index table
     * 
     * @param Varien_Db_Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param string $websiteId
     * @param array $updateFields       the array fields for compare with rule price and update
     * @param string $websiteDate
     * 
     * @return Mage_CatalogRule_Model_Rule_Product_Price
     */
    public function applyPriceRuleToIndexTable(
        Varien_Db_Select $select, $indexTable, $entityId, $customerGroupId, 
        $websiteId, $updateFields, $websiteDate
    ) 
    {
        $this->_getResource()->applyPriceRuleToIndexTable(
            clone $select, $indexTable, $entityId, $customerGroupId, $websiteId, 
            $updateFields, $websiteDate
        );
        return $this;
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
     * @param array $updateFields       the array fields for compare with rule price and update
     * @param string $websiteDate
     * 
     * @return Mage_CatalogRule_Model_Rule_Product_Price
     */
    public function applyPriceRuleToIndexTable2(
        Varien_Db_Select $select, $indexTable, $entityId, $customerGroupId, $currency, $websiteId, 
        $updateFields, $websiteDate
    ) 
    {
        $this->_getResource()->applyPriceRuleToIndexTable2(
            clone $select, $indexTable, $entityId, $customerGroupId, $currency, $websiteId, 
            $updateFields, $websiteDate
        );
        return $this;
    }
}