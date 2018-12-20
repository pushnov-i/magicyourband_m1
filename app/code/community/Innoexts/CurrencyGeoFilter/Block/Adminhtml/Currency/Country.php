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
 * Currency country
 *
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Block_Adminhtml_Currency_Country 
    extends Mage_Adminhtml_Block_Widget_Container 
{
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
        $helper         = $this->getCurrencyGeoFilterHelper();
        $this->setTemplate('innoexts/currencygeofilter/currency/country.phtml');
        parent::__construct();
        $this->_addButton('save', array(
            'label'     => $helper->__('Save'), 
            'onclick'   => 'currencyCountryForm.submit();', 
            'class'     => 'save', 
        ), 1);
        
    }
    /**
     * Preparing child blocks
     * 
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild('form', 
            $this->getLayout()
                ->createBlock('currencygeofilter/adminhtml_currency_country_form')
        );
        parent::_prepareLayout();
    }
    /**
     * Get form html
     * 
     * @return string
     */
    protected function getFormHtml()
    {
        return $this->getChildHtml('form');
    }
    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return $this->getCurrencyGeoFilterHelper()->__('Currency Geo Filter');
    }
    /**
     * Get header css class
     * 
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-admin-currency-country';
    }
    /**
     * Get header html
     * 
     * @return string
     */
    public function getHeaderHtml()
    {
        return '<h3 class="'.$this->getHeaderCssClass().'">'.$this->getHeaderText().'</h3>';
    }
}