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
 * @package     Innoexts_CustomerLocator
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract location attribute backend
 * 
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CustomerLocator_Model_Adminhtml_System_Config_Backend_Location_Attribute_Abstract 
    extends Mage_Core_Model_Config_Data 
{
    /**
     * Get customer locator helper
     * 
     * @return Innoexts_CustomerLocator_Helper_Data
     */
    protected function getCustomerLocatorHelper()
    {
        return Mage::helper('customerlocator');
    }
    /**
     * Get allowed attributes
     * 
     * @return array
     */
    protected function _getAllowedAttributes()
    {
        if ($this->getData('groups/options/fields/allow_attributes/inherit')) {
            return explode(',', Mage::getConfig()->getNode('customerlocator/options/allow_attributes', $this->getScope(), $this->getScopeId()));
        }
        return $this->getData('groups/options/fields/allow_attributes/value');
    }
    /**
     * Get required attributes
     *
     * @return array
     */
    protected function _getRequiredAttributes()
    {
        if ($this->getData('groups/options/fields/require_attributes/inherit')) {
            return explode(',', Mage::getConfig()->getNode('customerlocator/options/require_attributes', $this->getScope(), $this->getScopeId()));
        }
        return $this->getData('groups/options/fields/require_attributes/value');
    }
}
