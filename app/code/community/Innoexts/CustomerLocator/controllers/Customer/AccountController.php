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
require_once 'Mage/Customer/controllers/AccountController.php';
/**
 * Customer address controller
 * 
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CustomerLocator_Customer_AccountController 
    extends Mage_Customer_AccountController 
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
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $pattern = '/^(applyAddress)/i';
        if (preg_match($pattern, $action)) {
            $this->setFlag('', 'no-dispatch', false);
            $this->_getSession()->setNoReferer(true);
        }
    }
    /**
     * Apply address action
     */
    public function applyAddressAction()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $session    = $this->getCustomerLocatorHelper()->getCoreHelper()->getCoreSession();
            $street1    = trim($request->getPost('street1'));
            $street2    = trim($request->getPost('street2'));
            $street     = array();
            if ($street1) {
                array_push($street, $street1);
            }
            if ($street2) {
                array_push($street, $street2);
            }
            $address    = new Varien_Object(array(
                'country_id'   => trim($request->getPost('country_id')), 
                'region_id'    => trim($request->getPost('region_id')), 
                'region'       => trim($request->getPost('region')), 
                'city'         => trim($request->getPost('city')), 
                'postcode'     => trim($request->getPost('postcode')), 
                'street'       => (count($street)) ? $street : null, 
            ));
            $helper->setCustomerAddress($address);
            $session->addSuccess($helper->__('Your location has been saved.'));
        }
        $this->_redirectReferer();
    }
    /**
     * Apply address id action
     */
    public function applyAddressIdAction()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $session    = $helper->getCoreHelper()->getCoreSession();
            $addressId  = trim($request->getPost('address_id'));
            $helper->setCustomerAddressId($addressId);
            $session->addSuccess($helper->__('Your location has been saved.'));
        }
        $this->_redirectReferer();
    }
    /**
     * Apply address coordinates action
     */
    public function applyAddressCoordinatesAction()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $latitude       = trim($request->getPost('latitude'));
            $longitude      = trim($request->getPost('longitude'));
            if ($latitude && $longitude) {
                $coordinates    = new Varien_Object(array(
                    'latitude'      => $latitude, 
                    'longitude'     => $longitude, 
                ));
                $helper->setCustomerCoordinates($coordinates);
            }
        }
        $this->_redirectReferer();
    }
}