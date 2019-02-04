<?php

class Elsner_Multicurrency_Model_Mysql4_Multicurrency extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the tracking_id refers to the key field in your database table.
        $this->_init('elsner_multicurrency/multicurrency', 'multicurrency_id');
    }
}