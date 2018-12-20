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
 * @package     Innoexts_CurrencyGeoFilter
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Currency country controller
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Adminhtml_Currency_CountryController 
    extends Mage_Adminhtml_Controller_Action 
{
    /**
     * Get currency geo filter helper
     *
     * @return Innoexts_CurrencyGeoFilter_Helper_Data
     */
    protected function getCurrencyGeoFilterHelper()
    {
        return Mage::helper('currencygeofilter');
    }
    /**
     * Get admin session
     * 
     * @return Mage_Admin_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('admin/session');
    }
    /**
     * Check is allowed action
     * 
     * @return bool
     */
    protected function _isAllowed()
    {
        $session = $this->getSession();
        switch ($this->getRequest()->getActionName()) {
            case 'index': 
                return $session->isAllowed('system/currency_country'); 
                break;
            default: 
                return $session->isAllowed('system/currency_country'); 
                break;
        }
    }
    /**
     * Init actions
     *
     * @return self
     */
    protected function _initAction()
    {
        $helper = $this->getCurrencyGeoFilterHelper();
        $this->loadLayout()->_setActiveMenu('system/currency_country')
            ->_addBreadcrumb($helper->__('System'), $helper->__('System'))
            ->_addBreadcrumb($helper->__('Currency Geo Filter'), $helper->__('Currency Geo Filter'));
        return $this;
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $helper = $this->getCurrencyGeoFilterHelper();
        $this->_title($helper->__('System'))
            ->_title($this->__('Currency Geo Filter'));
        $this->_initAction();
        $this->renderLayout();
    }
    /**
     * Save action
     */
    public function saveAction()
    {
        $helper = $this->getCurrencyGeoFilterHelper();
        $currencies = $this->getRequest()->getParam('currency');
        if (is_array($currencies)) {
            try {
                Mage::getModel('currencygeofilter/currency')->saveCountries($currencies);
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('Currency countries have been saved.'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}