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
 * Form element renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
abstract class Innoexts_Core_Block_Adminhtml_Widget_Form_Element_Renderer_Abstract 
    extends Mage_Adminhtml_Block_Widget 
    implements Varien_Data_Form_Element_Renderer_Interface 
{
    /**
     * Form element
     * 
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_element;
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
     * Get form element
     * 
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->_element;
    }
    /**
     * Render block
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * 
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
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