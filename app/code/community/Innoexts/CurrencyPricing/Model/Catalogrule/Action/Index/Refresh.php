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
 * Catalog rule
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Catalogrule_Action_Index_Refresh 
    extends Mage_CatalogRule_Model_Action_Index_Refresh 
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
        return $this->getCurrencyPricingHelper()
            ->getVersionHelper();
    }
    /**
     * Get table
     * 
     * @param string $entityName
     * 
     * @return string
     */
    protected function getTable($entityName)
    {
        return $this->_resource->getTable($entityName);
    }
    /**
     * Run reindex
     */
    public function execute()
    {
        $this->_app->dispatchEvent('catalogrule_before_apply', array('resource' => $this->_resource));
        $coreDate   = $this->_factory->getModel('core/date');
        $timestamp  = $coreDate->gmtTimestamp('Today');
        foreach ($this->_app->getWebsites(false) as $website) {
            if ($website->getDefaultStore()) {
                $this->_reindex2($website, $timestamp);
            }
        }
        $this->_prepareGroupWebsite($timestamp);
        $this->_prepareAffectedProduct();
    }
    /**
     * Create temporary table
     */
    protected function _createTemporaryTable()
    {
        $adapter            = $this->_connection;
        $adapter->dropTemporaryTable($this->_getTemporaryTable());
        $table = $adapter->newTable($this->_getTemporaryTable())
            ->addColumn(
                'grouped_id',
                Varien_Db_Ddl_Table::TYPE_VARCHAR,
                80,
                array(),
                'Grouped ID'
            )
            ->addColumn(
                'product_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true
                ),
                'Product ID'
            )
            ->addColumn(
                'currency', 
                Varien_Db_Ddl_Table::TYPE_VARCHAR, 
                3, 
                array(
                    'nullable' => true, 
                    'default'  => null, 
                ),
                'Currency'
            )
            ->addColumn(
                'customer_group_id',
                Varien_Db_Ddl_Table::TYPE_SMALLINT,
                5,
                array(
                    'unsigned' => true
                ),
                'Customer Group ID'
            )
            ->addColumn(
                'from_date',
                Varien_Db_Ddl_Table::TYPE_DATE,
                null,
                array(),
                'From Date'
            )
            ->addColumn(
                'to_date',
                Varien_Db_Ddl_Table::TYPE_DATE,
                null,
                array(),
                'To Date'
            )
            ->addColumn(
                'action_amount',
                Varien_Db_Ddl_Table::TYPE_DECIMAL,
                '12,4',
                array(),
                'Action Amount'
            )
            ->addColumn(
                'action_operator',
                Varien_Db_Ddl_Table::TYPE_VARCHAR,
                10,
                array(),
                'Action Operator'
            )
            ->addColumn(
                'action_stop',
                Varien_Db_Ddl_Table::TYPE_SMALLINT,
                6,
                array(),
                'Action Stop'
            )
            ->addColumn(
                'sort_order',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                10,
                array(
                    'unsigned' => true
                ),
                'Sort Order'
            )
            ->addColumn(
                'price',
                Varien_Db_Ddl_Table::TYPE_DECIMAL,
                '12,4',
                array(),
                'Product Price'
            )
            ->addColumn(
                'rule_product_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true
                ),
                'Rule Product ID'
            )
            ->addColumn(
                'from_time',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true,
                    'nullable' => true,
                    'default'  => 0,
                ),
                'From Time'
            )
            ->addColumn(
                'to_time',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true,
                    'nullable' => true,
                    'default'  => 0,
                ),
                'To Time'
            )
            ->addIndex(
                $adapter->getIndexName($this->_getTemporaryTable(), 'grouped_id'),
                array('grouped_id')
            )
            ->setComment('CatalogRule Price Temporary Table');
        $adapter->createTemporaryTable($table);
    }
    /**
     * Prepare temporary data
     * 
     * @param Mage_Core_Model_Website $website
     * 
     * @return Varien_Db_Select
     */
    protected function _prepareTemporarySelect2(Mage_Core_Model_Website $website)
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $currencyHelper         = $helper->getCoreHelper()->getCurrencyHelper();
        $productFlatHelper      = $this->_factory->getHelper('catalog/product_flat');
        $eavConfig              = $this->_factory->getSingleton('eav/config');
        $priceAttribute         = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'price');
        $adapter                = $this->_connection;
        $websiteId              = $website->getId();
        $storeId                = $website->getDefaultStore()->getId();
        $select                 = $adapter->select()->from(
                array('rp' => $this->getTable('catalogrule/rule_product')), array())
            ->joinInner(array('r' => $this->getTable('catalogrule/rule')), 'r.rule_id = rp.rule_id', array())
            ->where('rp.website_id = ?', $websiteId)
            ->order(array(
                'rp.product_id', 
                'rp.currency', 
                'rp.customer_group_id', 
                'rp.sort_order', 
                'rp.rule_product_id', 
            ));
        $select->joinLeft(
            array('cr' => $this->getTable('directory/currency_rate')), 
            implode(' AND ', array(
                "(cr.currency_from = {$currencyHelper->getBaseDbExpr('rp.website_id')})", 
                '('.implode(' OR ', array(
                    '((rp.currency IS NOT NULL) AND (cr.currency_to = rp.currency))', 
                    '((rp.currency IS NULL) AND (cr.currency_to = cr.currency_from))', 
                )).')'
            )), 
            array()
        );
        $select->joinLeft(
            array('pgd' => $this->getTable('catalog/product_attribute_group_price')), 
            implode(' AND ', array(
                'pgd.entity_id = rp.product_id', 
                'pgd.customer_group_id = rp.customer_group_id', 
                'pgd.website_id = 0', 
                '(pgd.currency IS NULL) OR (pgd.currency = cr.currency_to)', 
            )), 
            array()
        );
        if (!$productPriceHelper->isGlobalScope()) {
            $select->joinLeft(
                array('pg' => $this->getTable('catalog/product_attribute_group_price')), 
                implode(' AND ', array(
                    'pg.entity_id = rp.product_id', 
                    'pg.customer_group_id = rp.customer_group_id', 
                    'pg.website_id = rp.website_id', 
                    '(pgd.currency IS NULL) OR (pgd.currency = cr.currency_to)', 
                )), 
                array()
            );
        }
        
        $customerGroupPriceExpr = new Zend_Db_Expr('pgd.value');
        if (!$productPriceHelper->isGlobalScope()) {
            $customerGroupPriceExpr = $adapter->getIfNullSql('pg.value', $customerGroupPriceExpr);
        }
        if ($productFlatHelper->isEnabled() && $productFlatHelper->isBuilt($storeId)) {
            $select->joinInner(
                array('p' => $this->getTable('catalog/product_flat').'_'.$storeId), 
                'p.entity_id = rp.product_id', 
                array()
            );
            $priceExpr = new Zend_Db_Expr('p.price');
        } else {
            $select->joinInner(
                array('pd' => $this->getTable(array('catalog/product', $priceAttribute->getBackendType()))), 
                implode(' AND ', array(
                    'pd.entity_id = rp.product_id', 
                    'pd.store_id = 0', 
                    'pd.attribute_id = '.$priceAttribute->getId(), 
                )), 
                array()
            );
            if (!$productPriceHelper->isGlobalScope()) {
                $select->joinLeft(
                    array('p' => $this->getTable(array('catalog/product', $priceAttribute->getBackendType()))), 
                    implode(' AND ', array(
                        'p.entity_id = rp.product_id', 
                        'p.store_id = '.$storeId, 
                        'p.attribute_id = '.$priceAttribute->getId(), 
                    )), 
                    array()
                );
            }
            $priceExpr = new Zend_Db_Expr('pd.value');
            if (!$productPriceHelper->isGlobalScope()) {
                $priceExpr = $adapter->getIfNullSql('p.value', $priceExpr);
            }
        }
        $select->joinLeft(
            array('pcpd' => $this->getTable('catalog/product_compound_price')), 
            implode(' AND ', array(
                '(pcpd.product_id = rp.product_id)', 
                '(pcpd.currency = cr.currency_to)', 
                '(pcpd.website_id = 0)', 
            )), 
            array()
        );
        if (!$productPriceHelper->isGlobalScope()) {
            $select->joinLeft(
                array('pcp' => $this->getTable('catalog/product_compound_price')), 
                implode(' AND ', array(
                    '(pcp.product_id = rp.product_id)', 
                    '(pcp.currency = cr.currency_to)', 
                    '(pcp.website_id = '.$websiteId.')', 
                )), 
                array()
            );
        }
        $rateExpr               = new Zend_Db_Expr('cr.rate');
        $compoundPriceExpr      = $adapter->getCheckSql(
            'pcpd.price IS NOT NULL', 
            'ROUND(pcpd.price / '.$rateExpr.', 4)', 
            $priceExpr
        );
        if (!$productPriceHelper->isGlobalScope()) {
            $compoundPriceExpr      = $adapter->getCheckSql(
                'pcp.price IS NOT NULL', 
                'ROUND(pcp.price / '.$rateExpr.', 4)', 
                $compoundPriceExpr
            );
        }
        $priceExpr = $adapter->getIfNullSql($customerGroupPriceExpr, $compoundPriceExpr);
        $select->columns(
            array(
                'grouped_id'        => $adapter->getConcatSql(
                    array(
                        'rp.product_id', 
                        'rp.currency', 
                        'rp.customer_group_id', 
                    ), '-'
                ), 
                'product_id'        => 'rp.product_id', 
                'currency'          => 'rp.currency', 
                'customer_group_id' => 'rp.customer_group_id', 
                'from_date'         => 'r.from_date', 
                'to_date'           => 'r.to_date', 
                'action_amount'     => 'rp.action_amount', 
                'action_operator'   => 'rp.action_operator', 
                'action_stop'       => 'rp.action_stop', 
                'sort_order'        => 'rp.sort_order', 
                'price'             => $priceExpr, 
                'rule_product_id'   => 'rp.rule_product_id', 
                'from_time'         => 'rp.from_time', 
                'to_time'           => 'rp.to_time', 
            )
        );
        return $select;
    }
    /**
     * Prepare price column
     * 
     * @return Zend_Db_Expr
     */
    protected function _calculatePrice()
    {
        $adapter            = $this->_connection;
        $toPercent          = $adapter->quote('to_percent');
        $byPercent          = $adapter->quote('by_percent');
        $toFixed            = $adapter->quote('to_fixed');
        $byFixed            = $adapter->quote('by_fixed');
        $nA                 = $adapter->quote('N/A');
        $groupIdExpr        = $adapter->getIfNullSql(new Zend_Db_Expr('@group_id'), $nA);
        $actionStopExpr     = $adapter->getIfNullSql(new Zend_Db_Expr('@action_stop'), new Zend_Db_Expr(0));
        return $adapter->getCaseSql('', 
            array(
                $groupIdExpr.' != cppt.grouped_id' => '@price := '.$adapter->getCaseSql(
                    $adapter->quoteIdentifier('cppt.action_operator'), 
                    array(
                        $toPercent => new Zend_Db_Expr('cppt.price * cppt.action_amount / 100'), 
                        $byPercent => new Zend_Db_Expr('cppt.price * (1 - cppt.action_amount / 100)'), 
                        $toFixed   => $adapter->getCheckSql(
                            new Zend_Db_Expr('cppt.action_amount < cppt.price'), 
                            new Zend_Db_Expr('cppt.action_amount'), 
                            new Zend_Db_Expr('cppt.price')
                        ),
                        $byFixed   => $adapter->getCheckSql(
                            new Zend_Db_Expr('0 > cppt.price - cppt.action_amount'), 
                            new Zend_Db_Expr('0'), 
                            new Zend_Db_Expr('cppt.price - cppt.action_amount')
                        ),
                    )
                ),
                $groupIdExpr.' = cppt.grouped_id AND '.$actionStopExpr.' = 0' => '@price := '.$adapter->getCaseSql(
                    $adapter->quoteIdentifier('cppt.action_operator'), 
                    array(
                        $toPercent => new Zend_Db_Expr('@price * cppt.action_amount / 100'), 
                        $byPercent => new Zend_Db_Expr('@price * (1 - cppt.action_amount / 100)'), 
                        $toFixed   => $adapter->getCheckSql(
                            new Zend_Db_Expr('cppt.action_amount < @price'), 
                            new Zend_Db_Expr('cppt.action_amount'), 
                            new Zend_Db_Expr('@price')
                        ), 
                        $byFixed   => $adapter->getCheckSql(
                            new Zend_Db_Expr('0 > @price - cppt.action_amount'), 
                            new Zend_Db_Expr('0'), 
                            new Zend_Db_Expr('@price - cppt.action_amount')
                        ), 
                    )
                )
            ),
            '@price := @price'
        );
    }
    /**
     * Prepare index select
     *
     * @param Mage_Core_Model_Website $website
     * @param $time
     * 
     * @return Varien_Db_Select
     */
    protected function _prepareIndexSelect2(Mage_Core_Model_Website $website, $time)
    {
        $websiteId          = $website->getId();
        $adapter            = $this->_connection;
        $nA                 = $adapter->quote('N/A');
        $adapter->query('SET @price := NULL');
        $adapter->query('SET @group_id := NULL');
        $adapter->query('SET @action_stop := NULL');
        $groupIdExpr        = $adapter->getIfNullSql(new Zend_Db_Expr('@group_id'), $nA);
        $actionStopExpr     = $adapter->getIfNullSql(new Zend_Db_Expr('@action_stop'), new Zend_Db_Expr(0));
        $indexSelect        = $adapter->select()
            ->from(array('cppt' => $this->_getTemporaryTable()), array())
            ->order(array(
                'cppt.grouped_id', 
                'cppt.sort_order', 
                'cppt.rule_product_id'
            ))
            ->columns(array(
                'customer_group_id'     => 'cppt.customer_group_id', 
                'product_id'            => 'cppt.product_id', 
                'currency'              => 'cppt.currency', 
                'rule_price'            => $this->_calculatePrice(), 
                'latest_start_date'     => 'cppt.from_date', 
                'earliest_end_date'     => 'cppt.to_date', 
                new Zend_Db_Expr(
                    $adapter->getCaseSql('', 
                        array(
                            $groupIdExpr.' != cppt.grouped_id' => new Zend_Db_Expr('@action_stop := cppt.action_stop'), 
                            $groupIdExpr.' = cppt.grouped_id' => '@action_stop := '.$actionStopExpr.' + cppt.action_stop', 
                        )
                    )
                ), 
                new Zend_Db_Expr('@group_id := cppt.grouped_id'), 
                'from_time'         => 'cppt.from_time', 
                'to_time'           => 'cppt.to_time', 
            ));
        $select = $adapter->select()
            ->from($indexSelect, array())
            ->joinInner(
                array(
                    'dates' => $adapter->select()->union(
                        array(
                            new Zend_Db_Expr(
                                'SELECT '.$adapter->getDateAddSql(
                                    $adapter->fromUnixtime($time), -1, Varien_Db_Adapter_Interface::INTERVAL_DAY
                                ).' AS rule_date'
                            ), 
                            new Zend_Db_Expr('SELECT '.$adapter->fromUnixtime($time).' AS rule_date'), 
                            new Zend_Db_Expr(
                                'SELECT '.$adapter->getDateAddSql(
                                    $adapter->fromUnixtime($time), 1, Varien_Db_Adapter_Interface::INTERVAL_DAY
                                ).' AS rule_date'
                            ), 
                        )
                    )
                ), '1=1', array()
            )
            ->columns(array(
                'rule_product_price_id' => new Zend_Db_Expr('NULL'), 
                'rule_date'             => 'dates.rule_date', 
                'customer_group_id'     => 'customer_group_id', 
                'product_id'            => 'product_id', 
                'currency'              => 'currency', 
                'rule_price'            => 'MIN(rule_price)', 
                'website_id'            => new Zend_Db_Expr($websiteId), 
                'latest_start_date'     => 'latest_start_date', 
                'earliest_end_date'     => 'earliest_end_date', 
            ))
            ->where(new Zend_Db_Expr($adapter->getUnixTimestamp('dates.rule_date')." >= from_time"))
            ->where($adapter->getCheckSql(
                new Zend_Db_Expr('to_time = 0'), 
                new Zend_Db_Expr(1), 
                new Zend_Db_Expr($adapter->getUnixTimestamp('dates.rule_date')." <= to_time")
            ))
            ->group(array(
                'customer_group_id', 
                'product_id', 
                'currency', 
                'dates.rule_date'
            ));
        return $select;
    }
    /**
     * Remove old index data
     *
     * @param Mage_Core_Model_Website $website
     */
    protected function _removeOldIndexData2(Mage_Core_Model_Website $website)
    {
        $adapter = $this->_connection;
        $adapter->delete(
            $this->getTable('catalogrule/rule_product_price'), array('website_id = ?' => $website->getId())
        );
    }
    /**
     * Fill Index Data
     *
     * @param Mage_Core_Model_Website $website
     * @param int $time
     */
    protected function _fillIndexData2(Mage_Core_Model_Website $website, $time)
    {
        $adapter = $this->_connection;
        $adapter->query(
            $adapter->insertFromSelect(
                $this->_prepareIndexSelect2($website, $time), $this->getTable('catalogrule/rule_product_price')
            )
        );
    }
    /**
     * Reindex catalog prices by website for timestamp
     *
     * @param Mage_Core_Model_Website $website
     * @param int $timestamp
     */
    protected function _reindex2(Mage_Core_Model_Website $website, $timestamp)
    {
        $adapter = $this->_connection;
        $this->_createTemporaryTable();
        $adapter->query(
            $adapter->insertFromSelect($this->_prepareTemporarySelect2($website), $this->_getTemporaryTable())
        );
        $this->_removeOldIndexData2($website);
        $this->_fillIndexData2($website, $timestamp);
    }
}