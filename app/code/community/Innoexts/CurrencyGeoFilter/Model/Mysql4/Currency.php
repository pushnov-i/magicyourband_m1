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
 * Currency resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyGeoFilter
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyGeoFilter_Model_Mysql4_Currency 
    extends Mage_Core_Model_Mysql4_Abstract 
{
    /**
     * Currency country table
     *
     * @var string
     */
    protected $_currencyCountryTable;
    /**
     * Countries
     *
     * @var array
     */
    protected $_countries;
    /**
     * Currency country cache array
     *
     * @var array
     */
    protected static $_countryCache;
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('currencygeofilter/currency', 'currency_code');
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->_currencyCountryTable = $resource->getTableName('currencygeofilter/currency_country');
        parent::__construct();
    }
    /**
     * Saving countries
     *
     * @param array $countries
     * 
     * @return self
     */
    public function saveCountries($countries)
    {
        if (is_array($countries) && sizeof($countries) > 0) {
            $adapter                = $this->_getWriteAdapter();
            $table                  = $adapter->quoteIdentifier($this->_currencyCountryTable);
            $colCurrency            = $adapter->quoteIdentifier('currency');
            $colCountry             = $adapter->quoteIdentifier('country_id');
            $sql                    = 'REPLACE INTO '.$table.' ('.$colCurrency.', '.$colCountry.') VALUES ';
            $values                 = array();
            $deleteConditionPieces  = array();
            foreach ($countries as $currencyCode => $_countries) {
                foreach ($_countries as $countryCode) {
                    array_push($deleteConditionPieces, '(
                        ('.$colCurrency.' <> '.$adapter->quoteInto('?', $currencyCode).') AND 
                        ('.$colCountry.' <> '.$adapter->quoteInto('?', $countryCode).')
                    )');
                    $values[]       = $adapter->quoteInto('(?)', array($currencyCode, $countryCode));
                }
            }
            $deleteCondition        = implode(' OR ', $deleteConditionPieces);
            $sql                    .= implode(',', $values);
            $adapter->delete($this->_currencyCountryTable, $deleteCondition);
            $adapter->query($sql);
        } else {
            Mage::throwException(Mage::helper('currencygeofilter')->__('Invalid countries received.'));
        }
        return $this;
    }
    /**
     * Get currency countries
     *
     * @param string|array $currency
     * 
     * @return array
     */
    public function getCurrencyCountries($currency)
    {
        $countries= array();
        if (is_array($currency)) {
            foreach($currency as $code) {
                $countries[$code] = $this->_getCountriesByCode($code);
            }
        } else {
            $countries = $this->_getCountriesByCode($currency);
        }
        return $countries;
    }
    /**
     * Get country currencies
     *
     * @param string $countryId
     * 
     * @return array
     */
    public function getCountryCurrencies($countryId)
    {
        $currencies     = array();
        $adapter        = $this->_getReadAdapter();
        $select         = $adapter->select()
            ->from($this->getTable('currencygeofilter/currency_country'), array('currency'))
            ->where($adapter->quoteInto('country_id = ?', $countryId));
        $data           = $adapter->fetchAll($select);
        foreach($data as $currencyCountry) {
            $currencies[] = $currencyCountry['currency'];
        }
        unset($data);
        return $currencies;
    }
    /**
     * Retrieve countries by currency code
     *
     * @param string $code
     * @return array
     */
    protected function _getCountriesByCode($code)
    {
        $adapter        = $this->_getReadAdapter();
        $select         = $adapter->select()
            ->from($this->getTable('currencygeofilter/currency_country'), array('country_id'))
            ->where($adapter->quoteInto('currency = ?', $code));
        $data           = $adapter->fetchAll($select);
        $result         = array();
        foreach($data as $currencyCountry) {
            $result[]       = $currencyCountry['country_id'];
        }
        unset($data);
        return $result;
    }
}