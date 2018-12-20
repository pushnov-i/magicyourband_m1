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
 * Configurable products price indexer resource
 *
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Catalog_Product_Indexer_Price_Configurable 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable 
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
    protected function getVersionHelper()
    {
        return $this->getCurrencyPricingHelper()
            ->getVersionHelper();
    }
    /**
     * Get price indexer helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product_Price_Indexer
     */
    protected function getProductPriceIndexerHelper()
    {
        return $this->getCurrencyPricingHelper()
            ->getProductPriceIndexerHelper();
    }
    /**
     * Prepare final price data
     * 
     * @param int|array $entityIds
     * 
     * @return self
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->getProductPriceIndexerHelper()
            ->prepareFinalPriceData(
                $this->_getWriteAdapter(), 
                $this->_getDefaultFinalPriceTable(), 
                $this->getTypeId(), 
                $entityIds
            );
        return $this;
    }
    /**
     * Apply custom option
     * 
     * @return self
     */
    protected function _applyCustomOption()
    {
        $this->getProductPriceIndexerHelper()
            ->applyCustomOption(
                $this->_getWriteAdapter(), 
                $this->_getDefaultFinalPriceTable(), 
                $this->_getCustomOptionAggregateTable(), 
                $this->_getCustomOptionPriceTable(), 
                $this->useIdxTable()
            );
        return $this;
    }
    /**
     * Apply configurable option
     *
     * @return self
     */
    protected function _applyConfigurableOption()
    {
        $this->getProductPriceIndexerHelper()
            ->applyConfigurableOption(
                $this->_getWriteAdapter(), 
                $this->_getDefaultFinalPriceTable(), 
                $this->_getConfigurableOptionAggregateTable(), 
                $this->_getConfigurableOptionPriceTable(), 
                $this->useIdxTable()
            );
        return $this;
    }
    /**
     * Mode price data to index table
     *
     * @return self
     */
    protected function _movePriceDataToIndexTable()
    {
        $this->getProductPriceIndexerHelper()
            ->movePriceDataToIndexTable(
                $this->_getWriteAdapter(), 
                $this->_getDefaultFinalPriceTable(), 
                $this->getIdxTable(), 
                $this->useIdxTable()
            );
        return $this;
    }
}