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
 * Currency geo gilter helper
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Helper_Data 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * Get version helper
     * 
     * @return Innoexts_Core_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getCoreHelper()
            ->getVersionHelper();
    }
    /**
     * Get config
     * 
     * @return Innoexts_CurrencyGeoFilter_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('currencygeofilter/config');
    }
    /**
     * Get customer locator helper
     * 
     * @return Innoexts_CustomerLocator_Helper_Data
     */
    public function getCustomerLocatorHelper()
    {
        return Mage::helper('customerlocator');
    }
    /**
     * Get currency singleton
     * 
     * @return Innoexts_CurrencyGeoFilter_Model_Currency
     */
    public function getCurrencySingleton()
    {
        return Mage::getSingleton('currencygeofilter/currency');
    }
    /**
     * Get currency codes
     * 
     * @return array
     */
    public function getCurrencyCodes()
    {
        $currencyCodes      = array();
        $countryId          = null;
        $address            = $this->getCustomerLocatorHelper()
            ->getCustomerAddress();
        if ($address) {
            $countryId          = $address->getCountryId();
        }
        if ($countryId) {
            $currencyCodes      = $this->getCurrencySingleton()
                ->getCountryCurrencies($countryId);
        }
        return $currencyCodes;
    }
    /**
     * Is currency allowed
     * 
     * @param string $currencyCode
     * 
     * @return boolean
     */
    public function isCurrencyAllowed($currencyCode)
    {
        return (in_array($currencyCode, $this->getCurrencyCodes())) ? true : false;
    }
    /**
     * Get website by base currency code
     * 
     * @param string $currencyCode
     * 
     * @return Mage_Core_Model_Website
     */
    public function getWebsiteByBaseCurrencyCode($currencyCode)
    {
        $website = null;
        foreach ($this->getCoreHelper()->getWebsites() as $_website) {
            if ($currencyCode == $_website->getBaseCurrencyCode()) {
                $website = $_website;
                break;
            }
        }
        return $website;
    }
    /**
     * Get website redirect route names
     * 
     * @return array of string
     */
    public function getWebsiteRedirectRouteNames()
    {
        return array('catalog', 'catalogsearch');
    }
    /**
     * Redirect to website by base currency code
     * 
     * @param Zend_Controller_Response_Http $responce
     * @param string $currencyCode
     * 
     * @return self
     */
    public function redirectWebsiteByBaseCurrencyCode($responce = null, $currencyCode = null)
    {
        if (is_null($responce)) {
            $responce       = Mage::app()->getResponse();
        }
        $store          = $this->getCoreHelper()->getStore();
        if (is_null($currencyCode)) {
            $currencyCode   = $store->getCurrentCurrencyCode();
        }
        $website        = $this->getWebsiteByBaseCurrencyCode($currencyCode);
        if ($website && ($website->getId() != $store->getWebsiteId())) {
            $defaultStore   = $website->getDefaultStore();
            if ($defaultStore) {
                $defaultStore->setCurrentCurrencyCode($currencyCode);
                $url            = $defaultStore->getUrl();
                $responce->setRedirect($url);
            }
        }
        return $this;
    }
}