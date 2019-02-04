<?php

class XJ_Tracking_Model_Mysql4_Tracking extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the tracking_id refers to the key field in your database table.
        $this->_init('tracking/tracking', 'tracking_id');
    }
}