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
 * Customer locator session
 * 
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CustomerLocator_Model_Session 
    extends Innoexts_Core_Model_Session_Abstract 
{
    /**
     * Namespace
     * 
     * @var string
     */
    protected $_namespace = 'customerlocator';
    /**
     * Address
     * 
     * @var Varien_Object
     */
    protected $_address;
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
     * Get address helper
     * 
     * @return Innoexts_Core_Helper_Address
     */
    protected function getAddressHelper()
    {
        return $this->getCustomerLocatorHelper()->getAddressHelper();
    }
    /**
     * Get customer helper
     * 
     * @return Innoexts_Core_Helper_Customer
     */
    protected function getCustomerHelper()
    {
        return $this->getCustomerLocatorHelper()->getCustomerHelper();
    }
    /**
     * Get current address
     * 
     * @return Varien_Object
     */
    protected function _getAddress()
    {
        if (is_null($this->_address)) {
            $address = new Varien_Object();
            $address->setCountryId($this->getCountryId());
            $address->setRegionId($this->getRegionId());
            $address->setRegion($this->getRegion());
            $address->setCity($this->getCity());
            $address->setPostcode($this->getPostcode());
            $address->setStreet($this->getStreet());
            $this->_address = $address;
        }
        return $this->_address;
    }
    /**
     * Check if address is empty
     * 
     * @return bool
     */
    public function isAddressEmpty()
    {
        $this->_getAddress();
        return $this->getAddressHelper()->isEmpty($this->_address);
    }
    /**
     * Get ip address
     * 
     * @return string
     */
    public function getIp()
    {
        $ip = $this->getCoreHelper()->getHttpHelper()->getRemoteAddr();
        return ($ip) ? long2ip(ip2long($ip)) : null;
    }
    /**
     * Get geo ip address
     * 
     * @return Varien_Object
     */
    protected function getGeoIpAddress()
    {
        $address        = null;
        $addressHelper  = $this->getAddressHelper();
        $ip             = $this->getIp();
        if (!$ip) {
            return $address;
        }
        $_address = $this->getCustomerLocatorHelper()->getGeoIpHelper()->getAddressByIp($ip);
        if (!$_address) {
            return $address;
        }
        $_address = $addressHelper->cast($_address);
        if (!$addressHelper->isEmpty($_address)) {
            $address = $_address;
        }
        return $address;
    }
    /**
     * Get customer default address
     * 
     * @return Varien_Object
     */
    protected function getCustomerDefaultAddress()
    {
        $address        = null;
        $customerHelper = $this->getCustomerHelper();
        if (!$customerHelper->isLoggedIn()) {
            return $address;
        }
        $_address = $customerHelper->getCustomer()->getDefaultShippingAddress();
        if (!$_address) {
            return $address;
        }
        $addressHelper  = $this->getAddressHelper();
        $_address       = $addressHelper->cast($_address);
        if (!$addressHelper->isEmpty($_address)) {
            $address = $_address;
        }
        return $address;
    }
    /**
     * Get default address
     * 
     * @return Varien_Object
     */
    protected function getDefaultAddress()
    {
        return $this->getAddressHelper()->cast($this->getCustomerLocatorHelper()->getDefaultAddress());
    }
    /**
     * Locate address
     * 
     * @return self
     */
    protected function locateAddress()
    {
        $helper         = $this->getCustomerLocatorHelper();
        $coreHelper     = $helper->getCoreHelper();
        $address        = null;
        if (!$coreHelper->isAdmin()) {
            if ($helper->useDefaultShippingAddress()) {
                $address        = $this->getCustomerDefaultAddress();
            }
            if (!$address && $helper->useIpGeolocation()) {
                $address        = $this->getGeoIpAddress();
            }
        }
        if (!$address) {
            $address        = $this->getDefaultAddress();
        }
        $this->setAddress($address);
        return $this;
    }
    /**
     * Set shipping address
     * 
     * @param Varien_Object $shippingAddress
     * 
     * @return self
     */
    public function setAddress($address)
    {
        $address        = $this->getAddressHelper()->cast($address);
        $this->unsetAddress();
        $this->setCountryId($address->getCountryId());
        $this->setRegionId($address->getRegionId());
        $this->setRegion($address->getRegion());
        $this->setCity($address->getCity());
        $this->setPostcode($address->getPostcode());
        $this->setStreet($address->getStreet());
        $this->_address = $address;
        return $this;
    }
    /**
     * Set address identifier
     * 
     * @param int $addressId
     * 
     * @return self
     */
    public function setAddressId($addressId)
    {
        $customerHelper = $this->getCustomerHelper();
        if ($customerHelper->isLoggedIn()) {
            $address = $customerHelper->getCustomer()->getAddressById($addressId);
            if ($address) {
                $addressHelper  = $this->getAddressHelper();
                $address        = $addressHelper->cast($address);
                if (!$addressHelper->isEmpty($address)) {
                    $this->setAddress($address);
                }
            }
        }
        return $this;
    }
    /**
     * Retrieve address
     * 
     * @return Varien_Object
     */
    public function getAddress()
    {
        $this->_getAddress();
        if ($this->isAddressEmpty()) {
            $this->locateAddress();
        }
        return $this->_address;
    }
    /**
     * Check if address is set
     * 
     * @return boolean
     */
    public function hasAddress()
    {
        return (!$this->getAddressHelper()->isEmpty($this->getAddress())) ? true : false;
    }
    /**
     * Unset address
     * 
     * @return self
     */
    public function unsetAddress()
    {
        $this->setCountryId(null);
        $this->setRegionId(null);
        $this->setRegion(null);
        $this->setCity(null);
        $this->setPostcode(null);
        $this->setStreet(null);
        $this->_address = null;
        return $this;
    }
    /**
     * Set coordinates
     * 
     * @param Varien_Object $coordinates
     * 
     * @return self
     */
    public function setCoordinates($coordinates)
    {
        $helper         = $this->getCustomerLocatorHelper();
        if ($helper->isCoordinatesGeolocatorEnabled()) {
            $address        = $helper->getGeoCoderHelper()->getAddress($coordinates);
            $addressHelper  = $this->getAddressHelper();
            if (!$addressHelper->isEmpty($address)) {
                $this->setAddress($address);
            }
            $this->setData('coordinates', $coordinates);
        }
        return $this;
    }
}