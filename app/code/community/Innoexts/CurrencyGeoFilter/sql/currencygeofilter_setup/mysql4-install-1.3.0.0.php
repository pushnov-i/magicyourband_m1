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

$installer                                      = $this;

$connection                                     = $installer->getConnection();

$installer->startSetup();

$currencyCountryTable                           = $installer->getTable('currencygeofilter/currency_country');
$countryTable                                   = $installer->getTable('directory/country');

/**
 * Currency Country
 */
$installer->run("
CREATE TABLE `{$currencyCountryTable}` (
  `currency` char(3) NOT NULL DEFAULT '', 
  `country_id` varchar(2) NOT NULL DEFAULT '', 
  PRIMARY KEY  (`currency`, `country_id`), 
  KEY `IDX_CURRENCYGEOFILTER_CURRENCY_COUNTRY_CURRENCY` (`currency`), 
  KEY `IDX_CURRENCYGEOFILTER_CURRENCY_COUNTRY_ID` (`country_id`), 
  CONSTRAINT `FK_CURRENCYGEOFILTER_CURRENCY_COUNTRY_ID` 
    FOREIGN KEY (`country_id`) REFERENCES {$countryTable} (`country_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
