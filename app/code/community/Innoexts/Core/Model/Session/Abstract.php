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
 * Abstract session model
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Model_Session_Abstract 
    extends Mage_Core_Model_Session_Abstract 
{
    /**
     * Namespace
     * 
     * @var string
     */
    protected $_namespace = 'innoexts_core';
    /**
     * Constructor
     */
    public function __construct()
    {
        $namespace = $this->_namespace;
        if ($this->getCustomerConfigShare()->isWebsiteScope()) {
            $namespace .= '_' . ($this->getCoreHelper()->getStore()->getWebsite()->getCode());
        }
        $this->init($namespace);
        Mage::dispatchEvent($this->_namespace.'_session_init', array('session' => $this));
    }
    /**
     * Get core helper
     * 
     * @return Innoexts_Core_Helper_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('innoexts_core');
    }
    /**
     * Get customer sharing configuration
     *
     * @return Mage_Customer_Model_Config_Share
     */
    public function getCustomerConfigShare()
    {
        return $this->getCoreHelper()->getCustomerHelper()->getCustomerConfigShare();
    }
}