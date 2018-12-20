<?php

class Magehit_Notification_Model_Mysql4_Notification extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('notification/notification', 'notification_id');
    }
}