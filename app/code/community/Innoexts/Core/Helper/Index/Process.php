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
 * Process helper
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Helper_Index_Process 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * Get product price process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductPrice()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');
    }
    /**
     * Get product flat process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductFlat()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
    }
    /**
     * Get stock process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getStock()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('cataloginventory_stock');
    }
    /**
     * Get search process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getSearch()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
    }
    /**
     * Reindex product price
     * 
     * @return self
     */
    public function reindexProductPrice()
    {
        $process = $this->getProductPrice();
        if ($process) {
            $process->reindexAll();
        }
        return $this;
    }
    /**
     * Reindex product flat
     * 
     * @return self
     */
    public function reindexProductFlat()
    {
        $process = $this->getProductFlat();
        if ($process) {
            $process->reindexAll();
        }
        return $this;
    }
    /**
     * Reindex stock
     * 
     * @return self
     */
    public function reindexStock()
    {
        $process = $this->getStock();
        if ($process) {
            $process->reindexAll();
        }
        return $this;
    }
    /**
     * Reindex search
     * 
     * @return self
     */
    public function reindexSearch()
    {
        $process = $this->getSearch();
        if ($process) {
            $process->reindexAll();
        }
        return $this;
    }
    /**
     * Change product price process status
     * 
     * @param int $status
     * 
     * @return self
     */
    public function changeProductPriceStatus($status)
    {
        $process = $this->getProductPrice();
        if ($process) {
            $process->changeStatus($status);
        }
        return $this;
    }
    /**
     * Change product flat process status
     * 
     * @param int $status
     * 
     * @return self
     */
    public function changeProductFlatStatus($status)
    {
        $process = $this->getProductFlat();
        if ($process) {
            $process->changeStatus($status);
        }
        return $this;
    }
    /**
     * Change stock process status
     * 
     * @param int $status
     * 
     * @return self
     */
    public function changeStockStatus($status)
    {
        $process = $this->getStock();
        if ($process) {
            $process->changeStatus($status);
        }
        return $this;
    }
    /**
     * Change search process status
     * 
     * @param int $status
     * 
     * @return self
     */
    public function changeSearchStatus($status)
    {
        $process = $this->getSearch();
        if ($process) {
            $process->changeStatus($status);
        }
        return $this;
    }
}