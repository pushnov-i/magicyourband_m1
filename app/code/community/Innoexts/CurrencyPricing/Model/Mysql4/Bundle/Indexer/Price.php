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
 * Bundle products price indexer resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Bundle_Indexer_Price 
    extends Mage_Bundle_Model_Mysql4_Indexer_Price 
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
     * Prepare bundle price by type
     *
     * @param int $priceType
     * @param int|array $entityIds
     * 
     * @return self
     */
    protected function _prepareBundlePriceByType($priceType, $entityIds = null)
    {
        $this->getProductPriceIndexerHelper()
            ->prepareBundlePriceByType(
                $this->_getWriteAdapter(), 
                $this->_getBundlePriceTable(), 
                $this->getTypeId(), 
                $priceType, 
                $entityIds
            );
        return $this;
    }
    /**
     * Calculate bundle selection price
     *
     * @param int $priceType
     * 
     * @return self
     */
    protected function _calculateBundleSelectionPrice($priceType)
    {
        $this->getProductPriceIndexerHelper()
            ->calculateBundleSelectionPrice(
                $this->_getWriteAdapter(), 
                $this->_getBundlePriceTable(), 
                $this->getIdxTable(), 
                $this->_getBundleSelectionTable(), 
                $priceType
            );
        return $this;
    }
    /**
     * Calculate bundle option price
     *
     * @return self
     */
    protected function _calculateBundleOptionPrice()
    {
        $this->getProductPriceIndexerHelper()
            ->calculateBundleOptionPrice(
                $this->_getWriteAdapter(), 
                $this->_getDefaultFinalPriceTable(), 
                $this->_getBundlePriceTable(), 
                $this->getIdxTable(), 
                $this->_getBundleSelectionTable(), 
                $this->_getBundleOptionTable()
            );
        return $this;
    }
    /**
     * Prepare tier price
     *
     * @param int|array $entityIds
     * 
     * @return self
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $this->getProductPriceIndexerHelper()
            ->prepareBundleTierPriceIndex(
                $this->_getWriteAdapter(), 
                $this->_getTierPriceIndexTable(), 
                $this->getTypeId(), 
                $entityIds
            );
        return $this;
    }
    /**
     * Prepare percentage group price for bundle products
     *
     * @param int|array $entityIds
     * 
     * @return self
     */
    protected function _prepareGroupPriceIndex($entityIds = null)
    {
        $this->getProductPriceIndexerHelper()
            ->prepareBundleGroupPriceIndex(
                $this->_getWriteAdapter(), 
                $this->_getGroupPriceIndexTable(), 
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
    /**
     * Prepare bundle price
     *
     * @param int|array $entityIds
     * 
     * @return self
     */
    protected function _prepareBundlePrice($entityIds = null)
    {
        $this->getProductPriceIndexerHelper()
            ->prepareBundlePrice(
                $this->_getWriteAdapter(), 
                $this->_getDefaultFinalPriceTable(), 
                $this->_getBundlePriceTable(), 
                $this->getIdxTable(), 
                $this->_getCustomOptionAggregateTable(), 
                $this->_getCustomOptionPriceTable(), 
                $this->_getBundleSelectionTable(), 
                $this->_getBundleOptionTable(), 
                $this->_getTierPriceIndexTable(), 
                (($this->getVersionHelper()->isGe1700()) ? $this->_getGroupPriceIndexTable() : null), 
                $this->getTypeId(), 
                $this->useIdxTable(), 
                $entityIds
            );
        return $this;
    }
}