<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_Core
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog fieldset element renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Block_Adminhtml_Catalog_Form_Renderer_Fieldset_Element 
    extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element 
{
    /**
     * Store
     * 
     * @var Mage_Core_Model_Store
     */
    protected $_store;
    /**
     * Get core helper
     * 
     * @return Innoexts_Core_Helper_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('innoexts_core');
    }
    /**
     * Get product price helper
     * 
     * @return Innoexts_Core_Helper_Catalog_Product_Price
     */
    public function getProductPriceHelper()
    {
        return $this->getCoreHelper()
            ->getProductHelper()
            ->getPriceHelper();
    }
    /**
     * Set form element
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * 
     * @return self
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element; 
        return $this;
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
     * Get website id
     * 
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->getStore()->getWebsiteId();
    }
    /**
     * Get store id
     * 
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }
    /**
     * Check if global price scope is active
     * 
     * @return bool
     */
    public function isGlobalPriceScope()
    {
        return $this->getProductPriceHelper()->isGlobalScope();
    }
    /**
     * Check if website price scope is active
     * 
     * @return bool
     */
    public function isWebsitePriceScope()
    {
        return $this->getProductPriceHelper()->isWebsiteScope();
    }
    /**
     * Check if store price scope is active
     * 
     * @return bool
     */
    public function isStorePriceScope()
    {
        return $this->getProductPriceHelper()->isStoreScope();
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
}