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
 * Customer address block
 * 
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CustomerLocator_Block_Customer_Address 
    extends Mage_Core_Block_Template 
{
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
     * Get address
     *
     * @return Varien_Object
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getCustomerLocatorHelper()->getCustomerAddress();
        }
        return $this->_address;
    }
    /**
     * Get country identifier
     * 
     * @return string
     */
    public function getCountryId()
    {
        return $this->getAddress()->getCountryId();
    }
    /**
     * Get region identifier
     * 
     * @return string
     */
    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }
    /**
     * Get region
     * 
     * @return string
     */
    public function getRegion()
    {
        return $this->getAddress()->getRegion();
    }
    /**
     * Get city
     * 
     * @return string
     */
    public function getCity()
    {
        return $this->getAddress()->getCity();
    }
    /**
     * Get postal code
     * 
     * @return string
     */
    public function getPostcode()
    {
        return $this->getAddress()->getPostcode();
    }
    /**
     * Get street 1
     * 
     * @return string
     */
    public function getStreet1()
    {
        return $this->getAddressHelper()->getStreet($this->getAddress(), 1);
    }
    /**
     * Get street 2
     * 
     * @return string
     */
    public function getStreet2()
    {
        return $this->getAddressHelper()->getStreet($this->getAddress(), 2);
    }
    /**
     * Get addresses
     * 
     * @return Mage_Customer_Model_Customer
     */
    protected function getAddresses()
    {
        $addresses      = array();
        $customerHelper = $this->getCustomerHelper();
        if ($customerHelper->isLoggedIn()) {
            $addresses = $customerHelper->getCustomer()->getAddresses();
        }
        return $addresses;
    }
    /**
     * Check if customer has addresses
     * 
     * @return bool
     */
    public function hasAddresses()
    {
        return (count($this->getAddresses())) ? true : false;
    }
    /**
     * Get addresses options
     * 
     * @return array 
     */
    protected function getAddressesOptions()
    {
        $helper = $this->getCustomerLocatorHelper();
        $options = array();
        $addresses = $this->getAddresses();
        array_push(
            $options, 
            array(
                'value' => '', 
                'label' => $helper->__('Please select address')
            )
        );
        foreach ($addresses as $address) {
            array_push(
                $options, 
                array(
                    'value' => $address->getId(), 
                    'label' => $address->format('oneline')
                )
            );
        }
        return $options;
    }
    /**
     * Get customer address html select
     * 
     * @return string
     */
    public function getAddressHtmlSelect()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $addressId  = null;
        $select     = $this->getLayout()->createBlock('core/html_select')
            ->setName('address_id')
            ->setId('address_id')
            ->setTitle($helper->__('Address'))
            ->setClass('validate-select')
            ->setValue($addressId)
            ->setOptions($this->getAddressesOptions())
            ->setExtraParams('onchange="customerAddressIdForm.submit();"');
        return $select->getHtml();
    }
}