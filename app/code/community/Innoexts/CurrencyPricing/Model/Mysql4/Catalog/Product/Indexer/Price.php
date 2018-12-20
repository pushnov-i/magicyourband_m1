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
 * Price indexer resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Catalog_Product_Indexer_Price 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price 
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
    protected function getProductPriceHelper()
    {
        return $this->getCurrencyPricingHelper()
            ->getProductPriceHelper();
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
     * Get version helper
     * 
     * @return Innoexts_Core_Helper_Version
     */
    protected function getVersionHelper()
    {
        return $this->getCurrencyPricingHelper()->getVersionHelper();
    }
    /**
     * Product save
     * 
     * @param Mage_Index_Model_Event $event
     * 
     * @return self
     */
    public function catalogProductSave(Mage_Index_Model_Event $event)
    {
        $productId = $event->getEntityPk();
        $data = $event->getNewData();
        if (!isset($data['reindex_price'])) {
            return $this;
        }
        $this->clearTemporaryIndexTable();
        $this->_prepareWebsiteDateTable();
        $indexer = $this->_getIndexer($data['product_type_id']);
        $processIds = array($productId);
        if ($indexer->getIsComposite()) {
            $this->_copyRelationIndexData($productId);
            
            $this->_prepareCompoundPriceIndex($productId);
            $this->_prepareCompoundSpecialPriceIndex($productId);
            
            $this->_prepareTierPriceIndex($productId);
            
            if ($this->getVersionHelper()->isGe1700()) {
                $this->_prepareGroupPriceIndex($productId);
            }
            
            $indexer->reindexEntity($productId);
        } else {
            $parentIds = $this->getProductParentsByChild($productId);
            if ($parentIds) {
                $processIds = array_merge($processIds, array_keys($parentIds));
                $this->_copyRelationIndexData(array_keys($parentIds), $productId);
                
                $this->_prepareCompoundPriceIndex($processIds);
                $this->_prepareCompoundSpecialPriceIndex($processIds);
                
                $this->_prepareTierPriceIndex($processIds);
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $this->_prepareGroupPriceIndex($processIds);
                }
                
                $indexer->reindexEntity($productId);
                $parentByType = array();
                foreach ($parentIds as $parentId => $parentType) {
                    $parentByType[$parentType][$parentId] = $parentId;
                }
                foreach ($parentByType as $parentType => $entityIds) {
                    $this->_getIndexer($parentType)->reindexEntity($entityIds);
                }
            } else {
                
                $this->_prepareCompoundPriceIndex($productId);
                $this->_prepareCompoundSpecialPriceIndex($productId);
                
                $this->_prepareTierPriceIndex($productId);
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $this->_prepareGroupPriceIndex($productId);
                }
                
                $indexer->reindexEntity($productId);
            }
        }
        $this->_copyIndexDataToMainTable($processIds);
        return $this;
    }
    /**
     * Rebuild all index data
     * 
     * @return self
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $this->beginTransaction();
            try {
                $this->clearTemporaryIndexTable();
                $this->_prepareWebsiteDateTable();

                $this->_prepareCompoundPriceIndex();
                $this->_prepareCompoundSpecialPriceIndex();

                $this->_prepareTierPriceIndex();

                if ($this->getVersionHelper()->isGe1700()) {
                    $this->_prepareGroupPriceIndex();
                }

                $indexers = $this->getTypeIndexers();
                foreach ($indexers as $indexer) {
                    $indexer->reindexAll();
                }
                $this->syncData();
                $this->commit();
            } catch (Exception $e) {
                $this->rollBack();
                throw $e;
            }
        } else {
            $this->useIdxTable(true);
            $this->clearTemporaryIndexTable();
            $this->_prepareWebsiteDateTable();
            
            $this->_prepareCompoundPriceIndex();
            $this->_prepareCompoundSpecialPriceIndex();
            
            $this->_prepareTierPriceIndex();
            $indexers = $this->getTypeIndexers();
            foreach ($indexers as $indexer) {
                
                if ($this->getVersionHelper()->isGe1610()) {
                    if (!$this->_allowTableChanges && is_callable(array($indexer, 'setAllowTableChanges'))) {
                        $indexer->setAllowTableChanges(false);
                    }
                }
                
                $indexer->reindexAll();
                
                if ($this->getVersionHelper()->isGe1610()) {
                    if (!$this->_allowTableChanges && is_callable(array($indexer, 'setAllowTableChanges'))) {
                        $indexer->setAllowTableChanges(true);
                    }
                }
            }
            $this->syncData();
        }
        return $this;
    }
    /**
     * Retrieve catalog_product attribute instance by attribute code
     *
     * @param string $attributeCode
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getAttribute($attributeCode)
    {
        return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
    }
    /**
     * Add attribute join condition to select and return Zend_Db_Expr
     * attribute value definition
     * If $condition is not empty apply limitation for select
     *
     * @param Varien_Db_Select $select
     * @param string $attrCode              the attribute code
     * @param string|Zend_Db_Expr $entity   the entity field or expression for condition
     * @param string|Zend_Db_Expr $store    the store field or expression for condition
     * @param Zend_Db_Expr $condition       the limitation condition
     * @param bool $required                if required or has condition used INNER join, else - LEFT
     * 
     * @return Zend_Db_Expr                 the attribute value expression
     */
    protected function _addAttributeToSelect($select, $attrCode, $entity, $store, $condition = null, $required = false)
    {
        $attribute      = $this->_getAttribute($attrCode);
        $attributeId    = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $adapter        = $this->_getReadAdapter();
        $joinType       = !is_null($condition) || $required ? 'join' : 'joinLeft';
        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attrCode;
            $select->$joinType(
                array($alias => $attributeTable),
                "{$alias}.entity_id = {$entity} AND {$alias}.attribute_id = {$attributeId}"
                    . " AND {$alias}.store_id = 0",
                array()
            );
            $expression = new Zend_Db_Expr("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;
            $select->$joinType(
                array($dAlias => $attributeTable),
                "{$dAlias}.entity_id = {$entity} AND {$dAlias}.attribute_id = {$attributeId}"
                    . " AND {$dAlias}.store_id = 0",
                array()
            );
            $select->joinLeft(
                array($sAlias => $attributeTable),
                "{$sAlias}.entity_id = {$entity} AND {$sAlias}.attribute_id = {$attributeId}"
                    . " AND {$sAlias}.store_id = {$store}",
                array()
            );
            if ($this->getVersionHelper()->isGe1600()) {
                $expression = $adapter->getCheckSql($adapter->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                    "{$sAlias}.value", "{$dAlias}.value");
            } else {
                $expression = new Zend_Db_Expr("IF({$sAlias}.value_id > 0, {$sAlias}.value, {$dAlias}.value)");
            }    
        }
        if (!is_null($condition)) {
            $select->where("{$expression}{$condition}");
        }
        return $expression;
    }
    /**
     * Prepare compound price index table
     * 
     * @param int|array $entityIds
     * @param string $attributeCode
     * @param string $table
     * @param string $indexTable
     * 
     * @return self
     */
    protected function __prepareCompoundPriceIndex($entityIds = null, $attributeCode, $table, $indexTable)
    {
        $priceHelper        = $this->getProductPriceHelper();
        $indexerHelper      = $this->getProductPriceIndexerHelper();
        $write              = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array())
            ->join(array('cw' => $this->getTable('core/website')), '', array())
            ->join(array('cwd' => $this->_getWebsiteDateTable()), 
                '(cw.website_id = cwd.website_id)', array())
            ->join(array('csg' => $this->getTable('core/store_group')), 
                'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id', array())
            ->join(array('cs' => $this->getTable('core/store')),
                'csg.default_store_id = cs.store_id AND cs.store_id != 0', array())
            ->join(array('pw' => $this->getTable('catalog/product_website')),
                '(pw.product_id = e.entity_id) AND (pw.website_id = cw.website_id)', array())
            ->joinLeft(array('cr' => $this->getTable('directory/currency_rate')), 
                "(cr.currency_from = {$indexerHelper->getBaseCurrencyExpr('cw.website_id')})", array());
        $price = $this->_addAttributeToSelect($select, $attributeCode, 'e.entity_id', 'cs.store_id');
        $select->joinLeft(array('ccgp' => $table), implode(' AND ', array(
            '(ccgp.product_id = e.entity_id)', 
            '(ccgp.currency = cr.currency_to)', 
            '(ccgp.website_id = 0)', 
        )), array());
        if (!$priceHelper->isGlobalScope()) {
            $select->joinLeft(array('ccp' => $table), implode(' AND ', array(
                '(ccp.product_id = e.entity_id)', 
                '(ccp.currency = cr.currency_to)', 
                '(csg.group_id = cw.default_group_id)', 
                '(ccp.website_id = cw.website_id)', 
            )), array());
        }
        $rate       = new Zend_Db_Expr('cr.rate');
        if (!$priceHelper->isGlobalScope()) {
            $price = new Zend_Db_Expr("IF (
                ccp.price IS NOT NULL, 
                ROUND(ccp.price / {$rate}, 8), 
                IF (
                    ccgp.price IS NOT NULL, 
                    ROUND(ROUND(ccgp.price * cwd.rate, 8) / {$rate}, 8), 
                    {$price}
                )
            )");
        } else {
            $price = new Zend_Db_Expr("IF (
                ccgp.price IS NOT NULL, 
                ROUND(ROUND(ccgp.price * cwd.rate, 8) / {$rate}, 8), 
                {$price}
            )");
        }
        $currency   = $indexerHelper->getCurrencyExpr('cw.website_id');
        $columns    = array(
            'entity_id'         => new Zend_Db_Expr('e.entity_id'), 
            'currency'          => $currency, 
            'website_id'        => new Zend_Db_Expr('cw.website_id'), 
            'price'             => new Zend_Db_Expr($price), 
        );
        $group              = array('e.entity_id', 'cr.currency_to', 'cs.website_id');
        $where                  = '(cw.website_id <> 0)';
        $select->where($where)
            ->columns($columns)
            ->group($group);
        if (!empty($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        $write->delete($indexTable);
        $query = $select->insertFromSelect($indexTable);
        
        $write->query($query);
        return $this;
    }
    /**
     * Prepare compound price index table
     * 
     * @param int|array $entityIds
     * 
     * @return self
     */
    protected function _prepareCompoundPriceIndex($entityIds = null)
    {
        return $this->__prepareCompoundPriceIndex(
            $entityIds, 
            'price', 
            $this->getTable('catalog/product_compound_price'), 
            $this->getProductPriceIndexerHelper()->getCompoundPriceIndexTable()
        );
    }
    /**
     * Prepare compound special price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * 
     * @return self
     */
    protected function _prepareCompoundSpecialPriceIndex($entityIds = null)
    {
        return $this->__prepareCompoundPriceIndex(
            $entityIds, 
            'special_price', 
            $this->getTable('catalog/product_compound_special_price'), 
            $this->getProductPriceIndexerHelper()->getCompoundSpecialPriceIndexTable()
        );
    }
    /**
     * Prepare tier price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * 
     * @return self
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $indexerHelper      = $this->getProductPriceIndexerHelper();
        $write              = $this->_getWriteAdapter();
        $table              = $this->_getTierPriceIndexTable();
        $write->delete($table);
        $currency           = $indexerHelper->getCurrencyExpr('cw.website_id');
        $rate               = new Zend_Db_Expr('cr.rate');
        $price              = new Zend_Db_Expr("IF (tp.website_id=0, ROUND(tp.value * cwd.rate, 8), tp.value)");
        $price              = new Zend_Db_Expr("IF (
            (tp.currency IS NOT NULL) AND (tp.currency <> ''), 
            ROUND({$price} / {$rate}, 8), 
            {$price}
        )");
        $columns = array(
            'entity_id'             => new Zend_Db_Expr('tp.entity_id'), 
            'customer_group_id'     => new Zend_Db_Expr('cg.customer_group_id'), 
            'website_id'            => new Zend_Db_Expr('cw.website_id'), 
            'currency'              => $currency, 
            'min_price'             => new Zend_Db_Expr("MIN({$price})"), 
        );
        $group = array('tp.entity_id', 'cg.customer_group_id', 'cw.website_id', $currency);
        $select = $write->select()
            ->from(array('tp' => $this->getValueTable('catalog/product', 'tier_price')), array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)', array())
            ->join(
                array('cw' => $this->getTable('core/website')),
                'tp.website_id = 0 OR tp.website_id = cw.website_id', array())
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()),
                'cw.website_id = cwd.website_id', array())
            ->joinLeft(array('cr' => $this->getTable('directory/currency_rate')), 
                implode(' AND ', array(
                    "(cr.currency_from = {$indexerHelper->getBaseCurrencyExpr('cw.website_id')})", 
                    "((tp.currency IS NULL) OR (tp.currency = cr.currency_to))"
                )), array())
            ->where('(cw.website_id != 0)')
            ->columns($columns)
            ->group($group);
        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }
        $query = $select->insertFromSelect($table);
        $write->query($query);
        return $this;
    }
    /**
     * Prepare group price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * 
     * @return self
     */
    protected function _prepareGroupPriceIndex($entityIds = null)
    {
        $indexerHelper      = $this->getProductPriceIndexerHelper();
        $write              = $this->_getWriteAdapter();
        $table              = $this->_getGroupPriceIndexTable();
        $write->delete($table);
        $currency           = $indexerHelper->getCurrencyExpr('cw.website_id');
        $rate               = new Zend_Db_Expr('cr.rate');
        $price              = new Zend_Db_Expr("IF (gp.website_id=0, ROUND(gp.value * cwd.rate, 8), gp.value)");
        $price              = new Zend_Db_Expr("IF (
            (gp.currency IS NOT NULL) AND (gp.currency <> ''), 
            ROUND({$price} / {$rate}, 8), 
            {$price}
        )");
        $columns = array(
            'entity_id'             => new Zend_Db_Expr('gp.entity_id'), 
            'customer_group_id'     => new Zend_Db_Expr('cg.customer_group_id'), 
            'website_id'            => new Zend_Db_Expr('cw.website_id'), 
            'currency'              => $currency, 
            'min_price'             => new Zend_Db_Expr("MIN({$price})"), 
        );
        $group = array('gp.entity_id', 'cg.customer_group_id', 'cw.website_id', $currency);
        $select = $write->select()
            ->from(array('gp' => $this->getValueTable('catalog/product', 'group_price')), array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                'gp.all_groups = 1 OR (gp.all_groups = 0 AND gp.customer_group_id = cg.customer_group_id)', array())
            ->join(
                array('cw' => $this->getTable('core/website')),
                'gp.website_id = 0 OR gp.website_id = cw.website_id', array())
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()),
                'cw.website_id = cwd.website_id', array())
            ->joinLeft(array('cr' => $this->getTable('directory/currency_rate')), 
                implode(' AND ', array(
                    "(cr.currency_from = {$indexerHelper->getBaseCurrencyExpr('cw.website_id')})", 
                    "((gp.currency IS NULL) OR (gp.currency = cr.currency_to))"
                )), array())
            ->where('(cw.website_id != 0)')
            ->columns($columns)
            ->group($group);
        if (!empty($entityIds)) {
            $select->where('gp.entity_id IN(?)', $entityIds);
        }
        $query = $select->insertFromSelect($table);
        $write->query($query);
        return $this;
    }
    
}