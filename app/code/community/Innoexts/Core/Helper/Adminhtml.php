<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_Core
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin html helper
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Helper_Adminhtml 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * Add column relation to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    protected function addColumnRelationToCollection($collection, $column)
    {
        if (!$column->getRelation()) {
            return $this;
        }
        $relation       = $column->getRelation();
        $fieldAlias     = $column->getId();
        $fieldName      = $relation['field_name'];
        $fkFieldName    = $relation['fk_field_name'];
        $refFieldName   = $relation['ref_field_name'];
        $tableAlias     = $relation['table_alias'];
        $table          = $collection->getTable($relation['table_name']);
        $collection->addFilterToMap($fieldAlias, $tableAlias.'.'.$fieldName);
        $collection->getSelect()->joinLeft(
            array($tableAlias => $table), 
            '(main_table.'.$fkFieldName.' = '.$tableAlias.'.'.$refFieldName.')', 
            array($fieldAlias => $tableAlias.'.'.$fieldName)
        );
        return $this;
    }
    /**
     * Add column relation to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    protected function addColumnRelationDataToCollection($collection, $column)
    {
        if (!$collection || !$column || !$column->getRelation()) {
            return $this;
        }
        $relation       = $column->getRelation();
        $fkFieldName    = $relation['fk_field_name'];
        $refFieldName   = $relation['ref_field_name'];
        $fieldName      = $relation['field_name'];
        $tableName      = $relation['table_name'];
        $table          = $collection->getTable($tableName);
        $modelValues = array();
        foreach ($collection as $model) {
            $modelValues[$model->getData($fkFieldName)] = array();
        }
        if (count($modelValues)) {
            $adapter    = $collection->getConnection();
            $select     = $adapter->select()
                ->from($table)
                ->where($adapter->quoteInto($fkFieldName.' IN (?)', array_keys($modelValues)));
            $items = $adapter->fetchAll($select);
            foreach ($items as $item) {
                $modelId    = $item[$refFieldName];
                $value      = $item[$fieldName];
                $modelValues[$modelId][] = $value;
            }
        }
        foreach ($collection as $model) {
            $modelId = $model->getData($fkFieldName);
            if (isset($modelValues[$modelId])) {
                $model->setData($column->getId(), $modelValues[$modelId]);
            }
        }
        return $this;
    }
    /**
     * Get column filter to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    public function addColumnFilterToCollection($collection, $column)
    {
        $this->addColumnRelationToCollection($collection, $column);
        $field          = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition      = $column->getFilter()->getCondition();
        if ($field && isset($condition)) {
            $collection->addFieldToFilter($field , $condition);
        }
        return $this;
    }
}