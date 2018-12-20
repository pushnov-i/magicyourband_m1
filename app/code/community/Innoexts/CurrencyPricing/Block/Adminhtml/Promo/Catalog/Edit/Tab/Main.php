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
 * Catalog rule main tab
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Promo_Catalog_Edit_Tab_Main 
    extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Main 
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
     * Get version helper
     * 
     * @return Innoexts_Core_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getCurrencyPricingHelper()->getVersionHelper();
    }
    /**
     * Get catalog rule
     * 
     * @return Mage_CatalogRule_Model_Rule
     */
    protected function getCatalogRule()
    {
        return Mage::registry('current_promo_catalog_rule');
    }
    /**
     * Get currency values
     * 
     * @return array
     */
    protected function getCurrencyValues()
    {
        $helper = $this->getCurrencyPricingHelper();
        $options = array();
        foreach ($helper->getCoreHelper()->getCurrencyHelper()->getCodes() as $currencyCode) {
            array_push($options, array(
                'label' => $currencyCode, 
                'value' => $currencyCode
            ));
        }
        return $options;
    }
    /**
     * Prepare form
     * 
     * @return self
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form           = $this->getForm();
        if (!$form) {
            return $this;
        }
        $fieldset       = $form->getElement('base_fieldset');
        if (!$fieldset) {
            return $this;
        }
        $helper         = $this->getCurrencyPricingHelper();
        $catalogRule    = $this->getCatalogRule();
        $isReadonly     = $catalogRule->isReadonly();
        
        $fieldset->addField('currencies', 'multiselect', array(
            'name'          => 'currencies[]', 
            'label'         => $helper->__('Currencies'), 
            'title'         => $helper->__('Currencies'), 
            'required'      => true, 
            'value'         => $catalogRule->getCurrencies(), 
            'values'        => $this->getCurrencyValues(), 
            'readonly'      => $isReadonly, 
            'disabled'      => $isReadonly, 
        ), 'customer_group_ids');
        
        return $this;
    }
}