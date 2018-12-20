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
 * Tax calculation
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Tax_Sales_Total_Quote_Subtotal 
    extends Mage_Tax_Model_Sales_Total_Quote_Subtotal 
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
     * 
     * Calculate item price and row total including/excluding tax based on unit price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     *
     * @return self
     */
    protected function _unitBaseCalculation($item, $request)
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $calculator             = $this->_calculator;
        $request->setProductClassId($item->getProduct()->getTaxClassId());
        $rate                   = $calculator->getRate($request);
        $qty                    = $item->getTotalQty();
        
        if ($helper->getVersionHelper()->isGe1800()) {
            $price                  = $taxPrice             = $calculator->round($item->getCalculationPriceOriginal());
        } else {
            if ($helper->getVersionHelper()->isGe1600()) {
                $price                  = $taxPrice             = $item->getCalculationPriceOriginal();
            } else {
                $price                  = $taxPrice             = $item->getCalculationPrice();
            }
        }
        
        if ($helper->getVersionHelper()->isGe1600()) {
            $basePrice              = $productPriceHelper->round($item->getBaseCalculationPriceOriginal());
        } else {
            $basePrice              = $productPriceHelper->round($item->getBaseCalculationPrice());
        }
        
        if ($helper->getVersionHelper()->isGe1800()) {
            $baseTaxPrice           = $calculator->round($item->getBaseCalculationPriceOriginal());
        } else {
            if ($helper->getVersionHelper()->isGe1600()) {
                $baseTaxPrice           = $item->getBaseCalculationPriceOriginal();
            } else {
                $baseTaxPrice           = $item->getBaseCalculationPrice();
            }
        }
        
        if ($helper->getVersionHelper()->isGe1810()) {
            $subtotal               = $taxSubtotal          = $calculator->round($item->getRowTotal());
            $baseSubtotal           = $baseTaxSubtotal      = $calculator->round($item->getBaseRowTotal());
        } else {
            $subtotal               = $taxSubtotal          = $item->getRowTotal();
            $baseSubtotal           = $baseTaxSubtotal      = $item->getBaseRowTotal();
        }
        $taxOnOrigPrice         = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origPrice              = $item->getOriginalPrice();
            $baseOrigPrice          = $item->getBaseOriginalPrice();
        }
        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_sameRateAsStore($request)) {
                
                if ($helper->getVersionHelper()->isGe1810()) {
                    if ($taxOnOrigPrice) {
                        $taxable                = $origPrice;
                        $baseTaxable            = $baseOrigPrice;
                    } else {
                        $taxable                = $price;
                        $baseTaxable            = $basePrice;
                    }
                    $tax                    = $calculator->calcTaxAmount($taxable, $rate, true);
                    $baseTax                = $calculator->calcTaxAmount($baseTaxable, $rate, true);
                    $taxPrice               = $price;
                    $baseTaxPrice           = $basePrice;
                    $taxSubtotal            = $subtotal;
                    $baseTaxSubtotal        = $baseSubtotal;
                    $price                  = $price - $tax;
                    $basePrice              = $basePrice - $baseTax;
                    $subtotal               = $price * $qty;
                    $baseSubtotal           = $basePrice * $qty;
                    $isPriceInclTax         = true;
                    $item->setRowTax($tax * $qty);
                    $item->setBaseRowTax($baseTax * $qty);
                } else {
                    $tax                    = $calculator->calcTaxAmount($price, $rate, true);
                    $baseTax                = $calculator->calcTaxAmount($basePrice, $rate, true);
                    $taxPrice               = $price;
                    $baseTaxPrice           = $basePrice;
                    $taxSubtotal            = $subtotal;
                    $baseTaxSubtotal        = $baseSubtotal;
                    $price                  = $price - $tax;
                    $basePrice              = $basePrice - $baseTax;
                    $subtotal               = $price * $qty;
                    $baseSubtotal           = $basePrice * $qty;
                    if ($taxOnOrigPrice) {
                        $taxable                = $origPrice;
                        $baseTaxable            = $baseOrigPrice;
                    } else {
                        $taxable                = $taxPrice;
                        $baseTaxable            = $baseTaxPrice;
                    }
                    $isPriceInclTax         = true;
                }
                
            } else {
                $storeRate              = $calculator->getStoreRate($request, $this->_store);
                
                if ($helper->getVersionHelper()->isGe1810()) {
                    if ($taxOnOrigPrice) {
                        $taxPrice               = $price;
                        $baseTaxPrice           = $basePrice;
                        $taxable                = $this->_calculatePriceInclTax($origPrice, $storeRate, $rate);
                        $baseTaxable            = $this->_calculatePriceInclTax($baseOrigPrice, $storeRate, $rate);
                    } else {
                        $taxPrice               = $this->_calculatePriceInclTax($price, $storeRate, $rate);
                        $baseTaxPrice           = $this->_calculatePriceInclTax($basePrice, $storeRate, $rate);
                        $taxable                = $taxPrice;
                        $baseTaxable            = $baseTaxPrice;
                    }
                    $tax                    = $calculator->calcTaxAmount($taxable, $rate, true, true);
                    $baseTax                = $this->_calculator->calcTaxAmount($baseTaxable, $rate, true, true);
                    $price                  = $taxPrice - $tax;
                    $basePrice              = $baseTaxPrice - $baseTax;
                    $taxSubtotal            = $taxPrice * $qty;
                    $baseTaxSubtotal        = $baseTaxPrice * $qty;
                    $subtotal               = $price * $qty;
                    $baseSubtotal           = $basePrice * $qty;
                    $isPriceInclTax         = true;
                    $item->setRowTax($tax * $qty);
                    $item->setBaseRowTax($baseTax * $qty);
                } else {
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $storeTax               = $calculator->calcTaxAmount($price, $storeRate, true, false);
                        $baseStoreTax           = $calculator->calcTaxAmount($basePrice, $storeRate, true, false);
                        $price                  = $calculator->round($price - $storeTax);
                        $basePrice              = $calculator->round($basePrice - $baseStoreTax);
                    } else {
                        $storeTax               = $calculator->calcTaxAmount($price, $storeRate, true);
                        $baseStoreTax           = $calculator->calcTaxAmount($basePrice, $storeRate, true);
                        $price                  = $price - $storeTax;
                        $basePrice              = $basePrice - $baseStoreTax;
                    }
                    
                    $subtotal               = $price * $qty;
                    $baseSubtotal           = $basePrice * $qty;
                    $tax                    = $calculator->calcTaxAmount($price, $rate, false);
                    $baseTax                = $calculator->calcTaxAmount($basePrice, $rate, false);
                    $taxPrice               = $price + $tax;
                    $baseTaxPrice           = $basePrice + $baseTax;
                    $taxSubtotal            = $taxPrice * $qty;
                    $baseTaxSubtotal        = $baseTaxPrice * $qty;
                    if ($taxOnOrigPrice) {
                        if ($helper->getVersionHelper()->isGe1800()) {
                            $taxable                = $calculator->round($origPrice - $storeTax + $tax);
                            $baseTaxable            = $calculator->round($baseOrigPrice - $baseStoreTax + $baseTax);
                        } else {
                            $taxable                = $origPrice - $storeTax;
                            $baseTaxable            = $baseOrigPrice - $baseStoreTax;
                        }
                    } else {
                        if ($helper->getVersionHelper()->isGe1800()) {
                            $taxable                = $taxPrice;
                            $baseTaxable            = $baseTaxPrice;
                        } else {
                            $taxable                = $price;
                            $baseTaxable            = $basePrice;
                        }
                    }
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $isPriceInclTax         = true;
                    } else {
                        $isPriceInclTax         = false;
                    }
                }
                
            }
        } else {
            
            if ($helper->getVersionHelper()->isGe1810()) {
                if ($taxOnOrigPrice) {
                    $taxable                = $origPrice;
                    $baseTaxable            = $baseOrigPrice;
                } else {
                    $taxable                = $price;
                    $baseTaxable            = $basePrice;
                }
                $appliedRates           = $calculator->getAppliedRates($request);
                $taxes                  = array();
                $baseTaxes              = array();
                foreach ($appliedRates as $appliedRate) {
                    $taxRate                = $appliedRate['percent'];
                    $taxes[]                = $calculator->calcTaxAmount($taxable, $taxRate, false);
                    $baseTaxes[]            = $calculator->calcTaxAmount($baseTaxable, $taxRate, false);
                }
                $tax                    = array_sum($taxes);
                $baseTax                = array_sum($baseTaxes);
                $taxPrice               = $price + $tax;
                $baseTaxPrice           = $basePrice + $baseTax;
                $taxSubtotal            = $taxPrice * $qty;
                $baseTaxSubtotal        = $baseTaxPrice * $qty;
                $isPriceInclTax         = false;
            } else {
                $tax                    = $calculator->calcTaxAmount($price, $rate, false);
                $baseTax                = $calculator->calcTaxAmount($basePrice, $rate, false);
                $taxPrice               = $price + $tax;
                $baseTaxPrice           = $basePrice + $baseTax;
                $taxSubtotal            = $taxPrice * $qty;
                $baseTaxSubtotal        = $baseTaxPrice * $qty;
                if ($taxOnOrigPrice) {
                    $taxable                = $origPrice;
                    $baseTaxable            = $baseOrigPrice;
                } else {
                    $taxable                = $price;
                    $baseTaxable            = $basePrice;
                }
                $isPriceInclTax = false;
            }
            
        }
        if ($item->hasCustomPrice()) {
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        } else {
            
            if (!$helper->getVersionHelper()->isGe1700()) {
                $item->setConvertedPrice($price);
            }
            
        }
        
        if ($helper->getVersionHelper()->isGe1700()) {
            $item->setPrice($basePrice);
        } else {
            $item->setPrice($price);
        }
        
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxPrice);
            $item->setBaseDiscountCalculationPrice($baseTaxPrice);
        }
        return $this;
    }
    /**
     * Calculate item price and row total including/excluding tax based on row total price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     *
     * @return self
     */
    protected function _rowBaseCalculation($item, $request)
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $calculator             = $this->_calculator;
        $request->setProductClassId($item->getProduct()->getTaxClassId());
        $rate                   = $calculator->getRate($request);
        $qty                    = $item->getTotalQty();
        
        if ($helper->getVersionHelper()->isGe1800()) {
            $price                  = $taxPrice             = $calculator->round($item->getCalculationPriceOriginal());
        } else {
            if ($helper->getVersionHelper()->isGe1600()) {
                $price                  = $taxPrice             = $item->getCalculationPriceOriginal();
            } else {
                $price                  = $taxPrice             = $item->getCalculationPrice();
            }
        }
        
        if ($helper->getVersionHelper()->isGe1600()) {
            $basePrice              = $productPriceHelper->round($item->getBaseCalculationPriceOriginal());
        } else {
            $basePrice              = $productPriceHelper->round($item->getBaseCalculationPrice());
        }
        
        if ($helper->getVersionHelper()->isGe1800()) {
            $baseTaxPrice           = $calculator->round($item->getBaseCalculationPriceOriginal());
        } else {
            if ($helper->getVersionHelper()->isGe1600()) {
                $baseTaxPrice           = $item->getBaseCalculationPriceOriginal();
            } else {
                $baseTaxPrice           = $item->getBaseCalculationPrice();
            }
        }
        
        if ($helper->getVersionHelper()->isGe1810()) {
            $subtotal               = $taxSubtotal          = $calculator->round($item->getRowTotal());
            $baseSubtotal           = $baseTaxSubtotal      = $calculator->round($item->getBaseRowTotal());
        } else {
            $subtotal               = $taxSubtotal          = $item->getRowTotal();
            $baseSubtotal           = $baseTaxSubtotal      = $item->getBaseRowTotal();
        }
        $taxOnOrigPrice         = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origSubtotal           = $item->getOriginalPrice() * $qty;
            $baseOrigSubtotal       = $item->getBaseOriginalPrice() * $qty;
        }
        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_sameRateAsStore($request)) {
                
                if ($helper->getVersionHelper()->isGe1810()) {
                    if ($taxOnOrigPrice) {
                        $taxable                = $origSubtotal;
                        $baseTaxable            = $baseOrigSubtotal;
                    } else {
                        $taxable                = $taxSubtotal;
                        $baseTaxable            = $baseTaxSubtotal;
                    }
                    $rowTax                 = $calculator->calcTaxAmount($taxable, $rate, true, true);
                    $baseRowTax             = $calculator->calcTaxAmount($baseTaxable, $rate, true, true);
                    $taxPrice               = $price;
                    $baseTaxPrice           = $basePrice;
                    $taxSubtotal            = $subtotal;
                    $baseTaxSubtotal        = $baseSubtotal;
                    $subtotal               = $calculator->round($subtotal - $rowTax);
                    $baseSubtotal           = $calculator->round($baseSubtotal - $baseRowTax);
                    $price                  = $calculator->round($subtotal / $qty);
                    $basePrice              = $calculator->round($baseSubtotal / $qty);
                    $isPriceInclTax         = true;
                    $item->setRowTax($rowTax);
                    $item->setBaseRowTax($baseRowTax);
                } else {
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $rowTax                 = $calculator->calcTaxAmount($subtotal, $rate, true, true);
                        $baseRowTax             = $calculator->calcTaxAmount($baseSubtotal, $rate, true, true);
                    } else {
                        $rowTax                 = $calculator->calcTaxAmount($subtotal, $rate, true, false);
                        $baseRowTax             = $calculator->calcTaxAmount($baseSubtotal, $rate, true, false);
                    }
                    
                    $taxPrice               = $price;
                    $baseTaxPrice           = $basePrice;
                    $taxSubtotal            = $subtotal;
                    $baseTaxSubtotal        = $baseSubtotal;
                    $subtotal               = $calculator->round($subtotal - $rowTax);
                    $baseSubtotal           = $calculator->round($baseSubtotal - $baseRowTax);
                    $price                  = $calculator->round($subtotal/$qty);
                    $basePrice              = $calculator->round($baseSubtotal/$qty);
                    if ($taxOnOrigPrice) {
                        $taxable                = $origSubtotal;
                        $baseTaxable            = $baseOrigSubtotal;
                    } else {
                        $taxable                = $taxSubtotal;
                        $baseTaxable            = $baseTaxSubtotal;
                    }
                    $isPriceInclTax = true;
                }
                
            } else {
                $storeRate              = $calculator->getStoreRate($request, $this->_store);
                
                if ($helper->getVersionHelper()->isGe1810()) {
                    if ($taxOnOrigPrice) {
                        $taxPrice               = $price;
                        $baseTaxPrice           = $basePrice;
                        $taxable                = $this->_calculatePriceInclTax($item->getOriginalPrice(), $storeRate, $rate);
                        $baseTaxable            = $this->_calculatePriceInclTax($item->getBaseOriginalPrice(), $storeRate, $rate);
                    } else {
                        $taxPrice               = $this->_calculatePriceInclTax($price, $storeRate, $rate);
                        $baseTaxPrice           = $this->_calculatePriceInclTax($basePrice, $storeRate, $rate);
                        $taxable                = $taxPrice;
                        $baseTaxable            = $baseTaxPrice;
                    }
                    $tax                    = $calculator->calcTaxAmount($taxable, $rate, true, true);
                    $baseTax                = $calculator->calcTaxAmount($baseTaxable, $rate, true, true);
                    $price                  = $taxPrice - $tax;
                    $basePrice              = $baseTaxPrice - $baseTax;
                    $taxable                *= $qty;
                    $baseTaxable            *= $qty;
                    $taxSubtotal            = $taxPrice * $qty;
                    $baseTaxSubtotal        = $baseTaxPrice * $qty;
                    $rowTax                 = $calculator->calcTaxAmount($taxable, $rate, true, true);
                    $baseRowTax             = $calculator->calcTaxAmount($baseTaxable, $rate, true, true);
                    $subtotal               = $taxSubtotal - $rowTax;
                    $baseSubtotal           = $baseTaxSubtotal - $baseRowTax;
                    $isPriceInclTax         = true;
                    $item->setRowTax($rowTax);
                    $item->setBaseRowTax($baseRowTax);
                } else {
                    $storeTax               = $calculator->calcTaxAmount($subtotal, $storeRate, true, false);
                    $baseStoreTax           = $calculator->calcTaxAmount($baseSubtotal, $storeRate, true, false);
                    $subtotal               = $calculator->round($subtotal - $storeTax);
                    $baseSubtotal           = $calculator->round($baseSubtotal - $baseStoreTax);
                    $price                  = $calculator->round($subtotal/$qty);
                    $basePrice              = $calculator->round($baseSubtotal/$qty);
                    $rowTax                 = $calculator->calcTaxAmount($subtotal, $rate, false, false);
                    $baseRowTax             = $calculator->calcTaxAmount($baseSubtotal, $rate, false, false);
                    $taxSubtotal            = $subtotal + $rowTax;
                    $baseTaxSubtotal        = $baseSubtotal + $baseRowTax;
                    $taxPrice               = $calculator->round($taxSubtotal/$qty);
                    $baseTaxPrice           = $calculator->round($baseTaxSubtotal/$qty);
                    if ($taxOnOrigPrice) {
                        
                        if ($helper->getVersionHelper()->isGe1800()) {
                            $taxable                = $calculator->round($origSubtotal - $storeTax + $rowTax);
                            $baseTaxable            = $calculator->round($baseOrigSubtotal - $baseStoreTax + $baseRowTax);
                        } else {
                            $taxable                = $calculator->round($origSubtotal - $storeTax);
                            $baseTaxable            = $calculator->round($baseOrigSubtotal - $baseStoreTax);
                        }
                        
                    } else {
                        
                        if ($helper->getVersionHelper()->isGe1800()) {
                            $taxable                = $taxSubtotal;
                            $baseTaxable            = $baseTaxSubtotal;
                        } else {
                            $taxable                = $subtotal;
                            $baseTaxable            = $baseSubtotal;
                        }
                        
                    }
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $isPriceInclTax         = true;
                    } else {
                        $isPriceInclTax         = false;
                    }
                    
                }
                
            }
        } else {
            
            if ($helper->getVersionHelper()->isGe1810()) {
                if ($taxOnOrigPrice) {
                    $taxable                = $origSubtotal;
                    $baseTaxable            = $baseOrigSubtotal;
                } else {
                    $taxable                = $subtotal;
                    $baseTaxable            = $baseSubtotal;
                }
                $appliedRates           = $calculator->getAppliedRates($request);
                $rowTaxes               = array();
                $baseRowTaxes           = array();
                foreach ($appliedRates as $appliedRate) {
                    $taxRate                = $appliedRate['percent'];
                    $rowTaxes[]             = $calculator->calcTaxAmount($taxable, $taxRate, false, true);
                    $baseRowTaxes[]         = $calculator->calcTaxAmount($baseTaxable, $taxRate, false, true);
                }
                $rowTax                 = array_sum($rowTaxes);
                $baseRowTax             = array_sum($baseRowTaxes);
                $taxSubtotal            = $subtotal + $rowTax;
                $baseTaxSubtotal        = $baseSubtotal + $baseRowTax;
                $taxPrice               = $calculator->round($taxSubtotal/$qty);
                $baseTaxPrice           = $calculator->round($baseTaxSubtotal/$qty);
                $isPriceInclTax         = false;
            } else {
                $rowTax                 = $calculator->calcTaxAmount($subtotal, $rate, false, false);
                $baseRowTax             = $calculator->calcTaxAmount($baseSubtotal, $rate, false, false);
                $taxSubtotal            = $subtotal + $rowTax;
                $baseTaxSubtotal        = $baseSubtotal + $baseRowTax;
                $taxPrice               = $calculator->round($taxSubtotal/$qty);
                $baseTaxPrice           = $calculator->round($baseTaxSubtotal/$qty);
                if ($taxOnOrigPrice) {
                    $taxable                = $origSubtotal;
                    $baseTaxable            = $baseOrigSubtotal;
                } else {
                    $taxable                = $subtotal;
                    $baseTaxable            = $baseSubtotal;
                }
                $isPriceInclTax = false;
            }
            
        }
        if ($item->hasCustomPrice()) {
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        } else {
            
            if (!$helper->getVersionHelper()->isGe1700()) {
                $item->setConvertedPrice($price);
            }
            
        }
        
        if ($helper->getVersionHelper()->isGe1700()) {
            $item->setPrice($basePrice);
        } else {
            $item->setPrice($price);
        }
        
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxSubtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseTaxSubtotal / $qty);
        } elseif ($isPriceInclTax) {
            $item->setDiscountCalculationPrice($subtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseSubtotal / $qty);
        }
        return $this;
    }
    /**
     * Calculate item price and row total including/excluding tax based on total price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     *
     * @return self
     */
    protected function _totalBaseCalculation($item, $request)
    {
        $helper                 = $this->getCurrencyPricingHelper();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $calculator             = $this->_calculator;
        $request->setProductClassId($item->getProduct()->getTaxClassId());
        $rate                   = $calculator->getRate($request);
        $qty                    = $item->getTotalQty();
        
        if ($helper->getVersionHelper()->isGe1800()) {
            $price                  = $taxPrice             = $calculator->round($item->getCalculationPriceOriginal());
        } else {
            if ($helper->getVersionHelper()->isGe1600()) {
                $price                  = $taxPrice             = $item->getCalculationPriceOriginal();
            } else {
                $price                  = $taxPrice             = $item->getCalculationPrice();
            }
        }
        
        if ($helper->getVersionHelper()->isGe1600()) {
            $basePrice              = $productPriceHelper->round($item->getBaseCalculationPriceOriginal());
        } else {
            $basePrice              = $productPriceHelper->round($item->getBaseCalculationPrice());
        }
        
        if ($helper->getVersionHelper()->isGe1800()) {
            $baseTaxPrice           = $calculator->round($item->getBaseCalculationPriceOriginal());
        } else {
            if ($helper->getVersionHelper()->isGe1600()) {
                $baseTaxPrice           = $item->getBaseCalculationPriceOriginal();
            } else {
                $baseTaxPrice           = $item->getBaseCalculationPrice();
            }
        }
        
        if ($helper->getVersionHelper()->isGe1810()) {
            $subtotal               = $taxSubtotal          = $calculator->round($item->getRowTotal());
            $baseSubtotal           = $baseTaxSubtotal      = $calculator->round($item->getBaseRowTotal());
        } else {
            $subtotal               = $taxSubtotal          = $item->getRowTotal();
            $baseSubtotal           = $baseTaxSubtotal      = $item->getBaseRowTotal();
        }
        
        if ($helper->getVersionHelper()->isGe1800() && !$helper->getVersionHelper()->isGe1810()) {
            $subtotalExact          = $baseSubtotalExact    = 0;
        }
        
        $taxOnOrigPrice         = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origSubtotal           = $item->getOriginalPrice() * $qty;
            $baseOrigSubtotal       = $item->getBaseOriginalPrice() * $qty;
        }
        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_sameRateAsStore($request)) {
                
                if ($helper->getVersionHelper()->isGe1810()) {
                    if ($taxOnOrigPrice) {
                        $taxable                = $origSubtotal;
                        $baseTaxable            = $baseOrigSubtotal;
                    } else {
                        $taxable                = $subtotal;
                        $baseTaxable            = $baseSubtotal;
                    }
                    $rowTaxExact            = $calculator->calcTaxAmount($taxable, $rate, true, false);
                    $rowTax                 = $this->_deltaRound($rowTaxExact, $rate, true);
                    $baseRowTaxExact        = $calculator->calcTaxAmount($baseTaxable, $rate, true, false);
                    $baseRowTax             = $this->_deltaRound($baseRowTaxExact, $rate, true, 'base');
                    $taxPrice               = $price;
                    $baseTaxPrice           = $basePrice;
                    $taxSubtotal            = $subtotal;
                    $baseTaxSubtotal        = $baseSubtotal;
                    $subtotal               = $subtotal - $rowTax;
                    $baseSubtotal           = $baseSubtotal - $baseRowTax;
                    $price                  = $calculator->round($subtotal / $qty);
                    $basePrice              = $calculator->round($baseSubtotal / $qty);
                    $isPriceInclTax         = true;
                    $item->setRowTax($rowTax);
                    $item->setBaseRowTax($baseRowTax);
                } else {
                    
                    if ($helper->getVersionHelper()->isGe1510()) {
                        if ($taxOnOrigPrice) {
                            if ($helper->getVersionHelper()->isGe1800()) {
                                $rowTaxExact            = $calculator->calcTaxAmount($origSubtotal, $rate, true, false);
                                $rowTax                 = $this->_deltaRound($rowTaxExact, $rate, true);
                                $baseRowTaxExact        = $calculator->calcTaxAmount($baseOrigSubtotal, $rate, true, false);
                                $baseRowTax             = $this->_deltaRound($baseRowTaxExact, $rate, true, 'base');
                            } else {
                                $rowTax                 = $this->_deltaRound(
                                    $calculator->calcTaxAmount($origSubtotal, $rate, true, false), 
                                    $rate, 
                                    true
                                );
                                $baseRowTax             = $this->_deltaRound(
                                    $calculator->calcTaxAmount($baseOrigSubtotal, $rate, true, false), 
                                    $rate, 
                                    true, 
                                    'base'
                                );
                            }
                            $taxable                = $origSubtotal;
                            $baseTaxable            = $baseOrigSubtotal;
                        } else {
                            if ($helper->getVersionHelper()->isGe1800()) {
                                $rowTaxExact            = $calculator->calcTaxAmount($subtotal, $rate, true, false);
                                $rowTax                 = $this->_deltaRound($rowTaxExact, $rate, true);
                                $baseRowTaxExact        = $calculator->calcTaxAmount($baseSubtotal, $rate, true, false);
                                $baseRowTax             = $this->_deltaRound($baseRowTaxExact, $rate, true, 'base');
                            } else {
                                $rowTax                 = $this->_deltaRound(
                                    $calculator->calcTaxAmount($subtotal, $rate, true, false), 
                                    $rate, 
                                    true
                                );
                                $baseRowTax             = $this->_deltaRound(
                                    $calculator->calcTaxAmount($baseSubtotal, $rate, true, false), 
                                    $rate, 
                                    true, 
                                    'base'
                                );
                            }
                            $taxable                = $subtotal;
                            $baseTaxable            = $baseSubtotal;
                        }
                    } else {
                        $rowTax                 = $this->_deltaRound(
                            $calculator->calcTaxAmount($subtotal, $rate, true, false), 
                            $rate, 
                            true
                        );
                        $baseRowTax             = $this->_deltaRound(
                            $calculator->calcTaxAmount($baseSubtotal, $rate, true, false), 
                            $rate, 
                            true, 
                            'base'
                        );
                    }
                    
                    $taxPrice               = $price;
                    $baseTaxPrice           = $basePrice;
                    $taxSubtotal            = $subtotal;
                    $baseTaxSubtotal        = $baseSubtotal;
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $subtotalExact          = $subtotal - $rowTaxExact;
                    }
                    
                    $subtotal               = $subtotal - $rowTax;
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $baseSubtotalExact      = $baseSubtotal - $baseRowTaxExact;
                    }
                    
                    $baseSubtotal           = $baseSubtotal - $baseRowTax;
                    $price                  = $calculator->round($subtotal/$qty);
                    $basePrice              = $calculator->round($baseSubtotal/$qty);
                    
                    if (!$helper->getVersionHelper()->isGe1510()) {
                        if ($taxOnOrigPrice) {
                            $taxable            = $origSubtotal;
                            $baseTaxable        = $baseOrigSubtotal;
                        } else {
                            $taxable            = $taxSubtotal;
                            $baseTaxable        = $baseTaxSubtotal;
                        }
                    }
                    
                    $isPriceInclTax         = true;
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $item->setRowTotalExact($subtotalExact);
                        $item->setBaseRowTotalExact($baseSubtotalExact);
                    }
                    
                }
                
            } else {
                $storeRate              = $calculator->getStoreRate($request, $this->_store);
                
                if ($helper->getVersionHelper()->isGe1810()) {
                    if ($taxOnOrigPrice) {
                        $taxPrice               = $price;
                        $baseTaxPrice           = $basePrice;
                        $taxable                = $this->_calculatePriceInclTax($item->getOriginalPrice(), $storeRate, $rate);
                        $baseTaxable            = $this->_calculatePriceInclTax($item->getBaseOriginalPrice(), $storeRate, $rate);
                    } else {
                        $taxPrice               = $this->_calculatePriceInclTax($price, $storeRate, $rate);
                        $baseTaxPrice           = $this->_calculatePriceInclTax($basePrice, $storeRate, $rate);
                        $taxable                = $taxPrice;
                        $baseTaxable            = $baseTaxPrice;
                    }
                    $tax                    = $calculator->calcTaxAmount($taxable, $rate, true, true);
                    $baseTax                = $calculator->calcTaxAmount($baseTaxable, $rate, true, true);
                    $price                  = $taxPrice - $tax;
                    $basePrice              = $baseTaxPrice - $baseTax;
                    $taxable                *= $qty;
                    $baseTaxable            *= $qty;
                    $taxSubtotal            = $taxPrice * $qty;
                    $baseTaxSubtotal        = $baseTaxPrice * $qty;
                    $rowTax                 = $this->_deltaRound(
                        $calculator->calcTaxAmount($taxable, $rate, true, false), 
                        $rate, 
                        true
                    );
                    $baseRowTax             = $this->_deltaRound(
                        $calculator->calcTaxAmount($baseTaxable, $rate, true, false), 
                        $rate, 
                        true, 
                        'base'
                    );
                    $subtotal               = $taxSubtotal - $rowTax;
                    $baseSubtotal           = $baseTaxSubtotal - $baseRowTax;
                    $isPriceInclTax         = true;
                    $item->setRowTax($rowTax);
                    $item->setBaseRowTax($baseRowTax);
                } else {
                    
                    if ($helper->getVersionHelper()->isGe1510()) {
                        if ($taxOnOrigPrice) {
                            $storeTax               = $calculator->calcTaxAmount($origSubtotal, $storeRate, true, false);
                            $baseStoreTax           = $calculator->calcTaxAmount($baseOrigSubtotal, $storeRate, true, false);
                        } else {
                            $storeTax               = $calculator->calcTaxAmount($subtotal, $storeRate, true, false);
                            $baseStoreTax           = $calculator->calcTaxAmount($baseSubtotal, $storeRate, true, false);
                        }
                    } else {
                        $storeTax               = $calculator->calcTaxAmount($subtotal, $storeRate, true, false);
                        $baseStoreTax           = $calculator->calcTaxAmount($baseSubtotal, $storeRate, true, false);
                    }
                    
                    $subtotal               = $calculator->round($subtotal - $storeTax);
                    $baseSubtotal           = $calculator->round($baseSubtotal - $baseStoreTax);
                    $price                  = $calculator->round($subtotal/$qty);
                    $basePrice              = $calculator->round($baseSubtotal/$qty);
                    $rowTax                 = $this->_deltaRound(
                        $calculator->calcTaxAmount($subtotal, $rate, false, false), 
                        $rate, 
                        true
                    );
                    $baseRowTax             = $this->_deltaRound(
                        $calculator->calcTaxAmount($baseSubtotal, $rate, false, false), 
                        $rate, 
                        true, 
                        'base'
                    );
                    $taxSubtotal            = $subtotal + $rowTax;
                    $baseTaxSubtotal        = $baseSubtotal + $baseRowTax;
                    $taxPrice               = $calculator->round($taxSubtotal/$qty);
                    $baseTaxPrice           = $calculator->round($baseTaxSubtotal/$qty);
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $taxable                = $taxSubtotal;
                        $baseTaxable            = $baseTaxSubtotal;
                    } else {
                        if ($helper->getVersionHelper()->isGe1510()) {
                            $taxable                = $subtotal;
                            $baseTaxable            = $baseSubtotal;
                        } else {
                            if ($taxOnOrigPrice) {
                                $taxable                = $calculator->round($origSubtotal - $storeTax);
                                $baseTaxable            = $calculator->round($baseOrigSubtotal - $baseStoreTax);
                            } else {
                                $taxable                = $subtotal;
                                $baseTaxable            = $baseSubtotal;
                            }
                        }
                    }
                    
                    if ($helper->getVersionHelper()->isGe1800()) {
                        $isPriceInclTax         = true;
                    } else {
                        $isPriceInclTax         = false;
                    }
                }
                
            }
        } else {
            
            if ($helper->getVersionHelper()->isGe1810()) {
                if ($taxOnOrigPrice) {
                    $taxable                = $origSubtotal;
                    $baseTaxable            = $baseOrigSubtotal;
                } else {
                    $taxable                = $subtotal;
                    $baseTaxable            = $baseSubtotal;
                }
                $appliedRates           = $calculator->getAppliedRates($request);
                $rowTaxes               = array();
                $baseRowTaxes           = array();
                foreach ($appliedRates as $appliedRate) {
                    $taxId                  = $appliedRate['id'];
                    $taxRate                = $appliedRate['percent'];
                    $rowTaxes[]             = $this->_deltaRound(
                        $calculator->calcTaxAmount($taxable, $taxRate, false, false), 
                        $taxId, 
                        false
                    );
                    $baseRowTaxes[]         = $this->_deltaRound(
                        $calculator->calcTaxAmount($baseTaxable, $taxRate, false, false), 
                        $taxId, 
                        false, 
                        'base'
                    );
                }
                $taxSubtotal            = $subtotal + array_sum($rowTaxes);
                $baseTaxSubtotal        = $baseSubtotal + array_sum($baseRowTaxes);
                $taxPrice               = $calculator->round($taxSubtotal/$qty);
                $baseTaxPrice           = $calculator->round($baseTaxSubtotal/$qty);
                $isPriceInclTax         = false;
            } else {
                
                
                if ($helper->getVersionHelper()->isGe1510()) {
                    if ($taxOnOrigPrice) {
                        $rowTax                 = $this->_deltaRound(
                            $calculator->calcTaxAmount($origSubtotal, $rate, false, false), 
                            $rate, 
                            true
                        );
                        $baseRowTax             = $this->_deltaRound(
                            $calculator->calcTaxAmount($baseOrigSubtotal, $rate, false, false), 
                            $rate, 
                            true, 
                            'base'
                        );
                        $taxable                = $origSubtotal;
                        $baseTaxable            = $baseOrigSubtotal;
                    } else {
                        $rowTax                 = $this->_deltaRound(
                            $calculator->calcTaxAmount($subtotal, $rate, false, false), 
                            $rate, 
                            true
                        );
                        $baseRowTax             = $this->_deltaRound(
                            $calculator->calcTaxAmount($baseSubtotal, $rate, false, false), 
                            $rate, 
                            true, 
                            'base'
                        );
                        $taxable                = $subtotal;
                        $baseTaxable            = $baseSubtotal;
                    }
                    $taxSubtotal            = $subtotal + $rowTax;
                    $baseTaxSubtotal        = $baseSubtotal + $baseRowTax;
                    $taxPrice               = $calculator->round($taxSubtotal/$qty);
                    $baseTaxPrice           = $calculator->round($baseTaxSubtotal/$qty);
                } else {
                    $rowTax                 = $this->_deltaRound(
                        $calculator->calcTaxAmount($subtotal, $rate, false, false), 
                        $rate, 
                        true
                    );
                    $baseRowTax             = $this->_deltaRound(
                        $calculator->calcTaxAmount($baseSubtotal, $rate, false, false), 
                        $rate, 
                        true, 
                        'base'
                    );
                    $taxSubtotal            = $subtotal + $rowTax;
                    $baseTaxSubtotal        = $baseSubtotal + $baseRowTax;
                    $taxPrice               = $calculator->round($taxSubtotal/$qty);
                    $baseTaxPrice           = $calculator->round($baseTaxSubtotal/$qty);
                    if ($taxOnOrigPrice) {
                        $taxable                = $origSubtotal;
                        $baseTaxable            = $baseOrigSubtotal;
                    } else {
                        $taxable                = $subtotal;
                        $baseTaxable            = $baseSubtotal;
                    }
                }
                
                $isPriceInclTax         = false;
            }
            
        }
        if ($item->hasCustomPrice()) {
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        } else {
            $item->setConvertedPrice($price);
        }
        $item->setPrice($basePrice);
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxSubtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseTaxSubtotal / $qty);
        } elseif ($isPriceInclTax) {
            $item->setDiscountCalculationPrice($subtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseSubtotal / $qty);
        }
        return $this;
    }
}