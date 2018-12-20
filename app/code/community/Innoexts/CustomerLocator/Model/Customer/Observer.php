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
 * Customer observer
 *
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CustomerLocator_Model_Customer_Observer 
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
     * Customer after login handler
     * 
     * @param 	Varien_Event_Observer $observer
     * 
     * @return 	self
     */
    public function loginAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getModel();
        if ($customer && ($customer instanceof Mage_Customer_Model_Customer)) {
            $this->getCustomerLocatorHelper()->unsetCustomerAddress();
        }
        return $this;
    }
    /**
     * Customer after logout handler
     * 
     * @param 	Varien_Event_Observer $observer
     * 
     * @return 	self
     */
    public function logoutAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($customer && ($customer instanceof Mage_Customer_Model_Customer)) {
            $this->getCustomerLocatorHelper()->unsetCustomerAddress();
        }
        return $this;
    }
}