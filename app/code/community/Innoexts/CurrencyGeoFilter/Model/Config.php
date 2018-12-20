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
 * Currency geo filter config
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Model_Config 
    extends Varien_Object 
{
    /**
     * Config path constants
     */
    const XML_PATH_CURRENCY_GEO_FILTER_ACTIVE                   = 'currency/geo_filter_options/active';
    const XML_PATH_CURRENCY_GEO_FILTER_ORDER_READJUST           = 'currency/geo_filter_options/order_readjust';
    const XML_PATH_CURRENCY_GEO_FILTER_ORDER_READJUST_METHOD    = 'currency/geo_filter_options/order_readjust_method';
    const XML_PATH_CURRENCY_GEO_FILTER_REDIRECT_BY_CURRENCY     = 'currency/geo_filter_options/redirect_by_currency';
    
    const CURRENCY_GEO_FILTER_SHIPPING_ORDER_READJUST_METHOD    = 'shipping';
    const CURRENCY_GEO_FILTER_BILLING_ORDER_READJUST_METHOD     = 'billing';
    /**
     * Check if currency geo filter enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CURRENCY_GEO_FILTER_ACTIVE, $store);
    }
    /**
     * Check if currency readjusting is required for checkout
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isOrderReadjust($store = null)
    {
        return (
            $this->isEnabled($store) && 
            Mage::getStoreConfigFlag(self::XML_PATH_CURRENCY_GEO_FILTER_ORDER_READJUST, $store)
        );
    }
    /**
     * Retrieve order readjusting method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function getOrderReadjustMethod($store = null)
    {
        return ($this->isOrderReadjust($store)) ? 
            Mage::getStoreConfig(self::XML_PATH_CURRENCY_GEO_FILTER_ORDER_READJUST_METHOD, $store) : 
            null;
    }
    /**
     * Is shipping order readjusting method active
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isShippingOrderReadjustMethod($store = null)
    {
        return ($this->getOrderReadjustMethod($store) == self::CURRENCY_GEO_FILTER_SHIPPING_ORDER_READJUST_METHOD) ? 
            true : false;
    }
    /**
     * Is billing order readjusting method active
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isBillingOrderReadjustMethod($store = null)
    {
        return ($this->getOrderReadjustMethod($store) == self::CURRENCY_GEO_FILTER_BILLING_ORDER_READJUST_METHOD) ? 
            true : false;
    }
    /**
     * Check if redirect by currency flag is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isRedirectByCurrency($store = null)
    {
        return (
            $this->isEnabled($store) && 
            Mage::getStoreConfigFlag(self::XML_PATH_CURRENCY_GEO_FILTER_REDIRECT_BY_CURRENCY, $store)
        );
    }
}