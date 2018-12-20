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
 * Geo Ip config
 * 
 * @category   Innoexts
 * @package    Innoexts_CustomerLocator
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_GeoIp_Model_Config 
    extends Varien_Object 
{
    /**
     * Config path constants
     */
    const XML_PATH_GEOIP_OPTIONS_USE_PHP_EXTENSION  = 'innoexts_geoip/options/use_php_extension';
    const XML_PATH_GEOIP_OPTIONS_DATABASE_FILE      = 'innoexts_geoip/options/database_file';
    /**
     * Check if customer can change current address
     * 
     * @return boolean
     */
    public function usePhpExtension()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_GEOIP_OPTIONS_USE_PHP_EXTENSION);
    }
    /**
     * Get database file
     * 
     * @return array
     */
    public function getDatabaseFile()
    {
        return Mage::getStoreConfig(self::XML_PATH_GEOIP_OPTIONS_DATABASE_FILE);
    }
}
?>