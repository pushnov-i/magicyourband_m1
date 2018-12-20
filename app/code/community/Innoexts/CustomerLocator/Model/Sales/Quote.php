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
 * @package     Innoexts_CustomerLocator
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Quote
 * 
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CustomerLocator_Model_Sales_Quote 
    extends Mage_Sales_Model_Quote 
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
     * Assign customer
     *
     * @param  Mage_Customer_Model_Customer    $customer
     * @param  Mage_Sales_Model_Quote_Address  $billingAddress
     * @param  Mage_Sales_Model_Quote_Address  $shippingAddress
     * 
     * @return self
     */
    public function assignCustomerWithAddressChange(
        Mage_Customer_Model_Customer    $customer,
        Mage_Sales_Model_Quote_Address  $billingAddress  = null,
        Mage_Sales_Model_Quote_Address  $shippingAddress = null
    )
    {
        parent::assignCustomerWithAddressChange($customer, $billingAddress, $shippingAddress);
        foreach ($this->getAllAddresses() as $address) {
            $this->getCustomerLocatorHelper()
                ->applyCustomerAddressToQuoteAddress($address, true);
        }
        return $this;
    }
}