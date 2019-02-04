<?php

class Elsner_Multicurrency_Helper_Data extends Mage_Core_Helper_Abstract
{
    public static function getBaseCurrency()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }

    public function getCurrencyArray()
    {
        return explode(',', self::getConfig('extra_currencies'));
    }

    public static function getSupportedCurrency()
    {
        return array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN',
            'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD', 'TWD', 'THB','INR');
    }

    public static function shouldConvert()
    {
        return !self::isActive();
        // return self::isActive() && !in_array(self::getBaseCurrency(), self::getSupportedCurrency());
    }

    public static function getConfig($name = '')
    {
        if ($name) {
            return Mage::getStoreConfig('payment/multicurrency/' . $name);
        }
        return;
    }

    /*public static function getToCurrency()
    {
        $to = self::getConfig('to_currency');

        if (!$to) {
            $to = 'USD';
        }
        if(Mage::app()->getStore()->getCurrentCurrencyCode() != "INR"){
            $to = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        return $to;
    } */
	
	public static function getToCurrency(){
		        
		$to = self::getConfig('to_currency');
		if (!$to){
            $to = 'USD';
		}
		
        $current_currency = Mage::app()->getStore()->getCurrentCurrencyCode();
		$getSupportedCurrency = self::getSupportedCurrency();
		if(in_array($current_currency, $getSupportedCurrency)){  
            $to = $current_currency;        
		}
		
        return $to;
    }

    // public function getConvertedAmount($value,$key = null)
    // {
        
    //     $baseCode = Mage::app()->getBaseCurrencyCode();
    //     $toCur = $this->getToCurrency();
            
    //     $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
    //     $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($allowedCurrencies));

    //     $output = ( $value * $rates[$toCur] ) / $rates[$baseCode];

    //     if($key == 'tax'){
    //         return floor(($output*100))/100;    
    //     }
    //     else{
    //         return sprintf('%.2F', $output);    
    //     }

        
    // }

    public function getConvertedAmount($value)
    {
        $baseCode = Mage::app()->getBaseCurrencyCode();
        $toCur = $this->getToCurrency();

        $priceTwo = Mage::helper('directory')->currencyConvert($value, $baseCode, $toCur);
        Mage::log('before='.$priceTwo);
        $priceTwo = floor($priceTwo * 100) / 100;//$this->truncate_number($priceTwo);
        Mage::log('after='.$priceTwo);
        
            
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($allowedCurrencies));

        $roundedvalue = ( $value * $rates[$toCur] ) / $rates[$baseCode];

        $outputArray = explode('.', $roundedvalue);
        $lastdigits = $outputArray['1'];
        $secondDigit = (int) substr($lastdigits, 1,1);

        if($secondDigit >= 5){
            $roundedvalue = round($roundedvalue,2);
        }
        else{
            $roundedvalue = round($roundedvalue,2);
            // $roundedvalue = floor(($roundedvalue*100))/100 ;
        }

        return $priceTwo;
    }

    public function truncate_number( $number, $precision = 2) {
        // Zero causes issues, and no need to truncate
        if ( 0 == (int)$number ) {
            return $number;
        }
        // Are we negative?
        $negative = $number / abs($number);
        // Cast the number to a positive to solve rounding
        $number = abs($number);
        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);
        // Run the math, re-applying the negative value to ensure returns correctly negative / positive
        return floor( $number * $precision ) / $precision * $negative;
    }	
	
    public function getCurrentExchangeRate()
    {
        $auto = self::getConfig('auto_rate');
        if ($auto) {
            $current = Mage::app()->getStore()->getCurrentCurrencyCode();
            $to = self::getToCurrency();
            $rate = Mage::getModel('directory/currency')->getCurrencyRates($current, $to);
            //var_dump($rate);
            if (!empty($rate[$to])) {
                $rate = $rate[$to];
            } else {
                $rate = 1;
            }
        } else {
            $rate = self::getConfig('rate');
        }
        return $rate;
    }

    public static function isActive()
    {
        $state = self::getConfig('active');
        if (!$state) {
            return;
        }
        return $state;

    }

    public function convertAmount($amount = false)
    {
        return self::getExchangeRate($amount);
    }

    public static function getExchangeRate($amount = false)
    {
        
        if (0) {
            return $amount;
        }
        if (!$amount) {
            return;
        }
        $auto = self::getConfig('auto_rate');
        if ($auto) {
            $current = Mage::app()->getStore()->getCurrentCurrencyCode();
            $base = Mage::app()->getStore()->getBaseCurrencyCode();
            $to = self::getToCurrency();
            //$rate = Mage::getModel('directory/currency')->getCurrencyRates($current, $to);
            $rate = Mage::getModel('directory/currency')->getCurrencyRates($base, $to);
            //var_dump($rate);
            if (!empty($rate[$to])) {
                $rate = $rate[$to];
            } else {
                $rate = 1;
            }
        } else {
            $rate = self::getConfig('rate');
        }
        if ($rate) {
        	
        //	echo "<br> amount ".$amount."  ".$rate;
         $final_amount = $amount * $rate; 
          //$fprice=number_format((float)$final_amount, 2, '.', ''); 
                        return $final_amount;
        }
        return;
    }

    public static function getOrderToCurrency($order){
                
        $to = self::getConfig('to_currency');
        if (!$to){
            $to = 'USD';
        }
        
        $current_currency = $order->getOrderCurrencyCode();
        $getSupportedCurrency = self::getSupportedCurrency();
        if(in_array($current_currency, $getSupportedCurrency)){  
            $to = $current_currency;        
        }
        
        return $to;
    }

    public function getConvertedOrderAmount($value , $order)
    {
        
        $baseCode = $order->getBaseCurrencyCode();

        $toCur = $this->getOrderToCurrency($order);
         
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($allowedCurrencies));

        $roundedvalue = ( $value * $rates[$toCur] ) / $rates[$baseCode];

        $outputArray = explode('.', $roundedvalue);
        $lastdigits = $outputArray['1'];
        $secondDigit = (int) substr($lastdigits, 1,1);

        if($secondDigit >= 5){
            $roundedvalue = round($roundedvalue,2);
        }
        else{
            $roundedvalue = round($roundedvalue,2);
            // $roundedvalue = floor(($roundedvalue*100))/100 ;
        }

        return $roundedvalue;
    }

}