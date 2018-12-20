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
 * Rule condition
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Catalogrule_Rule_Condition_Product 
    extends Mage_CatalogRule_Model_Rule_Condition_Product 
{
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
     * Validate
     * 
     * @param Varien_Object $object
     * 
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        if ($this->getVersionHelper()->isGe1800()) {
            $attrCode = $this->getAttribute();
            if ('category_ids' == $attrCode) {
                return $this->validateAttribute($object->getAvailableInCategories());
            }
            if ('attribute_set_id' == $attrCode) {
                return $this->validateAttribute($object->getData($attrCode));
            }
            $oldAttrValue = $object->hasData($attrCode) ? $object->getData($attrCode) : null;
            $attrValue = $this->_getAttributeValue($object);
            if (!is_null($attrValue)) {
                $object->setData($attrCode, $attrValue);
            }
            $result = $this->_validateProduct($object);
            $this->_restoreOldAttrValue($object, $oldAttrValue);
            return (bool) $result;
        } else {
            return parent::validate($object);
        }
    }
}