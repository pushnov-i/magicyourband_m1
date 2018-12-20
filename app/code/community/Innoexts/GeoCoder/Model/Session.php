<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 *
 * @category    Innoexts
 * @package     Innoexts_GeoCoder
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Geo coder session
 * 
 * @category   Innoexts
 * @package    Innoexts_GeoCoder
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_GeoCoder_Model_Session 
    extends Innoexts_Core_Model_Session_Abstract 
{
    /**
     * Namespace
     * 
     * @var string
     */
    protected $_namespace = 'innoexts_geocoder';
    /**
     * Get key by string
     * 
     * @param string $string
     * 
     * @return string
     */
    protected function getKeyByString($string)
    {
        return 'hash'.md5($string);
    }
    /**
     * Set coordinates
     * 
     * @param string $addressString
     * @param Varien_Object $coordinates
     * 
     * @return self
     */
    public function setCoordinates($addressString, $coordinates)
    {
        $this->setData($this->getKeyByString($addressString), $coordinates);
        return $this;
    }
    /**
     * Get coordinates
     * 
     * @param string $addressString
     * 
     * @return Varien_Object
     */
    public function getCoordinates($addressString)
    {
        return $this->getData($this->getKeyByString($addressString));
    }
    /**
     * Set address
     * 
     * @param string $coordinatesString
     * @param Varien_Object $address
     * 
     * @return self
     */
    public function setAddress($coordinatesString, $address)
    {
        $this->setData($this->getKeyByString($coordinatesString), $address);
        return $this;
    }
    /**
     * Get address
     * 
     * @param string $coordinatesString
     * 
     * @return Varien_Object
     */
    public function getAddress($coordinatesString)
    {
        return $this->getData($this->getKeyByString($coordinatesString));
    }
}