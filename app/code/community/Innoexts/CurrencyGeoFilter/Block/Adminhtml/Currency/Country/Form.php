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
 * Currency country form
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Block_Adminhtml_Currency_Country_Form 
    extends Mage_Adminhtml_Block_Template 
{
    /**
     * Currencies
     *
     * @var array
     */
    protected $_currencies;
    /**
     * Countries
     *
     * @var array
     */
    protected $_countries;
    /**
     * Get helper
     *
     * @return Innoexts_CurrencyGeoFilter_Helper_Data
     */
    protected function getCurrencyGeoFilterHelper()
    {
        return Mage::helper('currencygeofilter');
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('innoexts/currencygeofilter/currency/country/form.phtml');
    }
    /**
     * Get countries
     * 
     * @return array
     */
    protected function getCountries()
    {
        if (is_null($this->_countries)) {
            $source = new Mage_Adminhtml_Model_System_Config_Source_Country();
            $countries = $source->toOptionArray(true);
            if (count($countries)) {
                $this->_countries = $countries;
            }
        }
        return $this->_countries;
    }
    /**
     * Get currencies
     * 
     * @return array
     */
    protected function getCurrencies()
    {
        if (is_null($this->_currencies)) {
            $currencyModel = Mage::getModel('directory/currency');
            $currencies = $currencyModel->getConfigAllowCurrencies();
            sort($currencies);
            if (count($currencies)) {
                $this->_currencies = $currencies;
            }
        }
        return $this->_currencies;
    }
    /**
     * Get currency countries
     * 
     * @param string|array $currency
     * 
     * @return array
     */
    protected function getCurrencyCountries($currency)
    {
        $currencyModel = Mage::getModel('currencygeofilter/currency');
        return $currencyModel->getCurrencyCountries($currency);
    }
    /**
     * Get save URL
     */
    protected function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }
}