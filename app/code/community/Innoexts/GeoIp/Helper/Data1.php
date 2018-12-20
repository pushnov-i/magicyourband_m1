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
 * @package     Innoexts_GeoIp
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Geo ip helper
 * 
 * @category   Innoexts
 * @package    Innoexts_GeoIp
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_GeoIp_Helper_Data 
    extends Innoexts_Core_Helper_Abstract 
{
    /**
     * Geo Ip resource
     *
     * @var resource
     */
    protected $_geoIp;
    /**
     * Regions names
     *
     * @var array
     */
    protected $_regionsNames;
    /**
     * If PHP database is enabled
     * 
     * @var boolean 
     */
    protected $_isPhpDatabaseEnabled;
    /**
     * If database is enabled
     * 
     * @var boolean 
     */
    protected $_isDatabaseEnabled;
    /**
     * Constructor
     */
    public function __construct()
    {
        if ($this->isDatabaseEnabled()) {
            include_once $this->getVendorPath().'/geoip.inc';
            include_once $this->getVendorPath().'/geoipcity.inc';
            $geoIp = _geoip_open($this->getDatabaseFilePath(), _GEOIP_STANDARD);
            if ($geoIp) {
                $this->_geoIp = $geoIp;
            } else {
                $this->_geoIp = false;
            }
            include_once $this->getVendorPath().'/geoipregionvars.php';
            $this->_regionsNames = $_GEOIP_REGION_NAME;
        }
    }
    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->_geoIp) {
            _geoip_close($this->_geoIp);
        }
    }
    /**
     * Get string helper
     * 
     * @return Mage_Core_Helper_String
     */
    protected function getStringHelper()
    {
        return $this->getCoreHelper()->getStringHelper();
    }
    /**
     * Get config
     * 
     * @return Innoexts_GeoIp_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('innoexts_geoip/config');
    }
    /**
     * Check if PHP database is enabled
     * 
     * @return boolean
     */
    protected function isPhpDatabaseEnabled()
    {
        if (is_null($this->_isPhpDatabaseEnabled)) {
            $this->_isPhpDatabaseEnabled = (
                $this->getConfig()->usePhpExtension() && 
                extension_loaded('geoip') && 
                geoip_db_avail(GEOIP_CITY_EDITION_REV0)
            )  ? true : false;
        }
        return $this->_isPhpDatabaseEnabled;
    }
    /**
     * Check if database is enabled
     * 
     * @return boolean
     */
    protected function isDatabaseEnabled()
    {
        if (is_null($this->_isDatabaseEnabled)) {
            $this->_isDatabaseEnabled = (
                !$this->isPhpDatabaseEnabled() && 
                file_exists($this->getDatabaseFilePath())
            ) ? true : false;
        }
        return $this->_isDatabaseEnabled;
    }
    /**
     * Get database file path
     * 
     * @return string
     */
    protected function getDatabaseFilePath()
    {
        $path = trim($this->getConfig()->getDatabaseFile());
        if (substr($path, 0, 1) != '/') {
            $path = Mage::getBaseDir().DS.$path;
        }
        return $path;
    }
    /**
     * Get vendor path
     * 
     * @return string
     */
    protected function getVendorPath()
    {
        return Mage::getModuleDir(null, 'Innoexts_GeoIp').'/Helper'.'/Geoip';
    }
    /**
     * Get geo ip resource
     * 
     * @return resource
     */
    protected function getGeoIp()
    {
        return $this->_geoIp;
    }
    /**
     * Get regions names
     * 
     * @return array
     */
    protected function getRegionsNames()
    {
        return $this->_regionsNames;
    }
    /**
     * Get region name
     * 
     * @param string $countryCode
     * @param string $regionCode
     * 
     * @return string
     */
    protected function getRegionName($countryCode, $regionCode)
    {
        if ($this->isPhpDatabaseEnabled()) {
            return @geoip_region_name_by_code($countryCode, $regionCode);
        } else if ($this->isDatabaseEnabled()) {
            return (
                isset($this->_regionsNames[$countryCode]) && 
                isset($this->_regionsNames[$countryCode][$regionCode])
            ) ? $this->_regionsNames[$countryCode][$regionCode] : null;
        } else {
            return null;
        }
    }
    /**
     * Get record by ip adress
     * 
     * @param string $ip
     * 
     * @return stdClass
     */
    protected function getRecordByIp($ip)
    {
        $record = null;
        if ($this->isPhpDatabaseEnabled()) {
            $_record = @geoip_record_by_name($ip);
            if ($_record && is_array($_record)) {
                $record = $_record;
            }
        } else if ($this->isDatabaseEnabled()) {
            $_record = @_geoip_record_by_addr($this->getGeoIp(), $ip);
            if ($_record && is_object($_record)) {
                $record = get_object_vars($_record);
            }
        }
        if ($record) {
            $stringHelper = $this->getStringHelper();
            foreach ($record as $key => $value) {
                $record[$key] = $stringHelper->cleanString($value);
            }
            $record = new Varien_Object($record);
        }
        return $record;
    }
    /**
     * Get address by ip address
     *
     * @param string $ip
     * 
     * @return Varien_Object
     */
    public function getAddressByIp($ip)
    {
        $address = new Varien_Object();
        if (!$ip) {
            return $address;
        }
        $record = $this->getRecordByIp($ip);
        if (!$record) {
            return $address;
        }
        if ($record->getCountryCode()) {
            $address->setCountryId($record->getCountryCode());
        }
        if ($record->getCountryName()) {
            $address->setCountry($record->getCountryName());
        }
        if ($record->getCountryCode() && $record->getRegion()) {
            $address->setRegion($this->getRegionName($record->getCountryCode(), $record->getRegion()));
        }
        if ($record->getCity()) {
            $address->setCity($record->getCity());
        }
        if ($record->getPostalCode()) {
            $address->setPostcode($record->getPostalCode());
        }        
        return $address;
    }
    /**
     * Get coordinates by ip address
     *
     * @param string $ip
     * 
     * @return Varien_Object
     */
    public function getCoordinatesByIp($ip)
    {
        $coordinates = new Varien_Object();
        if (!$ip) {
            return $coordinates;
        }
        $record = $this->getRecordByIp($ip);
        if (!$record) {
            return $coordinates;
        }
        if ($record->getLatitude() && $record->getLongitude()) {
            $coordinates->setLatitude((float) $record->getLatitude());
            $coordinates->setLongitude((float) $record->getLongitude());
        }
        return $coordinates;
    }
}