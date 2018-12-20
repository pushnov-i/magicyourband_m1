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
 * Currency Geo Filter observer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Model_Observer 
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
     * Send response before
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function sendResponseBefore(Varien_Event_Observer $observer)
    {
        $helper     = $this->getCurrencyGeoFilterHelper();
        if (
            !$helper->getCoreHelper()->isAdmin() && 
            $helper->getConfig()->isRedirectByCurrency()
        ) {
            $front      = $observer->getEvent()->getFront();
            if ($front) {
                if (in_array(
                    $front->getRequest()->getRequestedRouteName(), 
                    $helper->getWebsiteRedirectRouteNames()
                )) {
                    $helper->redirectWebsiteByBaseCurrencyCode($front->getResponse());
                }
            }
        }
        return $this;
    }
}