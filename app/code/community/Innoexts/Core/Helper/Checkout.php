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
 * Checkout helper
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Helper_Checkout 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * Get quote
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->getCoreHelper()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            return Mage::getSingleton('checkout/session')->getQuote();
        }
    }
    /**
     * Get quote address
     * 
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getQuoteAddress()
    {
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            return $quote->getBillingAddress();
        } else {
            return $quote->getShippingAddress();
        }
    }
    /**
     * Get full controller names
     * 
     * @return array
     */
    public function getFullControllerNames()
    {
        return array(
            'checkout_onepage', 
            'adminhtml_sales_order_create', 
            'adminhtml_sales_order_edit', 
        );
    }
}