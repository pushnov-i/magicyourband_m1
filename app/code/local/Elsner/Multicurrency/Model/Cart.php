<?php

class Elsner_Multicurrency_Model_Cart extends Mage_Paypal_Model_Cart
{
    /**
     * Add a line item
     *
     * @param string  $name
     * @param numeric $qty
     * @param float   $amount
     * @param string  $identifier
     * @return Varien_Object
     */
    public function addItem($name, $qty, $amount, $identifier = null)
    {
        $this->_shouldRender = true;
        if($name != 'Discount') {
            $amount = Mage::helper('multicurrency')->getExchangeRate($amount);
        }

        $item = new Varien_Object(array(
            'name'   => $name,
            'qty'    => $qty,
            'amount' => (float)$amount,
        ));
        if ($identifier) {
            $item->setData('id', $identifier);
        }
        $this->_items[] = $item;
        return $item;
    }

    /**
     * Check the line items and totals according to PayPal business logic limitations
     */
    /*protected function _validate()
    {
        $this->_areItemsValid = true;
        $this->_areTotalsValid = true;

        //$referenceAmount = $this->_salesEntity->getBaseGrandTotal();
        $referenceAmount = Mage::helper('multicurrency')->getExchangeRate($this->_salesEntity->getBaseGrandTotal());
        $itemsSubtotal = 0;
        foreach ($this->_items as $i) {
            $itemsSubtotal = $itemsSubtotal + $i['qty'] * $i['amount'];
        }
        $sum = $itemsSubtotal + $this->_totals[self::TOTAL_TAX];
        if (!$this->_isShippingAsItem) {
            $sum += $this->_totals[self::TOTAL_SHIPPING];
        }
        if (!$this->_isDiscountAsItem) {
	    $discountAmount = Mage::helper('multicurrency')->getExchangeRate($this->_totals[self::TOTAL_DISCOUNT]);
            $sum -= $discountAmount;
//            $sum -= $this->_totals[self::TOTAL_DISCOUNT];
        }

       
	Mage::getModel('core/log_adapter', 'payment_paypal_standard.log')->log($sum);
	Mage::getModel('core/log_adapter', 'payment_paypal_standard.log')->log($referenceAmount);
        if (sprintf('%.4F', $sum) == sprintf('%.4F', $referenceAmount)) {
            $this->_areItemsValid = true;
        }

        // PayPal requires to have discount less than items subtotal
        if (!$this->_isDiscountAsItem) {
            $this->_areTotalsValid = round($discountAmount, 4) < round($itemsSubtotal, 4);
        } else {
            $this->_areTotalsValid = $itemsSubtotal > 0.00001;
        }
	Mage::getModel('core/log_adapter', 'payment_paypal_standard.log')->log($this->_areTotalsValid );
Mage::getModel('core/log_adapter', 'payment_paypal_standard.log')->log($this->_areItemsValid);
Mage::getModel('core/log_adapter', 'payment_paypal_standard.log')->log($this->_totals);

        $this->_areItemsValid = $this->_areItemsValid && $this->_areTotalsValid;
    }*/

    protected function _validate()
    {
        $this->_areItemsValid = true;
        $this->_areTotalsValid = false;

        $referenceAmount = $this->_salesEntity->getBaseGrandTotal();

        if(Mage::helper('multicurrency')->getToCurrency() == $this->_salesEntity->getQuoteCurrencyCode()){
            $referenceAmount = $this->_salesEntity->getGrandTotal();
        }else{
            $referenceAmount = Mage::helper('multicurrency')->getConvertedAmount($referenceAmount);
        }

        $itemsSubtotal = 0;
        foreach ($this->_items as $i) {
            if(is_object($i['name']) !== true){
                $itemPrice = Mage::helper('multicurrency')->getConvertedAmount($i['amount']);
            }else{
                $itemPrice = $i['amount'];
            }
            $itemsSubtotal = $itemsSubtotal + $i['qty'] * $itemPrice;
        }
        $sum = $itemsSubtotal + $this->_totals[self::TOTAL_TAX];
        if (!$this->_isShippingAsItem) {
            $sum += $this->_totals[self::TOTAL_SHIPPING];
        }
        if (!$this->_isDiscountAsItem) {
            $sum -= $this->_totals[self::TOTAL_DISCOUNT];
        }
        /**
         * numbers are intentionally converted to strings because of possible comparison error
         * see http://php.net/float
         */
        // match sum of all the items and totals to the reference amount
        if (sprintf('%.4F', $sum) != sprintf('%.4F', $referenceAmount)) {
            $adjustment = $sum - $referenceAmount;
            $this->_totals[self::TOTAL_SUBTOTAL] = $this->_totals[self::TOTAL_SUBTOTAL] - $adjustment;
        }

        // PayPal requires to have discount less than items subtotal
        if (!$this->_isDiscountAsItem) {
            $this->_areTotalsValid = round($this->_totals[self::TOTAL_DISCOUNT], 4) < round($itemsSubtotal, 4);
        } else {
            $this->_areTotalsValid = $itemsSubtotal > 0.00001;
        }
        $this->_areItemsValid = $this->_areItemsValid && $this->_areTotalsValid;
    }

    /**
     * Render and get line items
     * By default returns false if the items are invalid
     *
     * @param bool $bypassValidation
     * @return array|false
     */
    public function getItems($bypassValidation = false)
    {
        $this->_render();
        return $this->_items;
    }

    protected function _render()
    {
        //parent::_render();
        //echo "<pre>";
        //print_r($this->_totals); exit;
        //$this->_salesEntity->getId(); exit;

        // regular items from the sales entity
        $this->_items = array();
        foreach ($this->_salesEntity->getAllItems() as $item) {
            if (!$item->getParentItem()) {
                $this->_addRegularItem($item);
            }
        }

        if ($this->_salesEntity instanceof Mage_Sales_Model_Order) {
            $shippingDescription = $this->_salesEntity->getShippingDescription();
            $this->_totals = array (
                self::TOTAL_SUBTOTAL => $this->_salesEntity->getSubtotal(),
                self::TOTAL_TAX      => $this->_salesEntity->getTaxAmount(),
                self::TOTAL_SHIPPING => $this->_salesEntity->getShippingAmount(),
                self::TOTAL_DISCOUNT => abs($this->_salesEntity->getDiscountAmount())
            );
            foreach ($this->_totals as $key => $value) {
                //$this->_totals[$key] = Mage::helper('multicurrency')->getExchangeRate($this->_totals[$key]);
                if(Mage::helper('multicurrency')->getToCurrency() == $this->_salesEntity->getOrderCurrencyCode()){
                    if($key == self::TOTAL_SUBTOTAL){
                        $this->_totals[$key] = $this->_salesEntity->getSubtotal();
                    }elseif($key == self::TOTAL_TAX){
                        $this->_totals[$key] = $this->_salesEntity->getTaxAmount();
                    }elseif($key == self::TOTAL_SHIPPING){
                        $this->_totals[$key] = $this->_salesEntity->getShippingAmount();
                    }elseif($key == self::TOTAL_DISCOUNT){
                        $this->_totals[$key] = abs($this->_salesEntity->getDiscountAmount());
                    }
                }else{
                    $this->_totals[$key] = Mage::helper('multicurrency')->getConvertedAmount($this->_totals[$key]);
                }
            }
        } else {
            $address = $this->_salesEntity->getIsVirtual() ?
                $this->_salesEntity->getBillingAddress() : $this->_salesEntity->getShippingAddress();
            $shippingDescription = $address->getShippingDescription();
            $this->_totals = array (
                self::TOTAL_SUBTOTAL => $this->_salesEntity->getBaseSubtotal(),
                self::TOTAL_TAX      => $address->getBaseTaxAmount(),
                self::TOTAL_SHIPPING => $address->getBaseShippingAmount(),
                self::TOTAL_DISCOUNT => abs($address->getBaseDiscountAmount()),
            );
            foreach ($this->_totals as $key => $value) {
                //$this->_totals[$key] = Mage::helper('multicurrency')->getExchanFgeRate($this->_totals[$key]);
                if(Mage::helper('multicurrency')->getToCurrency() == $this->_salesEntity->getQuoteCurrencyCode()){
                    if($key == self::TOTAL_SUBTOTAL){
                        $this->_totals[$key] = $this->_salesEntity->getSubtotal();
                    }elseif($key == self::TOTAL_TAX){
                        $this->_totals[$key] = $address->getTaxAmount();
                    }elseif($key == self::TOTAL_SHIPPING){
                        $this->_totals[$key] = $address->getShippingAmount();
                    }elseif($key == self::TOTAL_DISCOUNT){
                        $this->_totals[$key] = abs($address->getDiscountAmount());
                    }
                }else{
                    $this->_totals[$key] = Mage::helper('multicurrency')->getConvertedAmount($this->_totals[$key]);
                }
            }
        }
        $originalDiscount = $this->_totals[self::TOTAL_DISCOUNT];

        // arbitrary items, total modifications
        Mage::dispatchEvent('paypal_prepare_line_items', array('paypal_cart' => $this));

        // distinguish original discount among the others
        if ($originalDiscount > 0.0001 && isset($this->_totalLineItemDescriptions[self::TOTAL_DISCOUNT])) {
            $this->_totalLineItemDescriptions[self::TOTAL_DISCOUNT][] = Mage::helper('sales')->__('Discount (%s)', Mage::app()->getStore()->convertPrice($originalDiscount, true, false));
        }



        // discount, shipping as items
        if ($this->_isDiscountAsItem && $this->_totals[self::TOTAL_DISCOUNT]) {

            $this->addItem(Mage::helper('paypal')->__('Discount'), 1, -1.00 * $this->_totals[self::TOTAL_DISCOUNT],
                $this->_renderTotalLineItemDescriptions(self::TOTAL_DISCOUNT)
            );
        }
        $shippingItemId = $this->_renderTotalLineItemDescriptions(self::TOTAL_SHIPPING, $shippingDescription);
        if ($this->_isShippingAsItem && (float)$this->_totals[self::TOTAL_SHIPPING]) {
            $this->addItem(Mage::helper('paypal')->__('Shipping'), 1, (float)$this->_totals[self::TOTAL_SHIPPING],
                $shippingItemId
            );
        }




        //$this->_validate();
        // if cart items are invalid, prepare cart for transfer without line items
        if (!$this->_areItemsValid) {
            $this->removeItem($shippingItemId);
        }

        $this->_shouldRender = false;

        Mage::dispatchEvent('paypal_prepare_line_items', array('paypal_cart' => $this));


    }


    public function getTotals($mergeDiscount = false)
    {
        $this->_render();

        if($this->_totals[self::TOTAL_DISCOUNT]) {
            $this->_totals[self::TOTAL_SUBTOTAL] = $this->_totals[self::TOTAL_SUBTOTAL] - round(abs($this->_totals[self::TOTAL_DISCOUNT]), 2);
        }
        $this->_areTotalsValid = true;
        // cut down totals to one total if they are invalid
        if (!$this->_areTotalsValid) {
            $totals = array(self::TOTAL_SUBTOTAL =>
                $this->_totals[self::TOTAL_SUBTOTAL] + $this->_totals[self::TOTAL_TAX]
            );
            if (!$this->_isShippingAsItem) {
                $totals[self::TOTAL_SUBTOTAL] += $this->_totals[self::TOTAL_SHIPPING];
            }
            if (!$this->_isDiscountAsItem) {
                $totals[self::TOTAL_SUBTOTAL] -= $this->_totals[self::TOTAL_DISCOUNT];
            }
            return $totals;
        } elseif ($mergeDiscount) {
            $totals = $this->_totals;
            unset($totals[self::TOTAL_DISCOUNT]);
            if (!$this->_isDiscountAsItem) {
                $totals[self::TOTAL_SUBTOTAL] -= $this->_totals[self::TOTAL_DISCOUNT];
            }
            return $totals;
        }

        return $this->_totals;
    }

    public function getMulticurrencyTotal()
    {
        if ($this->_salesEntity instanceof Mage_Sales_Model_Order) {
            //echo "hello"; exit;
            if(Mage::helper('multicurrency')->getToCurrency() == $this->_salesEntity->getOrderCurrencyCode()){
                return $this->_salesEntity->getGrandTotal();
            }else{
                $amount = Mage::helper('multicurrency')->getConvertedAmount($this->_salesEntity->getBaseGrandTotal());
                return $amount;
            }
        }else{
            if(Mage::helper('multicurrency')->getToCurrency() == $this->_salesEntity->getQuoteCurrencyCode()){
                return $this->_salesEntity->getGrandTotal();
            }else{
                $amount = Mage::helper('multicurrency')->getConvertedAmount($this->_salesEntity->getBaseGrandTotal());
                return $amount;
            }
        }
    }

    /**
     * Add a usual line item with amount and qty
     *
     * @param Varien_Object $salesItem
     * @return Varien_Object
     */
    protected function _addRegularItem(Varien_Object $salesItem)
    {
        if ($this->_salesEntity instanceof Mage_Sales_Model_Order) {
            $qty = (int) $salesItem->getQtyOrdered();
            $amount = (float) $salesItem->getBasePrice();
            // TODO: nominal item for order
        } else {
            $qty = (int) $salesItem->getTotalQty();
            $amount = $salesItem->isNominal() ? 0 : (float) $salesItem->getBaseCalculationPrice();
        }
        // workaround in case if item subtotal precision is not compatible with PayPal (.2)
        $subAggregatedLabel = '';
        if ($amount - round($amount, 2)) {
            $amount = $amount * $qty;
            $subAggregatedLabel = ' x' . $qty;
            $qty = 1;
        }

        // aggregate item price if item qty * price does not match row total
        /*if (($amount * $qty) != $salesItem->getBaseRowTotal()) {
            $amount = (float) $salesItem->getBaseRowTotal();
            $subAggregatedLabel = ' x' . $qty;
            $qty = 1;
        }*/

        return $this->addItem($salesItem->getName() . $subAggregatedLabel, $qty, $amount, $salesItem->getSku());
    }

}
