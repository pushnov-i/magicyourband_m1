<?php

class Elsner_Multicurrency_Model_Pro extends Mage_Paypal_Model_Pro
{
    public function capture(Varien_Object $payment, $amount)
    {
        $authTransactionId = $this->_getParentTransactionId($payment);
        if (!$authTransactionId) {
            return false;
        }
        $amount = Mage::helper('multicurrency')->getConvertedOrderAmount($amount,$payment->getOrder());
        $toCurrency = Mage::helper('multicurrency')->getOrderToCurrency($payment->getOrder());
        $api = $this->getApi()
            ->setAuthorizationId($authTransactionId)
            ->setIsCaptureComplete($payment->getShouldCloseParentTransaction())
            //->setAmount($amount)
            //->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setAmount($amount)
            ->setCurrencyCode($toCurrency)
            ->setInvNum($payment->getOrder()->getIncrementId())
            // TODO: pass 'NOTE' to API
        ;

        $api->callDoCapture();
        $this->_importCaptureResultToPayment($api, $payment);
    }

   
    public function refund(Varien_Object $payment, $amount)
    {
        $captureTxnId = $this->_getParentTransactionId($payment);
        if ($captureTxnId) {
            $api = $this->getApi();
            $order = $payment->getOrder();
            $amount = Mage::helper('multicurrency')->getConvertedOrderAmount($amount,$order);
            $toCurrency = Mage::helper('multicurrency')->getOrderToCurrency($order);
            $api->setPayment($payment)
                ->setTransactionId($captureTxnId)
                ->setAmount($amount)
                ->setCurrencyCode($toCurrency)
            ;
            $canRefundMore = $payment->getCreditmemo()->getInvoice()->canRefund();
            $isFullRefund = !$canRefundMore
                && (0 == ((float)$order->getBaseTotalOnlineRefunded() + (float)$order->getBaseTotalOfflineRefunded()));
            $api->setRefundType($isFullRefund ? Mage_Paypal_Model_Config::REFUND_TYPE_FULL
                : Mage_Paypal_Model_Config::REFUND_TYPE_PARTIAL
            );
            $api->callRefundTransaction();
            $this->_importRefundResultToPayment($api, $payment, $canRefundMore);
        } else {
            Mage::throwException(Mage::helper('paypal')->__('Impossible to issue a refund transaction because the capture transaction does not exist.'));
        }
    }
}
