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
 * Store
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Model_Core_Store 
    extends Mage_Core_Model_Store 
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
     * Get available currency codes
     *
     * @param bool $skipBaseNotAllowed
     * 
     * @return array
     */
    public function getAvailableCurrencyCodes($skipBaseNotAllowed = false)
    {
        $helper             = $this->getCurrencyGeoFilterHelper();
        if (!$helper->getConfig()->isEnabled($this)  || $helper->getCoreHelper()->isAdmin()) {
            return parent::getAvailableCurrencyCodes($skipBaseNotAllowed);
        }
        $codes              = array();
        foreach (parent::getAvailableCurrencyCodes($skipBaseNotAllowed) as $currencyCode) {
            if ($helper->isCurrencyAllowed($currencyCode)) {
                array_push($codes, $currencyCode);
            }
        }
        $baseCurrencyCode   = $this->getBaseCurrencyCode();
        $baseCodeIndex      = false;
        if (!in_array($baseCurrencyCode, $codes)) {
            $codes[]            = $baseCurrencyCode;
            $baseCodeIndex      = array_keys($codes);
            $baseCodeIndex      = array_pop($baseCodeIndex);
        }
        if ($skipBaseNotAllowed && ($baseCodeIndex !== false)) {
            unset($codes[$baseCodeIndex]);
        }
        return $codes;
    }
    /**
     * Get current currency code
     * 
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        $helper     = $this->getCurrencyGeoFilterHelper();
        if (!$helper->getConfig()->isEnabled($this) || $helper->getCoreHelper()->isAdmin()) {
            return parent::getCurrentCurrencyCode();
        }
        $code       = $this->_getSession()->getCurrencyCode();
        if (empty($code)) {
            $code       = $this->getDefaultCurrencyCode();
        }
        if (in_array($code, $this->getAvailableCurrencyCodes(true))) {
            return $code;
        }
        $codes      = array_values($this->getAvailableCurrencyCodes(true));
        if (empty($codes)) {
            return $this->getDefaultCurrencyCode();
        }
        return array_shift($codes);
    }
}