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
 * Catalog rule actions tab
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Store_Switcher_Form_Renderer_Fieldset_Element 
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element 
    implements Varien_Data_Form_Element_Renderer_Interface 
{
    /**
     * Form element which re-rendering
     *
     * @var Varien_Data_Form_Element_Fieldset
     */
    protected $_element;
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
     * Constructor
     */
    protected function _construct()
    {
        $this->setTemplate('innoexts/currencypricing/store/switcher/form/renderer/fieldset/element.phtml');
    }
    /**
     * Retrieve an element
     *
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function getElement()
    {
        return $this->_element;
    }
    /**
     * Render element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * 
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
    /**
     * Return html for store switcher hint
     * 
     * @return string
     */
    public function getHintHtml()
    {
        return Mage::getBlockSingleton('adminhtml/store_switcher')->getHintHtml();
    }
}