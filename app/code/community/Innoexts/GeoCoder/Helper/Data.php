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
 * Geo coder helper
 * 
 * @category   Innoexts
 * @package    Innoexts_GeoCoder
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_GeoCoder_Helper_Data 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * HTTP client
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient;
    /**
     * URI
     *
     * @var string
     */
    protected $_uri = 'http://maps.googleapis.com/maps/api/geocode/json';
    /**
     * Get address helper
     * 
     * @return Innoexts_Core_Helper_Address
     */
    public function getAddressHelper()
    {
        return $this->getCoreHelper()->getAddressHelper();
    }
    /**
     * Get HTTP client
     * 
     * @return Zend_Http_Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->_httpClient)) {
            $this->_httpClient = new Zend_Http_Client();
        }
        return $this->_httpClient;
    }
    /**
     * Get URI
     *
     * @return string
     */
    protected function getUri()
    {
        return $this->_uri;
    }
    /**
     * Get session
     * 
     * @return Innoexts_GeoCoder_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('innoexts_geocoder/session');
    }
    /**
     * Get coordinates object
     * 
     * @param Varien_Object $address
     * 
     * @return Varien_Object
     */
    public function getCoordinates($address)
    {
        $coordinates    = new Varien_Object();
        $addressHelper  = $this->getAddressHelper();
        if ($addressHelper->isEmpty($address)) {
            return $coordinates;
        }
        $address        = $addressHelper->cast($address);
        $addressString  = $addressHelper->format($address);
        if (!$addressString) {
            return $coordinates;
        }
        $_coordinates   = $this->getSession()->getCoordinates($addressString);
        if ($_coordinates) {
            return $_coordinates;
        }
        $httpClient     = $this->getHttpClient();
        $httpClient->setUri($this->getUri());
        $httpClient->setParameterGet('address', $addressString);
        $httpClient->setParameterGet('sensor', 'false');
        try {
            $responce = $httpClient->request('GET');
            if ($responce) {
                $data = Zend_Json_Decoder::decode($responce->getBody(), Zend_Json::TYPE_OBJECT);
            }
        } catch (Exception $e) {
            $data = null;
        }
        if (
            $data && 
            isset($data->status) && (strtoupper(trim($data->status)) == 'OK') && 
            isset($data->results) && isset($data->results[0]) && isset($data->results[0]->geometry)
        ) {
            $geometry = $data->results[0]->geometry;
            if (isset($geometry->location) && isset($geometry->location->lat) && isset($geometry->location->lng)) {
                $location = $geometry->location;
                $coordinates->setLatitude((float) $location->lat);
                $coordinates->setLongitude((float) $location->lng);
            }
        }
        $this->getSession()->setCoordinates($addressString, $coordinates);
        return $coordinates;
    }
    /**
     * Get coordinates string
     * 
     * @param Varien_Object $coordinates
     * 
     * @return string
     */
    protected function getCoordinatesString($coordinates)
    {
        return implode(',', array(
            (float) $coordinates->getLatitude(), 
            (float) $coordinates->getLongitude()
        ));
    }
    /**
     * Get address
     * 
     * @param Varien_Object $coordinates
     * 
     * @return Varien_Object
     */
    public function getAddress($coordinates)
    {
        $address            = new Varien_Object();
        $addressHelper      = $this->getAddressHelper();
        if (!$coordinates->hasLatitude() || !$coordinates->hasLongitude()) {
            return $address;
        }
        $coordinatesString  = $this->getCoordinatesString($coordinates);
        $_address           = $this->getSession()->getAddress($coordinatesString);
        if ($_address) {
            return $_address;
        }
        $httpClient         = $this->getHttpClient();
        $httpClient->setUri($this->getUri());
        $httpClient->setParameterGet('latlng', $coordinatesString);
        $httpClient->setParameterGet('sensor', 'false');
        try {
            $responce = $httpClient->request('GET');
            if ($responce) {
                $data = Zend_Json_Decoder::decode($responce->getBody(), Zend_Json::TYPE_OBJECT);
            }
        } catch (Exception $e) {
            $data = null;
        }
        if (
            $data && 
            isset($data->status) && (strtoupper(trim($data->status)) == 'OK') && 
            isset($data->results) && isset($data->results[0]) && 
            isset($data->results[0]->address_components)
        ) {
            $addressComponents = $data->results[0]->address_components;
            if (is_array($addressComponents) && count($addressComponents)) {
                $streetNumber   = null;
                $route          = null;
                $city           = null;
                $region         = null;
                $postalCode     = null;
                $country        = null;
                foreach ($addressComponents as $addressComponent) {
                    if (!isset($addressComponent->short_name) || !isset($addressComponent->types)) {
                        continue;
                    }
                    $shortName      = $addressComponent->short_name;
                    $types          = $addressComponent->types;
                    if (!is_array($types)) {
                        $types = array($types);
                    }
                    if (in_array('country', $types)) {
                        $country            = $shortName;
                    } else if (in_array('postal_code', $types)) {
                        $postalCode         = $shortName;
                    } else if (in_array('administrative_area_level_1', $types)) {
                        $region             = $shortName;
                    } else if (in_array('locality', $types)) {
                        $city               = $shortName;
                    } else if (in_array('route', $types)) {
                        $route              = $shortName;
                    } else if (in_array('street_number', $types)) {
                        $streetNumber       = $shortName;
                    }
                }
                $address->setCountryId($country);
                $address->setPostcode($postalCode);
                $address->setRegion($region);
                $address->setCity($city);
                $streetComponents       = array();
                if ($streetNumber) {
                    array_push($streetComponents, $streetNumber);
                }
                if ($route) {
                    array_push($streetComponents, $route);
                }
                $street = (count($streetComponents)) ? array(implode(' ', $streetComponents)) : null;
                $address->setStreet($street);
                $address = $addressHelper->cast($address);
            }
        }
        $this->getSession()->setAddress($coordinatesString, $address);
        return $address;
    }
}