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
 * Catalog rule compound discount amount renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
abstract class Innoexts_CurrencyPricing_Block_Adminhtml_Promo_Catalog_Edit_Tab_Actions_Compound_Renderer_Abstract 
    extends Innoexts_Core_Block_Adminhtml_Widget_Form_Element_Renderer_Abstract 
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->setTemplate(
            'innoexts/currencypricing/promo/catalog/edit/tab/actions/compound/renderer/abstract.phtml'
        );
    }
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
     * Get catalog rule
     * 
     * @return Mage_CatalogRule_Model_Rule
     */
    protected function getCatalogRule()
    {
        return Mage::registry('current_promo_catalog_rule');
    }
    /**
     * Get currency codes
     * 
     * @return array
     */
    public function getCurrencyCodes()
    {
        return $this->getCurrencyPricingHelper()
            ->getCoreHelper()
            ->getCurrencyHelper()
            ->getCodes();
    }
    /**
     * Sort values function
     *
     * @param mixed $a
     * @param mixed $b
     * 
     * @return int
     */
    protected function sortValues($a, $b)
    {
        if ($a['currency'] != $b['currency']) {
            return $a['currency'] < $b['currency'] ? -1 : 1;
        }
        return 0;
    }
    /**
     * Get default amount
     * 
     * @return float
     */
    abstract public function getDefaultAmount();
    /**
     * Get default currency amount
     * 
     * @param string $currencyCode
     * 
     * @return float
     */
    public function getDefaultCurrencyAmount($currencyCode)
    {
        return $this->getCurrencyPricingHelper()
            ->getCoreHelper()
            ->getCurrencyHelper()
            ->getBase()
            ->convert($this->getDefaultAmount(), $currencyCode);
    }
    /**
     * Get base currency code
     * 
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->getCurrencyPricingHelper()
            ->getCoreHelper()
            ->getCurrencyHelper()
            ->getBaseCode();
    }
    /**
     * Get values
     * 
     * @return array
     */
    public function getValues()
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $currencyHelper         = $helper->getCoreHelper()
            ->getCurrencyHelper();
        $values                 = array();
        $currencyCodes          = $this->getCurrencyCodes();
        if (count($currencyCodes)) {
            $element            = $this->getElement();
            $readonly           = $element->getReadonly();
            $data               = $element->getValue();
            $baseCurrency       = $currencyHelper->getBase();
            foreach ($currencyCodes as $currencyCode) {
                if (!$baseCurrency->getRate($currencyCode)) {
                    continue;
                }
                $value              = array('currency' => $currencyCode);
                $defaultAmount      = $productPriceHelper->escapedPrice(
                    $this->getDefaultCurrencyAmount($currencyCode)
                );
                if (isset($data[$currencyCode])) {
                    $value['amount']           = $productPriceHelper->escapedPrice($data[$currencyCode]);
                    $value['default_amount']   = $defaultAmount;
                    $value['use_default']      = 0;
                } else {
                    if (!is_null($defaultAmount)) {
                        $value['amount']       = $defaultAmount;
                    } else {
                        $value['amount']       = null;
                    }
                    $value['default_amount']   = $defaultAmount;
                    $value['use_default']      = 1;
                }
                $value['readonly']      = $readonly;
                $value['rates']          = array();
                foreach ($currencyCodes as $currencyCode2) {
                    $value['rates'][$currencyCode2] = $currencyHelper->getRate($currencyCode2, $currencyCode);
                }
                array_push($values, $value);
            }
        }
        usort($values, array($this, 'sortValues'));
        return $values;
    }
    /**
     * Get action element identifier
     * 
     * @return string
     */
    abstract public function getActionElementId();
    /**
     * Get amount element identifier
     * 
     * @return string
     */
    abstract public function getAmountElementId();
}