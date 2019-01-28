<?php

class Elsner_Multicurrency_Block_Checkout_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    public function needDisplayBaseGrandtotal()
    {
        $quote = $this->getQuote();
        if ($quote->getPayment()->getMethodInstance()->getCode() == 'paypal_standard') {
            if (Mage::helper('multicurrency')->shouldConvert() && ($quote->getQuoteCurrencyCode() != Mage::helper('multicurrency')->getToCurrency())) {
                return true;
            } else {
                return false;
            }
        }
        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
            return true;
        }
        return false;
    }

    public function displayBaseGrandtotal()
    {
        $firstTotal = reset($this->_totals);
        if (Mage::helper('multicurrency')->shouldConvert()) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            $total = Mage::helper('multicurrency')->getExchangeRate($total);
            $currency = Mage::getModel('directory/currency')->load(Mage::helper('multicurrency')->getToCurrency());
            return $currency->format($total, array(), true);
        }
        if ($firstTotal) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            return Mage::app()->getStore()->getBaseCurrency()->format($total, array(), true);
        }
        return '-';
    }
}