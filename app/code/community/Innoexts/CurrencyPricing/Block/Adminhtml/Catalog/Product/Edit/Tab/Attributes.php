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
 * Product attributes tab
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes 
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes 
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
     * Get product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }
    /**
     * Prepare form before rendering HTML
     * 
     * @return self
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $group          = $this->getGroup();
        if (!$group) {
            return $this;
        }
        $form           = $this->getForm();
        $fieldset       = $form->getElement('group_fields'.$group->getId());
        if (!$fieldset) {
            return $this;
        }
        $helper         = $this->getCurrencyPricingHelper();
        $product        = $this->getProduct();
        if ($form->getElement('price')) {
            $fieldset->addField('compound_prices', 'text', array(
                'name'      => 'compound_prices', 
                'label'     => $helper->__('Compound Price'), 
                'title'     => $helper->__('Compound Price'), 
                'required'  => false, 
                'value'     => $product->getCompoundPrices(), 
            ), 'price')->setRenderer($this->getLayout()->createBlock(
                'currencypricing/adminhtml_catalog_product_edit_tab_price_compound_renderer'
            ));
        }
        if ($form->getElement('special_price')) {
            $fieldset->addField('compound_special_prices', 'text', array(
                'name'      => 'compound_special_prices', 
                'label'     => $helper->__('Compound Special Price'), 
                'title'     => $helper->__('Compound Special Price'), 
                'required'  => false, 
                'value'     => $product->getCompoundSpecialPrices(), 
            ), 'special_price')->setRenderer($this->getLayout()->createBlock(
                'currencypricing/adminhtml_catalog_product_edit_tab_price_compoundSpecial_renderer'
            ));
        }
        return $this;
    }
}