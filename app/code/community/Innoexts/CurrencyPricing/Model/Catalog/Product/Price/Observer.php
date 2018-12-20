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
 * Product price observer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Catalog_Product_Price_Observer 
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
     * Get product price helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product_Price
     */
    protected function getProductPriceHelper()
    {
        return $this->getCurrencyPricingHelper()
            ->getProductPriceHelper();
    }
    /**
     * Get product price indexer helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Catalog_Product_Price_Indexer
     */
    protected function getProductPriceIndexerHelper()
    {
        return $this->getCurrencyPricingHelper()
            ->getProductPriceIndexerHelper();
    }
    /**
     * Save compound price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function saveCompoundPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->saveCompoundPrice($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Save compound special price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function saveCompoundSpecialPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->saveCompoundSpecialPrice($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load compound price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function loadCompoundPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->loadCompoundPrice($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load compound special price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function loadCompoundSpecialPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->loadCompoundSpecialPrice($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load collection compound price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function loadCollectionCompoundPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->loadCollectionCompoundPrice($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Load collection compound special price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function loadCollectionCompoundSpecialPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->loadCollectionCompoundSpecialPrice($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove compound price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function removeCompoundPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->removeCompoundPrice($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Remove compound special price
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function removeCompoundSpecialPrice(Varien_Event_Observer $observer)
    {
        $this->getProductPriceHelper()
            ->removeCompoundSpecialPrice($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Before product collection load
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function beforeProductCollectionLoad(Varien_Event_Observer $observer)
    {
        $this->getProductPriceIndexerHelper()
            ->addPriceIndexFilter($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * After product collection apply limitations
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function afterProductCollectionApplyLimitations(Varien_Event_Observer $observer)
    {
        $this->getProductPriceIndexerHelper()
            ->addPriceIndexFilter($observer->getEvent()->getCollection());
        return $this;
    }
}
