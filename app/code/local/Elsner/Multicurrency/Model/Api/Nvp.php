<?php

class Elsner_Multicurrency_Model_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{

	protected function _exportLineItemsCustom(array &$request, $i = 0)
    {
        if (!$this->_cart) {
            return;
        }

        // always add cart totals, even if line items are not requested
        if ($this->_lineItemTotalExportMap) {
            foreach ($this->_cart->getTotals() as $key => $total) {
                if (isset($this->_lineItemTotalExportMap[$key])) { // !empty($total)
                    $privateKey = $this->_lineItemTotalExportMap[$key];
                    $request[$privateKey] = $this->_filterAmount($total);
                }
            }
        }

        // add cart line items
        $items = $this->_cart->getItems();
        if (empty($items) || !$this->getIsLineItemsEnabled()) {
            return;
        }
        $result = null;
        foreach ($items as $item) {
            foreach ($this->_lineItemExportItemsFormat as $publicKey => $privateFormat) {
                $result = true;
                $value = $item->getDataUsingMethod($publicKey);
                /*if($publicKey == 'amount' && is_object($item->getName()) !== true){
                   echo $value; exit;
                   $value = Mage::helper('multicurrency')->getExchangeRate($value);
                }*/
                if (isset($this->_lineItemExportItemsFilters[$publicKey])) {
                    $callback   = $this->_lineItemExportItemsFilters[$publicKey];
                    $value = call_user_func(array($this, $callback), $value);
                }
                if (is_float($value)) {
                    $value = $this->_filterAmount($value);
                }
                $request[sprintf($privateFormat, $i)] = $value;
            }
            $i++;
        }
        return $result;
    }

	protected function _exportLineItems(array &$request, $i = 0)
    {
        if (!$this->_cart) {
            return;
        }
        //$this->_cart->setTransferDiscountAsItem();
        $this->_cart->isDiscountAsItem(true);

        return $this->_exportLineItemsCustom($request, $i);
    }

	public function callSetExpressCheckout()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_setExpressCheckoutRequest);
        $request = $this->_exportToRequest($this->_setExpressCheckoutRequest);

        if(isset($request['AMT']) && isset($request['CURRENCYCODE'])){
            if($request['CURRENCYCODE'] != Mage::helper('multicurrency')->getToCurrency()){
                $request['AMT'] = $this->_cart->getMulticurrencyTotal();
                $request['CURRENCYCODE'] = Mage::helper('multicurrency')->getToCurrency();
                $multicurrencyObj = Mage::getModel('elsner_multicurrency/multicurrency')->addRow($request['INVNUM'],$request['CURRENCYCODE'],'Authorize');
            }
        }
        $this->_exportLineItems($request);

        // import/suppress shipping address, if any
        $options = $this->getShippingOptions();
        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 1;
        } elseif ($options && (count($options) <= 10)) { // doesn't support more than 10 shipping options
            $request['CALLBACK'] = $this->getShippingOptionsCallbackUrl();
            $request['CALLBACKTIMEOUT'] = 6; // max value
            $request['MAXAMT'] = $request['AMT'] + 999.00; // it is impossible to calculate max amount
            $this->_exportShippingOptions($request);
        }

        // add recurring profiles information
        $i = 0;
        foreach ($this->_recurringPaymentProfiles as $profile) {
            $request["L_BILLINGTYPE{$i}"] = 'RecurringPayments';
            $request["L_BILLINGAGREEMENTDESCRIPTION{$i}"] = $profile->getScheduleDescription();
            $i++;
        }

        $response = $this->call(self::SET_EXPRESS_CHECKOUT, $request);
        $this->_importFromResponse($this->_setExpressCheckoutResponse, $response);
    }

    function callGetExpressCheckoutDetails()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_getExpressCheckoutDetailsRequest);
        $request = $this->_exportToRequest($this->_getExpressCheckoutDetailsRequest);
        $response = $this->call(self::GET_EXPRESS_CHECKOUT_DETAILS, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_exportAddressses($response);
    }

    public function callDoExpressCheckoutPayment()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_doExpressCheckoutPaymentRequest);
        $request = $this->_exportToRequest($this->_doExpressCheckoutPaymentRequest);
        if(isset($request['AMT']) && isset($request['CURRENCYCODE'])){
            $request['AMT'] = $this->_cart->getMulticurrencyTotal();
            $request['CURRENCYCODE'] = Mage::helper('multicurrency')->getToCurrency(); 
        }
        $this->_exportLineItems($request);
        $response = $this->call(self::DO_EXPRESS_CHECKOUT_PAYMENT, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doExpressCheckoutPaymentResponse, $response);
        $this->_importFromResponse($this->_createBillingAgreementResponse, $response);
    }

    public function callRefundTransaction()
    {
        $request = $this->_exportToRequest($this->_refundTransactionRequest);
        if ($this->getRefundType() === Mage_Paypal_Model_Config::REFUND_TYPE_PARTIAL) {
            $request['AMT'] = $this->getAmount();
        }
        if(isset($request['CURRENCYCODE'])){
            if($request['CURRENCYCODE']){
                $detail = Mage::getModel('elsner_multicurrency/multicurrency')->getRowByIncrementId($this->getPayment()->getOrder()->getIncrementId());
                if(empty($detail) !== true){
                    if($request['CURRENCYCODE'] != $detail['paypal_currency_code'] && isset($request['AMT']) && $this->getRefundType() === Mage_Paypal_Model_Config::REFUND_TYPE_PARTIAL){
                        $request['AMT'] = Mage::helper('multicurrency')->getConvertedOrderAmount($request['AMT'],$this->getPayment()->getOrder());
                    }
                    $request['CURRENCYCODE'] = $detail['paypal_currency_code'];
                }
                
            }
        }
        $response = $this->call(self::REFUND_TRANSACTION, $request);
        $this->_importFromResponse($this->_refundTransactionResponse, $response);
    }
}