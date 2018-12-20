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
 * @package     Innoexts_CurrencyPricing
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Catalog rule collection resource
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Mysql4_Catalogrule_Rule_Collection 
    extends Mage_CatalogRule_Model_Mysql4_Rule_Collection 
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'catalogrule_rule_collection';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'collection';
    /**
     * Get currency pricing helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Data
     */
    protected function getCurrencyPricingHelper()
    {
        return Mage::helper('currencypricing');
    }
    /**
     * Get version helper
     * 
     * @return Innoexts_Core_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getCurrencyPricingHelper()->getVersionHelper();
    }
    /**
     * Constructor
     */
    protected function _construct()
    {
        if ($this->getVersionHelper()->isGe1700()) {
            $this->_associatedEntitiesMap['currency'] = array(
                'associations_table'    => 'catalogrule/currency', 
                'rule_id_field'         => 'rule_id', 
                'entity_id_field'       => 'currency', 
            );
        }
        parent::_construct();
    }
    /**
     * Provide support for store id filter
     *
     * @param string $field
     * @param mixed $condition
     *
     * @return self
     */
    public function addFieldToFilter($field, $condition = null)
    {
        parent::addFieldToFilter($field, $condition);
        return $this;
    }
}