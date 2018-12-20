<?php

class MageStack_Varnish_Helper_Cacheable extends Mage_Core_Helper_Abstract
{

    /**
     * Retrieves current cookie.
     *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        return Mage::app()->getCookie();
    }

    public function isNoCacheStable()
    {
        $this->getCookie()->get('nocache_stable') === 1;
    }

    public function setNoCacheStable($value=1)
    {
        $this->getCookie()->set('nocache_stable', $value);
    }

    public function skipVarnishCache()
    {
        Mage::app()->getResponse()->setHeader('MageStack-Cacheable', 'no', true);
    }

    public function turnOffVarnishCache()
    {
        Mage::app()->getResponse()->setHeader('MageStack-Cacheable', 'no', true);
        $this->getCookie()->set('nocache', 1);
    }

    public function turnOnVarnishCache()
    {
        if ($this->getCookie()->get('nocache'))
        {
            $this->getCookie()->delete('nocache');
        }
    }

    public function quoteHasItems()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        return $quote instanceof Mage_Sales_Model_Quote && $quote->hasItems();
    }

    public function hasCompareItems()
    {
        // see Mage_Catalog_Helper_Product_Compare
        return Mage::helper('catalog/product_compare')->getItemCount() > 0;
    }

    public function isCustomerLoggedIn()
    {
        $customerSession = Mage::getSingleton('customer/session');

        return $customerSession instanceof Mage_Customer_Model_Session && $customerSession->isLoggedIn();
    }

    public function pollVerification()
    {
        $justVotedPollId = (int) Mage::getSingleton('core/session')->getJustVotedPoll();
        if ($justVotedPollId) {
            return true;
        }
    }

    public function matchException()
    {
        $excludesConfig = Mage::getStoreConfig('varnish/options/excluded_uris');
        $excludes = unserialize($excludesConfig);

        if (!is_array($excludes))
            return false;

        $currentUri = $_SERVER['REQUEST_URI'];

        foreach ($excludes as $exclude):

            if(stripos($currentUri, $exclude['regexp']) !== false)
                return true;

        endforeach;

        return false;
    }
}

