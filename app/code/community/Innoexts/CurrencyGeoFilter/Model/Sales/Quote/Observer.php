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
 * @package     Innoexts_CurrencyGeoFilter
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Quote observer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Model_Sales_Quote_Observer 
{
    /**
     * Get currency geo filter helper
     * 
     * @return Innoexts_CurrencyGeoFilter_Helper_Data
     */
    protected function getCurrencyGeoFilterHelper()
    {
        return Mage::helper('currencygeofilter');
    }
    /**
     * Preset address currency
     * 
     * @param Varien_Event_Observer $quoteAddress
     * 
     * @return self
     */
    protected function presetAddressCurrency($quoteAddress)
    {
        $helper                 = $this->getCurrencyGeoFilterHelper();
        if ($helper->getCoreHelper()->isAdmin()) {
            return $this;
        }
        if (!$quoteAddress) {
            return $this;
        }
        $quote                  = $quoteAddress->getQuote();
        if (!$quote) {
            return $this;
        }
        $store                  = $quote->getStore();
        if (!$store) {
            return $this;
        }
        $config                 = $helper->getConfig();
        if (!$config->isOrderReadjust($store)) {
            return $this;
        }
        if (
            $config->isShippingOrderReadjustMethod($store) && 
            ($quoteAddress->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_BILLING)
        ) {
            return $this;
        }
        if (
            $config->isBillingOrderReadjustMethod($store) && 
            ($quoteAddress->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
        ) {
            return $this;
        }
        $helper->getCustomerLocatorHelper()
            ->applyQuoteAddressToCustomerAddress($quoteAddress);
        $store->setCurrentCurrency(null);
        $currency               = $store->getCurrentCurrency();
        if ($quote->getQuoteCurrencyCode() != $currency->getCode()) {
            $quote->collectTotals();
        }
        return $this;
    }
    /**
     * Address before save
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function addressBeforeSave(Varien_Event_Observer $observer)
    {
        $quoteAddress           = $observer->getEvent()->getQuoteAddress();
        if (!$quoteAddress) {
            return $this;
        }
        $this->presetAddressCurrency($quoteAddress);
        return $this;
    }
}