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
 * Product group price tab renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_Group_Renderer 
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group 
{
    /**
     * Store
     * 
     * @var Mage_Core_Model_Store
     */
    protected $_store;
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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(
            'innoexts/currencypricing/catalog/product/edit/tab/price/group/renderer.phtml'
        );
    }
    /**
     * Get control HTML id
     * 
     * @return string
     */
    public function getControlHtmlId()
    {
        return ($this->getElement()) ? $this->getElement()->getHtmlId().'_control' : 'control';
    }
    /**
     * Get control JS object name
     * 
     * @return string
     */
    public function getControlJsObjectName()
    {
        return $this->_camelize($this->getControlHtmlId());
    }
    /**
     * Get store
     * 
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            $this->_store = Mage::app()->getStore($storeId);
        }
        return $this->_store;
    }
    /**
     * Get default currency code
     * 
     * @return string
     */
    public function getDefaultCurrencyCode()
    {
        return $this->getStore()->getBaseCurrencyCode();
    }
    /**
     * Get currency codes
     * 
     * @return array
     */
    public function getCurrencyCodes()
    {
        return $this->getCurrencyPricingHelper()
            ->getCoreHelper()
            ->getCurrencyHelper()
            ->getCodes();
    }
    /**
     * Check if group price is fixed
     * 
     * @return bool
     */
    public function isGroupPriceFixed()
    {
        return $this->getCurrencyPricingHelper()
            ->getProductHelper()
            ->isGroupPriceFixed($this->getProduct());
    }
    /**
     * Get values
     * 
     * @return array
     */
    public function getValues()
    {
        $helper             = $this->getCurrencyPricingHelper();
        $productHelper      = $helper->getProductHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $element            = $this->getElement();
        $product            = $this->getProduct();
        $websiteId          = $productHelper->getWebsiteId($product);
        $data               = $element->getValue();
        $values             = array();
        if (is_array($data)) {
            usort($data, array($this, '_sortGroupPrices'));
            $values         = $data;
        }
        $_values    = array();
        foreach ($values as $k => $v) {
            if (!$priceHelper->isInactiveData($v, $websiteId)) {
                $_values[$k]    = $v;
            }
        }
        $values = $_values;
        foreach ($values as &$v) {
            $v['readonly']  = ($priceHelper->isAncestorData($v, $websiteId)) ? true : false;
        }
        return $values;
    }
    /**
     * Sort tier price values callback method
     *
     * @param array $a
     * @param array $b
     * 
     * @return int
     */
    protected function _sortTierPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group']) < $this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        if ($this->isGroupPriceFixed()) {
            if ($a['currency'] != $b['currency']) {
                return $a['currency'] < $b['currency'] ? -1 : 1;
            }
        }
        return 0;
    }
    /**
     * Prepare layout
     *
     * @return self
     */
    protected function _prepareLayout()
    {
        $helper     = $this->getCurrencyPricingHelper();
        $button     = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $helper->__('Add Group Price'), 
                'class'     => 'add-button', 
            ));
        $button->setName('add_group_price_item_button');
        $this->setChild('add_button', $button);
        Mage_Core_Block_Abstract::_prepareLayout();
        return $this;
    }
}