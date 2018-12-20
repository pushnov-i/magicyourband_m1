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
 * Shipping helper
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Helper_Shipping 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * Get carrier name
     * 
     * @param string $carrierCode
     * 
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        $name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
        if ($name) {
            return $name;
        }
        return $carrierCode;
    }
    /**
     * Get all carriers
     * 
     * @return array
     */
    public function getAllCarriers()
    {
         return Mage::getSingleton('shipping/config')->getAllCarriers();
    }
    /**
     * Get carriers
     * 
     * @return array
     */
    public function getCarriers()
    {
        $carriers = array();
        foreach ($this->getAllCarriers() as $carrier) {
            if (!$carrier->isActive()) {
                continue;
            }
            $carriers[$carrier->getCarrierCode()] = $carrier;
        }
        return $carriers;
    }
    /**
     * Get carrier codes by method full codes
     * 
     * @param array $methodFullCodes
     * 
     * @return array
     */
    public function getCarrierCodesByMethodFullCodes($methodFullCodes)
    {
        $carrierCodes = array();
        if (empty($methodFullCodes)) {
            return $carrierCodes;
        }
        foreach ($this->getCarriers() as $carrier) {
            $carrierCode = $carrier->getCarrierCode();
            foreach ($carrier->getAllowedMethods() as $methodCode => $methodName) {
                $methodFullCode = $carrierCode.'_'.$methodCode;
                if (in_array($methodFullCode, $methodFullCodes)) {
                    array_push($carrierCodes, $carrierCode);
                }
            }
        }
        return $carrierCodes;
    }
    /**
     * Get method codes by carrier code and method full codes
     * 
     * @param string $carrierCode
     * @param array $methodFullCodes
     * 
     * @return array
     */
    public function getMethodCodesByCarrierCodeAndMethodFullCodes($carrierCode, $methodFullCodes)
    {
        $methodCodes = array();
        if (empty($carrierCode) || empty($methodFullCodes)) {
            return $methodCodes;
        }
        foreach ($this->getCarriers() as $carrier) {
            if ($carrierCode == $carrier->getCarrierCode()) {
                foreach ($carrier->getAllowedMethods() as $methodCode => $methodName) {
                    $methodFullCode = $carrierCode.'_'.$methodCode;
                    if (in_array($methodFullCode, $methodFullCodes)) {
                        array_push($methodCodes, $methodCode);
                    }
                }
            }
        }
        return $methodCodes;
    }
    /**
     * Get method options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getMethodOptions($required = true, $emptyLabel = '', $emptyValue = '')
    {
        $options    = array();
        foreach ($this->getCarriers() as $carrier) {
            $carrierCode    = $carrier->getCarrierCode();
            $carrierName    = $this->getCarrierName($carrierCode);
            foreach ($carrier->getAllowedMethods() as $methodCode => $methodName) {
                if (!isset($options[$carrierCode])) {
                    $options[$carrierCode] = array(
                        'value' => array(), 
                        'label' => $carrierName, 
                    );
                }
                $methodFullCode = $carrierCode.'_'.$methodCode;
                $options[$carrierCode]['value'][$methodFullCode] = array(
                    'value' => $methodFullCode, 
                    'label' => $methodName, 
                );
            }
        }
        $this->getCoreHelper()->prepareOptions($options, $required, $emptyLabel, $emptyValue);
        return $options;
    }
    /**
     * Get method full codes
     * 
     * @return array
     */
    public function getMethodFullCodes()
    {
        $methodCodes = array();
        foreach ($this->getCarriers() as $carrier) {
            $carrierCode = $carrier->getCarrierCode();
            foreach ($carrier->getAllowedMethods() as $methodCode => $methodName) {
                $methodFullCode = $carrierCode.'_'.$methodCode;
                array_push($methodCodes, $methodFullCode);
            }
        }
        return $methodCodes;
    }
}