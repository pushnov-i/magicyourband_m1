<?php

class XJ_Tracking_Model_Mysql4_Tracking_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('tracking/tracking');
    }
}