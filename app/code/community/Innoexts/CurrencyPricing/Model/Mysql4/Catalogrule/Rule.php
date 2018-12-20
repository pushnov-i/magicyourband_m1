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
 * @license     http://innoexts.com/commercial-license-agreement InnoExts Commercial License
 */

/**
 * Catalog rule resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Catalogrule_Rule 
    extends Mage_CatalogRule_Model_Mysql4_Rule 
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
     * Get product price helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product_Price
     */
    public function getProductPriceHelper()
    {
        return $this->getCurrencyPricingHelper()->getProductPriceHelper();
    }
    /**
     * Constructor
     */
    protected function _construct()
    {
        if ($this->getVersionHelper()->isGe1700()) {
            $this->_associatedEntitiesMap['currency'] = array(
                'associations_table'    => 'catalogrule/currency', 
                'rule_id_field'         => 'rule_id', 
                'entity_id_field'       => 'currency', 
            );
        }
        parent::_construct();
    }
    /**
     * Get write adapter
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getWriteConnection()
    {
        return $this->_getWriteAdapter();
    }
    /**
     * Get currencies by rule identifier
     *
     * @param int $ruleId
     * 
     * @return array
     */
    public function getCurrencies($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'currency');
    }
    /**
     * After load
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return self
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($this->getVersionHelper()->isGe1700()) {
            $object->setData('currencies', (array) $this->getCurrencies($object->getId()));
        }
        parent::_afterLoad($object);
        return $this;
    }
    /**
     * After save
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return self
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($this->getVersionHelper()->isGe1700()) {
            if ($object->hasCurrencies()) {
                $currencies = $object->getCurrencies();
                if (!is_array($currencies)) {
                    $currencies = explode(',', (string) $currencies);
                }
                $this->bindRuleToEntity($object->getId(), $currencies, 'currency');
            }
        }
        parent::_afterSave($object);
        return $this;
    }
    /**
     * Return whether the product fits the rule
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param Varien_Object $product
     * @param array $websiteIds
     * 
     * @return bool
     */
    public function validateProduct2(
        Mage_CatalogRule_Model_Rule $rule, 
        Varien_Object $product, 
        $websiteIds = array()
    )
    {
        $helper             = $this->getCurrencyPricingHelper();
        $productFlatHelper  = $this->_factory->getHelper('catalog/product_flat');
        if ($productFlatHelper->isEnabled() && $productFlatHelper->isBuiltAllStores()) {
            foreach ($this->_app->getStores(false) as $store) {
                $websiteId  = $store->getWebsiteId();
                $storeId    = $store->getId();
                if ((count($websiteIds) > 0) && !in_array($websiteId, $websiteIds)) {
                    continue;
                }
                $selectByStore = $rule->getProductFlatSelect($storeId);
                $selectByStore->where('p.entity_id = ?', $product->getId());
                $selectByStore->limit(1);
                if ($this->_getReadAdapter()->fetchOne($selectByStore)) {
                    return true;
                }
            }
            return false;
        } else {
            return $rule->getConditions()->validate($product);
        }
    }
    /**
     * Inserts rule data into catalogrule/rule_product table
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param array $websiteIds
     * @param array $currencies
     * @param array $productIds
     */
    public function insertRuleData2(
        Mage_CatalogRule_Model_Rule $rule, 
        array $websiteIds, 
        array $currencies, 
        array $productIds = array()
    )
    {
        $helper             = $this->getCurrencyPricingHelper();
        $catalogRuleHelper  = $helper->getCatalogRuleHelper();
        $ruleId             = $rule->getId();
        
        $write              = $this->_getWriteAdapter();
        $customerGroupIds   = $rule->getCustomerGroupIds();
        $fromTime           = (int) strtotime($rule->getFromDate());
        $toTime             = (int) strtotime($rule->getToDate());
        $toTime             = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;
        $sortOrder          = (int) $rule->getSortOrder();
        $actionOperator     = $rule->getSimpleAction();
        $subActionOperator  = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
        $actionStop         = (int) $rule->getStopRulesProcessing();
        $productFlatHelper  = $this->_factory->getHelper('catalog/product_flat');
        if ($productFlatHelper->isEnabled() && $productFlatHelper->isBuiltAllStores()) {
            foreach ($this->_app->getStores(false) as $store) {
                $websiteId = $store->getWebsiteId();
                $storeId = $store->getId();
                if (!in_array($websiteId, $websiteIds)) {
                    continue;
                }
                foreach ($currencies as $currency) {
                    $discountAmount         = $catalogRuleHelper->getCompoundDiscountAmount($rule, $websiteId, $currency);
                    $subDiscountAmount      = $catalogRuleHelper->getCompoundSubDiscountAmount($rule, $websiteId, $currency);
                    $selectByStore          = $rule->getProductFlatSelect($storeId)
                        ->joinLeft(array('cg' => $this->getTable('customer/customer_group')),
                            $write->quoteInto('cg.customer_group_id IN (?)', $customerGroupIds),
                            array('cg.customer_group_id'))
                        ->reset(Varien_Db_Select::COLUMNS)
                        ->columns(array(
                            new Zend_Db_Expr($websiteId), 
                            new Zend_Db_Expr("'".$currency."'"), 
                            'cg.customer_group_id', 
                            'p.entity_id', 
                            new Zend_Db_Expr($ruleId), 
                            new Zend_Db_Expr($fromTime), 
                            new Zend_Db_Expr($toTime), 
                            new Zend_Db_Expr("'".$actionOperator."'"), 
                            new Zend_Db_Expr($discountAmount), 
                            new Zend_Db_Expr($actionStop), 
                            new Zend_Db_Expr($sortOrder), 
                            new Zend_Db_Expr("'".$subActionOperator."'"), 
                            new Zend_Db_Expr($subDiscountAmount), 
                        ));
                    if (count($productIds) > 0) {
                        $selectByStore->where('p.entity_id IN (?)', $productIds);
                    }
                    $selects = $write->selectsByRange('entity_id', $selectByStore, self::RANGE_PRODUCT_STEP);
                    foreach ($selects as $select) {
                        $write->query(
                            $write->insertFromSelect(
                                $select, $this->getTable('catalogrule/rule_product'), array(
                                    'website_id', 
                                    'currency', 
                                    'customer_group_id', 
                                    'product_id', 
                                    'rule_id', 
                                    'from_time', 
                                    'to_time', 
                                    'action_operator', 
                                    'action_amount', 
                                    'action_stop', 
                                    'sort_order', 
                                    'sub_simple_action', 
                                    'sub_discount_amount', 
                                ), Varien_Db_Adapter_Interface::INSERT_IGNORE
                            )
                        );
                    }
                }
            }
        } else {
            if (count($productIds) == 0) {
                Varien_Profiler::start('__MATCH_PRODUCTS__');
                $productIds = $rule->getMatchingProductIds();
                Varien_Profiler::stop('__MATCH_PRODUCTS__');
            }
            $rows = array();
            foreach ($productIds as $productId => $validationByWebsite) {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        if (empty($validationByWebsite[$websiteId])) {
                            continue;
                        }
                        foreach ($currencies as $currency) {
                            $discountAmount         = $catalogRuleHelper->getCompoundDiscountAmount($rule, $websiteId, $currency);
                            $subDiscountAmount      = $catalogRuleHelper->getCompoundSubDiscountAmount($rule, $websiteId, $currency);
                            $rows[] = array(
                                'rule_id'               => $ruleId, 
                                'from_time'             => $fromTime, 
                                'to_time'               => $toTime, 
                                'website_id'            => $websiteId, 
                                'currency'              => $currency, 
                                'customer_group_id'     => $customerGroupId, 
                                'product_id'            => $productId, 
                                'action_operator'       => $actionOperator, 
                                'action_amount'         => $discountAmount, 
                                'action_stop'           => $actionStop, 
                                'sort_order'            => $sortOrder, 
                                'sub_simple_action'     => $subActionOperator, 
                                'sub_discount_amount'   => $subDiscountAmount, 
                            );
                            if (count($rows) == 1000) {
                                $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                                $rows = array();
                            }
                        }
                    }
                }
            }
            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
            }
        }
    }
    /**
     * Update products which are matched for rule
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * 
     * @return self
     */
    public function updateRuleProductData(Mage_CatalogRule_Model_Rule $rule)
    {
        $helper             = $this->getCurrencyPricingHelper();
        $catalogRuleHelper  = $helper->getCatalogRuleHelper();
        $ruleId             = $rule->getId();
        $websiteIds         = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds         = explode(',', $websiteIds);
        }
        if (empty($websiteIds)) {
            return $this;
        }
        $currencies         = $rule->getCurrencies();
        if (!is_array($currencies)) {
            $currencies         = explode(',', $currencies);
        }
        if (empty($currencies)) {
            return $this;
        }
        $write              = $this->_getWriteAdapter();
        $write->beginTransaction();
        
        if (!$this->getVersionHelper()->isGe1800()) {
            if ($this->getVersionHelper()->isGe1600() && $rule->getProductsFilter()) {
                $write->delete(
                    $this->getTable('catalogrule/rule_product'), 
                    array('rule_id=?' => $ruleId, 'product_id IN (?)' => $rule->getProductsFilter())
                );
            } else {
                $write->delete($this->getTable('catalogrule/rule_product'), $write->quoteInto('rule_id=?', $ruleId));
            }
            if (!$rule->getIsActive()) {
                $write->commit();
                return $this;
            }
            $productIds             = $rule->getMatchingProductIds();
            $customerGroupIds       = $rule->getCustomerGroupIds();
            $fromTime               = strtotime($rule->getFromDate());
            $toTime                 = strtotime($rule->getToDate());
            $toTime                 = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;
            $sortOrder              = (int)$rule->getSortOrder();
            $actionOperator         = $rule->getSimpleAction();
            if ($this->getVersionHelper()->isGe1700()) {
                $subActionOperator      = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
            }
            $actionStop             = $rule->getStopRulesProcessing();
            $rows                   = array();
            
            if (!$this->getVersionHelper()->isGe1600()) {
                $queryStart = 'INSERT INTO '.$this->getTable('catalogrule/rule_product').' (
                    rule_id, from_time, to_time, website_id, currency, customer_group_id, product_id, action_operator,
                    action_amount, action_stop, sort_order ) values ';
                $queryEnd = ' ON DUPLICATE KEY UPDATE action_operator=VALUES(action_operator),
                    action_amount=VALUES(action_amount), action_stop=VALUES(action_stop)';
            }

            try {
                foreach ($productIds as $productId) {
                    foreach ($websiteIds as $websiteId) {
                        foreach ($customerGroupIds as $customerGroupId) {
                            foreach ($currencies as $currency) {
                                $discountAmount         = $catalogRuleHelper->getCompoundDiscountAmount($rule, $websiteId, $currency);
                                if ($this->getVersionHelper()->isGe1600()) {
                                    $row = array(
                                        'rule_id'               => $ruleId, 
                                        'from_time'             => $fromTime, 
                                        'to_time'               => $toTime, 
                                        'website_id'            => $websiteId, 
                                        'currency'              => $currency, 
                                        'customer_group_id'     => $customerGroupId, 
                                        'product_id'            => $productId, 
                                        'action_operator'       => $actionOperator, 
                                        'action_amount'         => $discountAmount, 
                                        'action_stop'           => $actionStop, 
                                        'sort_order'            => $sortOrder, 
                                    );
                                    if ($this->getVersionHelper()->isGe1700()) {
                                        $subDiscountAmount      = $catalogRuleHelper->getCompoundSubDiscountAmount($rule, $websiteId, $currency);
                                        $row['sub_simple_action'] = $subActionOperator;
                                        $row['sub_discount_amount'] = $subDiscountAmount;
                                    }
                                    $rows[] = $row;
                                    if (count($rows) == 1000) {
                                        $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                                        $rows = array();
                                    }
                                } else {
                                    $rows[] = "('" . implode("','", array(
                                        $ruleId,
                                        $fromTime,
                                        $toTime,
                                        $websiteId, 
                                        $currency, 
                                        $customerGroupId,
                                        $productId,
                                        $actionOperator,
                                        $discountAmount,
                                        $actionStop,
                                        $sortOrder))."')";
                                    if (sizeof($rows)==1000) {
                                        $sql = $queryStart.join(',', $rows).$queryEnd;
                                        $write->query($sql);
                                        $rows = array();
                                    }
                                }
                            }
                        }
                    }
                }
                if ($this->getVersionHelper()->isGe1600()) {
                    if (!empty($rows)) {
                        $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                    }
                } else {
                    if (!empty($rows)) {
                        $sql = $queryStart.join(',', $rows).$queryEnd;
                        $write->query($sql);
                    }
                }
                $write->commit();
            } catch (Exception $e) {
                $write->rollback();
                throw $e;
            }
        } else {
            if ($rule->getProductsFilter()) {
                $this->cleanProductData($ruleId, $rule->getProductsFilter());
            } else {
                $this->cleanProductData($ruleId);
            }
            if (!$rule->getIsActive()) {
                $write->commit();
                return $this;
            }
            try {
                $this->insertRuleData2($rule, $websiteIds, $currencies);
                $write->commit();
            } catch (Exception $e) {
                $write->rollback();
                throw $e;
            }
        }
        return $this;
    }
    /**
     * Get price join condition
     * 
     * @return string
     */
    protected function getPriceJoinCondition()
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        return implode(' AND ', array(
            '(%1$s.entity_id = rp.product_id)', 
            '(%1$s.attribute_id = '.$productPriceHelper->getPriceAttributeId().')', 
            '(%1$s.store_id = %2$s)'
        ));
    }
    /**
     * Add website price join
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addWebsitePriceJoin($select)
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $tableAlias             = 'pp_website';
        $fieldAlias             = 'website_price';
        $storeId                = new Zend_Db_Expr('csg.default_store_id');
        $websiteId              = new Zend_Db_Expr('cw.website_id');
        $select->joinLeft(
            array($tableAlias => $productPriceHelper->getPriceAttributeTable()), 
            sprintf($this->getPriceJoinCondition(), $tableAlias, $storeId), 
            array()
        );
        $globalCompoundTableAlias = 'cpgcp';
        $compoundTableAlias     = 'cpwcp';
        $currency               = new Zend_Db_Expr('cr.currency_to');
        $select->joinLeft(
            array($compoundTableAlias => $this->getTable('catalog/product_compound_price')), 
            implode(' AND ', array(
                "({$compoundTableAlias}.product_id = rp.product_id)", 
                "({$compoundTableAlias}.currency = {$currency})", 
                "({$compoundTableAlias}.website_id = {$websiteId})", 
            )), 
            array()
        );
        $price                  = new Zend_Db_Expr($tableAlias.'.value');
        $rate                   = new Zend_Db_Expr('cr.rate');
        $price                  = new Zend_Db_Expr("IF (
            {$compoundTableAlias}.price IS NOT NULL, 
            ROUND({$compoundTableAlias}.price / {$rate}, 4), 
            IF (
                {$globalCompoundTableAlias}.price IS NOT NULL, 
                ROUND({$globalCompoundTableAlias}.price / {$rate}, 4), 
                {$price}
            )
        )");
        $select->columns(array(
            $fieldAlias => $price, 
        ));
        return $this;
    }
    /**
     * Get DB resource statement for processing query result
     *
     * @param int $fromDate
     * @param int $toDate
     * @param int|null $productId
     * @param int $websiteId
     *
     * @return Zend_Db_Statement_Interface
     */
    protected function _getRuleProductsStmt2($fromDate, $toDate, $productId = null, $websiteId)
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $coreHelper             = $helper->getCoreHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $currencyHelper         = $coreHelper->getCurrencyHelper();
        $read       = $this->_getReadAdapter();
        $order      = array(
            'rp.website_id', 'rp.customer_group_id', 'rp.currency', 
            'rp.product_id', 'rp.sort_order', 'rp.rule_id', 
        );
        $select     = $read->select()->from(array('rp' => $this->getTable('catalogrule/rule_product')))
            ->where($read->quoteInto('rp.from_time = 0 or rp.from_time <= ?', $toDate).' OR '.
            $read->quoteInto('rp.to_time = 0 or rp.to_time >= ?', $fromDate))
            ->order($order);
        if (!is_null($productId)) {
            $select->where('rp.product_id=?', $productId);
        }
        $select->joinInner(
            array('product_website' => $this->getTable('catalog/product_website')), 
            'product_website.product_id=rp.product_id ' .
            'AND rp.website_id=product_website.website_id ' .
            'AND product_website.website_id='.$websiteId, 
            array()
        );
        if ($productPriceHelper->isWebsiteScope()) {
            $select->join(
                array('cw' => $this->getTable('core/website')), 
                '(cw.website_id = rp.website_id)', 
                array()
            );
            $select->join(
                array('csg' => $this->getTable('core/store_group')), 
                '(csg.group_id = cw.default_group_id)', 
                array()
            );
        }
        $select->joinLeft(
            array('cr' => $this->getTable('directory/currency_rate')), 
            implode(' AND ', array(
                "(cr.currency_from = {$currencyHelper->getBaseDbExpr('rp.website_id')})", 
                "((rp.currency IS NOT NULL AND (cr.currency_to = rp.currency)) OR ".
                "(rp.currency IS NULL AND (cr.currency_to = cr.currency_from)))"
            )), 
            array()
        );
        $select->join(
            array('pp_default' => $productPriceHelper->getPriceAttributeTable()), 
            sprintf($this->getPriceJoinCondition(), 'pp_default', Mage_Core_Model_App::ADMIN_STORE_ID), 
            array()
        );
        $globalCompoundTableAlias   = 'cpgcp';
        $currency                   = new Zend_Db_Expr('cr.currency_to');
        $select->joinLeft(
            array($globalCompoundTableAlias => $this->getTable('catalog/product_compound_price')), 
            implode(' AND ', array(
                "({$globalCompoundTableAlias}.product_id = rp.product_id)", 
                "({$globalCompoundTableAlias}.currency = {$currency})", 
                "({$globalCompoundTableAlias}.website_id = 0)", 
            )), 
            array()
        );
        $defaultPrice   = new Zend_Db_Expr('pp_default.value');
        $rate           = new Zend_Db_Expr('cr.rate');
        $defaultPrice   = new Zend_Db_Expr("IF (
            cpgcp.price IS NOT NULL, 
            ROUND(cpgcp.price / {$rate}, 4), 
            {$defaultPrice}
        )");
        $select->columns(array(
            'default_price' => $defaultPrice, 
        ));
        
        if ($productPriceHelper->isWebsiteScope()) {
            $this->addWebsitePriceJoin($select);
        }
        return $read->query($select);
    }
    /**
     * Generate catalog price rules prices for specified date range
     * If from date is not defined - will be used previous day by UTC
     * If to date is not defined - will be used next day by UTC
     *
     * @param int|string|null $fromDate
     * @param int|string|null $toDate
     * @param int $productId
     *
     * @return self
     */
    public function applyAllRulesForDateRange($fromDate = null, $toDate = null, $productId = null)
    {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        Mage::dispatchEvent('catalogrule_before_apply', array('resource' => $this));
        $clearOldData = false;
        if ($fromDate === null) {
            $fromDate = mktime(0,0,0,date('m'),date('d')-1);
            $clearOldData = true;
        }
        if (is_string($fromDate)) {
            $fromDate = strtotime($fromDate);
        }
        if ($toDate === null) {
            $toDate = mktime(0,0,0,date('m'),date('d')+1);
        }
        if (is_string($toDate)) {
            $toDate = strtotime($toDate);
        }
        $product = null;
        if ($productId instanceof Mage_Catalog_Model_Product) {
            $product    = $productId;
            $productId  = $productId->getId();
        }
        $this->removeCatalogPricesForDateRange($fromDate, $toDate, $productId);
        if ($clearOldData) {
            $this->deleteOldData($fromDate, $productId);
        }
        $dayPrices  = array();
        try {
            foreach (Mage::app()->getWebsites(false) as $website) {
                $websiteId = $website->getId();
                $productsStmt = $this->_getRuleProductsStmt2($fromDate, $toDate, $productId, $websiteId);
                $dayPrices  = array();
                $stopFlags  = array();
                $prevKey    = null;
                while ($ruleData = $productsStmt->fetch()) {
                    $ruleProductId  = $ruleData['product_id'];
                    $productKey     = implode('_', array(
                        $ruleProductId, 
                        $ruleData['website_id'], 
                        $ruleData['customer_group_id'], 
                        $ruleData['currency'], 
                    ));
                    if ($prevKey && ($prevKey != $productKey)) {
                        $stopFlags = array();
                    }
                    for ($time = $fromDate; $time <= $toDate; $time += self::SECONDS_IN_DAY) {
                        if (($ruleData['from_time']==0 || $time >= $ruleData['from_time'])
                            && ($ruleData['to_time']==0 || $time <=$ruleData['to_time'])
                        ) {
                            $priceKey = $time.'_'.$productKey;
                            if (isset($stopFlags[$priceKey])) {
                                continue;
                            }
                            if (!isset($dayPrices[$priceKey])) {
                                $dayPrices[$priceKey] = array(
                                    'rule_date'         => $time, 
                                    'website_id'        => $ruleData['website_id'], 
                                    'customer_group_id' => $ruleData['customer_group_id'],
                                    'currency'          => $ruleData['currency'],
                                    'product_id'        => $ruleProductId,
                                    'rule_price'        => $this->_calcRuleProductPrice($ruleData),
                                    'latest_start_date' => $ruleData['from_time'],
                                    'earliest_end_date' => $ruleData['to_time'],
                                );
                            } else {
                                $dayPrices[$priceKey]['rule_price'] = $this->_calcRuleProductPrice(
                                    $ruleData,
                                    $dayPrices[$priceKey]
                                );
                                $dayPrices[$priceKey]['latest_start_date'] = max(
                                    $dayPrices[$priceKey]['latest_start_date'],
                                    $ruleData['from_time']
                                );
                                $dayPrices[$priceKey]['earliest_end_date'] = min(
                                    $dayPrices[$priceKey]['earliest_end_date'],
                                    $ruleData['to_time']
                                );
                            }
                            if ($ruleData['action_stop']) {
                                $stopFlags[$priceKey] = true;
                            }
                        }
                    }
                    $prevKey = $productKey;
                    if (count($dayPrices)>1000) {
                        $this->_saveRuleProductPrices($dayPrices);
                        $dayPrices = array();
                    }
                }
                $this->_saveRuleProductPrices($dayPrices);
            }
            
            $this->_saveRuleProductPrices($dayPrices);
            $write->delete($this->getTable('catalogrule/rule_group_website'), array());
            $timestamp = Mage::getModel('core/date')->gmtTimestamp();
            
            $attributes = array('rule_id', 'customer_group_id', 'website_id');
            $select = $write->select()->distinct(true)
                ->from($this->getTable('catalogrule/rule_product'), $attributes)
                ->where("{$timestamp} >= from_time AND (({$timestamp} <= to_time AND to_time > 0) OR to_time = 0)");
            $query = $select->insertFromSelect($this->getTable('catalogrule/rule_group_website'));
            $write->query($query);
            
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }
        $productCondition = Mage::getModel('catalog/product_condition')
            ->setTable($this->getTable('catalogrule/affected_product'))
            ->setPkFieldName('product_id');
        Mage::dispatchEvent('catalogrule_after_apply', array(
            'product' => $product,
            'product_condition' => $productCondition
        ));
        $write->delete($this->getTable('catalogrule/affected_product'));
        return $this;
    }
    /**
     * Calculate product price based on price rule data and previous information
     *
     * @param array $ruleData
     * @param null|array $productData
     *
     * @return float
     */
    protected function _calcRuleProductPrice($ruleData, $productData = null)
    {
        if ($productData !== null && isset($productData['rule_price'])) {
            $productPrice = $productData['rule_price'];
        } else {
            if (isset($ruleData['website_price'])) {
                $productPrice = $ruleData['website_price'];
            } else {
                $productPrice = $ruleData['default_price'];
            }
        }
        $productPrice = Mage::helper('catalogrule')->calcPriceRule(
            $ruleData['action_operator'], $ruleData['action_amount'], $productPrice
        );
        return $this->getCurrencyPricingHelper()
            ->getCatalogRuleHelper()
            ->roundPrice($productPrice);
    }
    /**
     * Save rule prices for products to DB
     *
     * @param array $arrData
     * 
     * @return self
     */
    protected function _saveRuleProductPrices($arrData)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            return parent::_saveRuleProductPrices($arrData);
        } else {
            if (empty($arrData)) {
                return $this;
            }
            $header = 'replace into '.$this->getTable('catalogrule/rule_product_price').' (
                    rule_date, website_id, customer_group_id, currency, product_id, rule_price, latest_start_date, earliest_end_date
                ) values ';
            $rows = array();
            $productIds = array();
            foreach ($arrData as $data) {
                $productIds[$data['product_id']] = true;
                $data['rule_date']          = $this->formatDate($data['rule_date'], false);
                $data['latest_start_date']  = $this->formatDate($data['latest_start_date'], false);
                $data['earliest_end_date']  = $this->formatDate($data['earliest_end_date'], false);
                $rows[] = '(' . $this->_getWriteAdapter()->quote($data) . ')';
            }
            $query = $header.join(',', $rows);
            $insertQuery = 'REPLACE INTO ' . $this->getTable('catalogrule/affected_product') . ' (product_id)  VALUES ' .
                '(' . join('),(', array_keys($productIds)) . ')';
            $this->_getWriteAdapter()->query($insertQuery);
            $this->_getWriteAdapter()->query($query);
            return $this;
        }
    }
    /**
     * Get catalog rules product price for specific date, website, store and customer group
     *
     * @param int|string $date
     * @param int $wId
     * @param int $sId
     * @param int $gId
     * @param string $curr
     * @param int $pId
     *
     * @return float|bool
     */
    public function getRulePrice2($date, $wId, $gId, $curr, $pId)
    {
        $data = $this->getRulePrices2($date, $wId, $gId, $curr, array($pId));
        if (isset($data[$pId])) {
            return $data[$pId];
        }
        return false;
    }
    /**
     * Retrieve product prices by catalog rule for specific date, website, store and customer group
     * Collect data with  product Id => price pairs
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $currency
     * @param array $productIds
     *
     * @return array
     */
    public function getRulePrices2($date, $websiteId, $customerGroupId, $currency, $productIds)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('catalogrule/rule_product_price'), array('product_id', 'rule_price'))
            ->where('rule_date = ?', $this->formatDate($date, false))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('currency = ?', $currency)
            ->where('product_id IN(?)', $productIds);
        return $adapter->fetchPairs($select);
    }
    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $currency
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct2($date, $websiteId, $customerGroupId, $currency, $productId)
    {
        $adapter = $this->_getReadAdapter();
        if ($this->getVersionHelper()->isGe1700()) {
            if (is_string($date)) {
               $date = strtotime($date);
            }
            $select = $adapter->select()
                ->from($this->getTable('catalogrule/rule_product'))
                ->where('website_id = ?', $websiteId)
                ->where('customer_group_id = ?', $customerGroupId)
                ->where('currency = ?', $currency)
                ->where('product_id = ?', $productId)
                ->where('from_time = 0 or from_time < ?', $date)
                ->where('to_time = 0 or to_time > ?', $date);
        } else {
            $dateQuoted = $adapter->quote($this->formatDate($date, false));
            $joinCondsQuoted[] = 'main_table.rule_id = rp.rule_id';
            $joinCondsQuoted[] = $adapter->quoteInto('rp.website_id = ?', $websiteId);
            $joinCondsQuoted[] = $adapter->quoteInto('rp.customer_group_id = ?', $customerGroupId);
            $joinCondsQuoted[] = $adapter->quoteInto('rp.product_id = ?', $productId);
            if ($this->getVersionHelper()->isGe1600()) {
                $fromDate = $adapter->getIfNullSql('main_table.from_date', $dateQuoted);
                $toDate = $adapter->getIfNullSql('main_table.to_date', $dateQuoted);
                $select = $adapter->select()
                    ->from(array('main_table' => $this->getTable('catalogrule/rule')))
                    ->joinInner(
                        array('rp' => $this->getTable('catalogrule/rule_product')),
                        implode(' AND ', $joinCondsQuoted),
                        array())
                    ->where(new Zend_Db_Expr("{$dateQuoted} BETWEEN {$fromDate} AND {$toDate}"))
                    ->where('main_table.is_active = ?', 1)
                    ->order('main_table.sort_order');
            } else {
                $select = $adapter->select()
                    ->distinct()
                    ->from(array('main_table' => $this->getTable('catalogrule/rule')), 'main_table.*')
                    ->joinInner(
                        array('rp' => $this->getTable('catalogrule/rule_product')),
                        implode(' AND ', $joinCondsQuoted),
                        array())
                    ->where(new Zend_Db_Expr("{$dateQuoted} BETWEEN IFNULL(main_table.from_date, {$dateQuoted}) AND IFNULL(main_table.to_date, {$dateQuoted})"))
                    ->where('main_table.is_active = ?', 1)
                    ->order('main_table.sort_order');
            }
        }
        return $adapter->fetchAll($select);
    }
    /**
     * Retrieve product price data for all customer groups
     *
     * @param int|string $date
     * @param int $wId
     * @param int $pId
     *
     * @return array
     */
    public function getRulesForProduct2($date, $wId, $pId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('catalogrule/rule_product_price'), '*')
            ->where('rule_date=?', $this->formatDate($date, false))
            ->where('website_id=?', $wId)
            ->where('product_id=?', $pId);
        return $read->fetchAll($select);
    }
    
    /**
     * Apply catalog rule to product
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param Mage_Catalog_Model_Product $product
     * @param array $websiteIds
     * 
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function applyToProduct2($rule, $product, $websiteIds)
    {
        if (!$rule->getIsActive()) {
            return $this;
        }
        $helper             = $this->getCurrencyPricingHelper();
        $catalogRuleHelper  = $helper->getCatalogRuleHelper();
        $ruleId             = $rule->getId();
        $productId          = $product->getId();
        $write              = $this->_getWriteAdapter();
        $write->beginTransaction();
        
        if (!$this->getVersionHelper()->isGe1800()) {
            $write->delete($this->getTable('catalogrule/rule_product'), array(
                $write->quoteInto('rule_id=?', $ruleId),
                $write->quoteInto('product_id=?', $productId),
            ));
            if (!$rule->getConditions()->validate($product)) {
                $write->delete($this->getTable('catalogrule/rule_product_price'), array(
                    $write->quoteInto('product_id=?', $productId),
                ));
                $write->commit();
                return $this;
            }
            $customerGroupIds   = $rule->getCustomerGroupIds();
            $currencies         = $rule->getCurrencies();
            $fromTime           = strtotime($rule->getFromDate());
            $toTime             = strtotime($rule->getToDate());
            $toTime             = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
            $sortOrder          = (int)$rule->getSortOrder();
            $actionOperator     = $rule->getSimpleAction();
            $actionStop         = $rule->getStopRulesProcessing();
            if ($this->getVersionHelper()->isGe1700()) {
                $subActionOperator  = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
            }
            $rows = array();
            if (!$this->getVersionHelper()->isGe1600()) {
                $header = 'replace into '.$this->getTable('catalogrule/rule_product').' (
                    rule_id,
                    from_time,
                    to_time,
                    website_id,
                    customer_group_id,
                    currency,
                    product_id,
                    action_operator,
                    action_amount,
                    action_stop,
                    sort_order
                ) values ';
            }
            try {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        foreach ($currencies as $currency) {
                            $discountAmount         = $catalogRuleHelper->getCompoundDiscountAmount($rule, $websiteId, $currency);
                            if ($this->getVersionHelper()->isGe1600()) {
                                $row = array(
                                    'rule_id'               => $ruleId,
                                    'from_time'             => $fromTime,
                                    'to_time'               => $toTime,
                                    'website_id'            => $websiteId,
                                    'customer_group_id'     => $customerGroupId,
                                    'currency'              => $currency,
                                    'product_id'            => $productId,
                                    'action_operator'       => $actionOperator,
                                    'action_amount'         => $discountAmount,
                                    'action_stop'           => $actionStop,
                                    'sort_order'            => $sortOrder, 
                                );
                                if ($this->getVersionHelper()->isGe1700()) {
                                    $subDiscountAmount      = $catalogRuleHelper->getCompoundSubDiscountAmount($rule, $websiteId, $currency);
                                    $row['sub_simple_action'] = $subActionOperator;
                                    $row['sub_discount_amount'] = $subDiscountAmount;
                                }
                                $rows[] = $row;
                                if (count($rows) == 1000) {
                                    $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                                    $rows = array();
                                }
                            } else {
                                $rows[] = "(
                                    '$ruleId',
                                    '$fromTime',
                                    '$toTime',
                                    '$websiteId',
                                    '$customerGroupId',
                                    '$currency',
                                    '$productId',
                                    '$actionOperator',
                                    '$discountAmount',
                                    '$actionStop',
                                    '$sortOrder'
                                )";
                                if (sizeof($rows)==100) {
                                    $sql = $header.join(',', $rows);
                                    $write->query($sql);
                                    $rows = array();
                                }
                            }
                        }
                    }
                }
                if ($this->getVersionHelper()->isGe1600()) {
                    if (!empty($rows)) {
                        $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                    }
                } else {
                    if (!empty($rows)) {
                        $sql = $header.join(',', $rows);
                        $write->query($sql);
                    }
                }
            } catch (Exception $e) {
                $write->rollback();
                throw $e;
            }
            $this->applyAllRulesForDateRange(null, null, $product);
        } else {
            $this->cleanProductData($ruleId, array($productId));
            if (!$this->validateProduct2($rule, $product, $websiteIds)) {
                $write->delete($this->getTable('catalogrule/rule_product_price'), array(
                    $write->quoteInto('product_id = ?', $productId),
                ));
                $write->commit();
                return $this;
            }
            try {
                $currencies         = $rule->getCurrencies();
                $this->insertRuleData2(
                    $rule, 
                    $websiteIds, 
                    $currencies, 
                    array(
                        $productId => array_combine(
                            array_values($websiteIds), 
                            array_values($websiteIds)
                        )
                    )
                );
            } catch (Exception $e) {
                $write->rollback();
                throw $e;
            }
        }
        
        $write->commit();
        
        return $this;
    }
}