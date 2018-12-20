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
 * Product price indexer helper
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Helper_Catalog_Product_Price_Indexer 
    extends Innoexts_Core_Helper_Catalog_Product_Price_Indexer 
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
     * Get price helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product_Price
     */
    public function getPriceHelper()
    {
        return Mage::helper('currencypricing/catalog_product_price');
    }
    /**
     * Get product helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product
     */
    public function getProductHelper()
    {
        return $this->getPriceHelper()->getProductHelper();
    }
    /**
     * Get base currency expression
     * 
     * @param Zend_Db_Expr|string $websiteExpr
     * 
     * @return Zend_Db_Expr 
     */
    public function getBaseCurrencyExpr($websiteExpr)
    {
        return $this->getCurrencyPricingHelper()
            ->getCoreHelper()
            ->getCurrencyHelper()
            ->getBaseDbExpr($websiteExpr);
    }
    /**
     * Get currency expression
     * 
     * @param Zend_Db_Expr|string $websiteExpr
     * 
     * @return Zend_Db_Expr
     */
    public function getCurrencyExpr($websiteExpr)
    {
        return $this->getCurrencyPricingHelper()
            ->getCoreHelper()
            ->getCurrencyHelper()
            ->getDbExpr($websiteExpr);
    }
    /**
     * Add currency rate to select
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    public function addCurrencyRateJoin($select)
    {
        $tableAlias     = 'cr';
        $table          = $this->getTable('directory/currency_rate');
        $select->joinLeft(
            array($tableAlias => $table), 
            "({$tableAlias}.currency_from = {$this->getBaseCurrencyExpr('cw.website_id')})", 
            array()
        );
        return $this;
    }
    /**
     * Get compound price join additional conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getCompoundPriceJoinAdditionalConditions($tableAlias)
    {
        return array(
            "({$tableAlias}.currency = {$this->getCurrencyExpr('cw.website_id')})", 
            "({$tableAlias}.website_id = cw.website_id)", 
        );
    }
    /**
     * Get tier price join additional conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getTierPriceJoinAdditionalConditions($tableAlias)
    {
        return array(
            "({$tableAlias}.website_id = cw.website_id)", 
            "({$tableAlias}.currency = {$this->getCurrencyExpr('cw.website_id')})", 
        );
    }
    /**
     * Get group price join additional conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getGroupPriceJoinAdditionalConditions($tableAlias)
    {
        return array(
            "({$tableAlias}.website_id = cw.website_id)", 
            "({$tableAlias}.currency = {$this->getCurrencyExpr('cw.website_id')})", 
        );
    }
    /**
     * Add store join
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addStoreJoin($select)
    {
        $select->join(
                array('csg' => $this->getTable('core/store_group')), 
                'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id', 
                array()
            )->join(
                array('cs' => $this->getTable('core/store')), 
                'csg.default_store_id = cs.store_id AND cs.store_id != 0', 
                array()
            );
        return $this;
    }
    /**
     * Add price joins
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addPriceJoins($select)
    {
        $this->addCurrencyRateJoin($select);
        parent::addPriceJoins($select);
        return $this;
    }
    /**
     * Get final price select additional columns
     * 
     * @return array
     */
    protected function getFinalPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => $this->getCurrencyExpr('cw.website_id'), 
        );
    }
    /**
     * Get prepare product index select event additional data
     * 
     * @return array
     */
    protected function getPrepareProductIndexSelectEventAdditionalData()
    {
        return array(
            'currency_field' => $this->getCurrencyExpr('cw.website_id'), 
        );
    }
    /**
     * Get prepare product index table event additional data
     * 
     * @return array
     */
    protected function getPrepareProductIndexTableEventAdditionalData()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Add option select store join
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addOptionSelectStoreJoin($select)
    {
        parent::addOptionSelectStoreJoin($select);
        return $this;
    }
    /**
     * Get option type price select additional columns
     * 
     * @return array
     */
    protected function getOptionTypePriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Get option type price select group additional columns
     * 
     * @return array
     */
    protected function getOptionTypePriceSelectGroupAdditionalColumns()
    {
        return array('i.currency');
    }
    /**
     * Get option price select additional columns
     * 
     * @return array
     */
    protected function getOptionPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Get aggregated option price select additional columns
     * 
     * @return array
     */
    protected function getAggregatedOptionPriceSelectAdditionalColumns()
    {
        return array('currency');
    }
    /**
     * Get aggregated option price select group additional columns
     * 
     * @return array
     */
    protected function getAggregatedOptionPriceSelectGroupAdditionalColumns()
    {
        return array('currency');
    }
    /**
     * Get option final price select join additional conditions
     * 
     * @return array
     */
    protected function getOptionFinalPriceSelectJoinAdditionalConditions()
    {
        return array(
            '(i.currency = io.currency)', 
        );
    }
    /**
     * Get price select additional columns
     * 
     * @return array
     */
    protected function getPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'currency', 
        );
    }
    /**
     * Get configurable option price select additional columns
     * 
     * @return array
     */
    protected function getConfigurableOptionPriceSelectAdditionalColumns()
    {
        if ($this->getVersionHelper()->isGe1600()) {
            return array(
                'currency' => 'i.currency', 
            );
        } else {
            return array('i.currency');
        }
    }
    /**
     * Get configurable option price select group additional columns
     * 
     * @return array
     */
    protected function getConfigurableOptionPriceSelectGroupAdditionalColumns()
    {
        return array('i.currency');
    }
    /**
     * Get aggregated configurable option price select join additional columns
     * 
     * @return array
     */
    protected function getAggregatedConfigurableOptionPriceSelectJoinAdditionalColumns()
    {
        return array('currency');
    }
    /**
     * Get aggregated configurable option price select join group additional columns
     * 
     * @return array
     */
    protected function getAggregatedConfigurableOptionPriceSelectJoinGroupAdditionalColumns()
    {
        return array('currency');
    }
    /**
     * Get configurable option final price select join additional conditions
     * 
     * @return array
     */
    protected function getConfigurableOptionFinalPriceSelectJoinAdditionalConditions()
    {
        return array(
            '(i.currency = io.currency)', 
        );
    }
    /**
     * Get grouped product price select additional columns
     * 
     * @return array
     */
    protected function getGroupedProductPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Get grouped product price select group additional columns
     * 
     * @return array
     */
    protected function getGroupedProductPriceSelectGroupAdditionalColumns()
    {
        return array('i.currency');
    }
    /**
     * Get downloadable link price select additional columns
     * 
     * @return array
     */
    protected function getDownloadableLinkPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Get downloadable link price select group additional columns
     * 
     * @return array
     */
    protected function getDownloadableLinkPriceSelectGroupAdditionalColumns()
    {
        return array('i.currency');
    }
    /**
     * Get bundle price select additional columns
     * 
     * @return array
     */
    protected function getBundlePriceSelectAdditionalColumns()
    {
        return array(
            'currency' => $this->getCurrencyExpr('cw.website_id'), 
        );
    }
    /**
     * Get bundle selection price select additional columns
     * 
     * @return array
     */
    protected function getBundleSelectionPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Get bundle selection price select index join additional conditions
     * 
     * @return array
     */
    protected function getBundleSelectionPriceSelectIndexJoinAdditionalConditions()
    {
        return array(
            '(i.currency = idx.currency)', 
        );
    }
    /**
     * Get bundle option price select additional columns
     * 
     * @return array
     */
    protected function getBundleOptionPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Get bundle option price select group additional columns
     * 
     * @return array
     */
    protected function getBundleOptionPriceSelectGroupAdditionalColumns()
    {
        return array('currency');
    }
    /**
     * Get bundle final price select group additional columns
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectGroupAdditionalColumns()
    {
        return array('io.currency');
    }
    /**
     * Get bundle final price select price join additional conditions
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectPriceJoinAdditionalConditions()
    {
        return array(
            '(i.currency = io.currency)', 
        );
    }
    /**
     * Get bundle final price select additional columns
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => 'i.currency', 
        );
    }
    /**
     * Get tier price select additional columns
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => $this->getCurrencyExpr('cw.website_id'), 
        );
    }
    /**
     * Get bundle tier price select group additional columns
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectGroupAdditionalColumns()
    {
        return array($this->getCurrencyExpr('cw.website_id'));
    }
    /**
     * Get bundle tier price select additional conditions
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectAdditionalConditions()
    {
        return parent::getBundleTierPriceSelectAdditionalConditions();
    }
    /**
     * Add bundle tier price select additional joins
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addBundleTierPriceSelectAdditionalJoins($select)
    {
        $select->joinLeft(
                array('cr' => $this->getTable('directory/currency_rate')), 
                implode(' AND ', array(
                    "(cr.currency_from = {$this->getBaseCurrencyExpr('cw.website_id')})", 
                    "((tp.currency IS NULL) OR (tp.currency = cr.currency_to))"
                )), array()
            );
        return $this;
    }
    /**
     * Get bundle group price select additional columns
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectAdditionalColumns()
    {
        return array(
            'currency' => $this->getCurrencyExpr('cw.website_id'), 
        );
    }
    /**
     * Get bundle group price select group additional columns
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectGroupAdditionalColumns()
    {
        return array($this->getCurrencyExpr('cw.website_id'));
    }
    /**
     * Get bundle group price select additional conditions
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectAdditionalConditions()
    {
        return parent::getBundleGroupPriceSelectAdditionalConditions();
    }
    /**
     * Add bundle group price select additional joins
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addBundleGroupPriceSelectAdditionalJoins($select)
    {
        $select->joinLeft(
                array('cr' => $this->getTable('directory/currency_rate')), 
                implode(' AND ', array(
                    "(cr.currency_from = {$this->getBaseCurrencyExpr('cw.website_id')})", 
                    "((gp.currency IS NULL) OR (gp.currency = cr.currency_to))"
                )), array()
            );
        return $this;
    }
    /**
     * Add price index filter
     * 
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function addPriceIndexFilter($collection)
    {
        if (!$collection) {
            return null;
        }
        $select         = $collection->getSelect();
        $fromPart       = $select->getPart(Zend_Db_Select::FROM);
        $helper         = $this->getCurrencyPricingHelper();
        $currencyHelper = $helper->getCoreHelper()
            ->getCurrencyHelper();
        $connection     = $collection->getConnection();
        if (isset($fromPart['price_index'])) {
            $joinCond       = $fromPart['price_index']['joinCondition'];
            
            $currencyCode   = null;
            if (!$collection->getFlag('currency')) {
                $currencyCode   = $currencyHelper->getCurrentCode();
            } else {
                $currencyCode   = $collection->getFlag('currency');
            }
            $currencyCode   = $connection->quote($currencyCode);
            if (strpos($joinCond, 'price_index.currency') === false) {
                $joinCond .= " AND ((price_index.currency IS NULL) OR (price_index.currency = {$currencyCode}))";
            }
            
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $select->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        return $collection;
    }
}