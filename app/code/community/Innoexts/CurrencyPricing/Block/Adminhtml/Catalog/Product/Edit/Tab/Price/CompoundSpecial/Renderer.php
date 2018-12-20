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
 * Compound special price renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_CompoundSpecial_Renderer 
    extends Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_Renderer_Abstract 
{
    /**
     * Get default price
     * 
     * @param mixed $currencyCode
     * 
     * @return float
     */
    protected function getDefaultPrice($currencyCode)
    {
        return $this->getProductPriceHelper()->getDefaultCompoundSpecialPrice(
            $this->getProduct(), 
            array(
                'website_id' => $this->getStore()->getWebsiteId(), 
                'currency' => $currencyCode, 
            )
        );
    }   
}