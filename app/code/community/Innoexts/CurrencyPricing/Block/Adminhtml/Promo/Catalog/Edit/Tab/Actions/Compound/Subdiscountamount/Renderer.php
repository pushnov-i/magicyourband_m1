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
 * Catalog rule compound sub discount amount renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Promo_Catalog_Edit_Tab_Actions_Compound_Subdiscountamount_Renderer 
    extends Innoexts_CurrencyPricing_Block_Adminhtml_Promo_Catalog_Edit_Tab_Actions_Compound_Renderer_Abstract 
{
    /**
     * Get default amount
     * 
     * @return float
     */
    public function getDefaultAmount()
    {
        return $this->getCatalogRule()
            ->getSubDiscountAmount();
    }
    /**
     * Get action element identifier
     * 
     * @return string  
     */
    public function getActionElementId()
    {
        return $this->getElement()
            ->getForm()
            ->getElement('sub_simple_action')
            ->getHtmlId();
    }
    /**
     * Get amount element identifier
     * 
     * @return string  
     */
    public function getAmountElementId()
    {
        return $this->getElement()
            ->getForm()
            ->getElement('sub_discount_amount')
            ->getHtmlId();
    }
}