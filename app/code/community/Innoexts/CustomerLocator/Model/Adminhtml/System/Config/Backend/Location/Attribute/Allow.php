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
 * Allowed location attribute backend
 * 
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CustomerLocator_Model_Adminhtml_System_Config_Backend_Location_Attribute_Allow 
    extends Innoexts_CustomerLocator_Model_Adminhtml_System_Config_Backend_Location_Attribute_Abstract 
{
    /**
     * After save
     * 
     * @return self
     */
    protected function _afterSave()
    {
        $helper = $this->getCustomerLocatorHelper();
        $exceptions = array();
        $allowedAttributes = $this->_getAllowedAttributes();
        if (!in_array($helper->getConfig()->getCountryAttribute(), $allowedAttributes)) {
            $exceptions[] = $helper->__('Country attribute must be selected.');
        }
        if ($exceptions) {
            Mage::throwException(join("\n", $exceptions));
        }
        return $this;
    }
}