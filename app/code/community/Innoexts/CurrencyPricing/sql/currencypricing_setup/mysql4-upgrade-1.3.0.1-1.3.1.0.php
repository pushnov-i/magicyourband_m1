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

$installer                                  = $this;

$adapter                                    = $installer->getConnection();

$helper                                     = Mage::helper('currencypricing');
$coreHelper                                 = $helper->getCoreHelper();
$versionHelper                              = $coreHelper->getVersionHelper();
$databaseHelper                             = $coreHelper->getDatabaseHelper();

$productIndexPriceTable                     = $installer->getTable('catalog/product_index_price');
$productIndexCompoundPriceTable             = $installer->getTable('catalog/product_index_compound_price');
$productIndexCompoundSpecialPriceTable      = $installer->getTable('catalog/product_index_compound_special_price');
$productIndexTierPriceTable                 = $installer->getTable('catalog/product_index_tier_price');
$quoteTable                                 = $installer->getTable('sales/quote');

if ($versionHelper->isGe1700()) {
    $productIndexGroupPriceTable                = $installer->getTable('catalog/product_index_group_price');
}

$indexTables                                = array(
    $productIndexPriceTable, 
    $productIndexCompoundPriceTable, 
    $productIndexCompoundSpecialPriceTable, 
    $productIndexTierPriceTable, 
    $quoteTable, 
);

if ($versionHelper->isGe1700()) {
    array_push($indexTables, $productIndexGroupPriceTable);
}

$installer->startSetup();

$tables                                     = $adapter->listTables();

if (is_array($tables) && count($tables)) {
    foreach ($tables as $table) {
        $isIndexTable = false;
        foreach ($indexTables as $indexTable) {
            if (strpos($table, $indexTable) !== false) {
                $isIndexTable = true;
                break;
            }
        }
        if (!$isIndexTable) {
            continue;
        }
        $columns                                    = $adapter->describeTable($table);
        if (is_array($columns) && count($columns)) {
            foreach ($columns as $column) {
                if (
                    ($column['DATA_TYPE'] == 'decimal') && 
                    ($column['SCALE'] == 4)
                ) {
                    $column['SCALE']                        = 8;
                    $column['PRECISION']                    = 16;
                    $column                                 = $databaseHelper->getColumnByDdl($column);
                    $adapter->modifyColumn(
                        $column['TABLE_NAME'], 
                        $column['COLUMN_NAME'], 
                        $databaseHelper->getColumnDefinition($adapter, $column)
                    );
                }
            }
        }
    }
}

$installer->endSetup();