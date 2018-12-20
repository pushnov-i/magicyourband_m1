<?php

class Magehit_Notification_Model_Mysql4_Notification_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('notification/notification');
    }
    
    public function setValidationFilter($storeId, $customerGroupId, $now = null)
    {
        if (is_null($now)) {
            $now = Mage::getModel('core/date')->date('Y-m-d');
        }

        $this->getSelect()->where('status=1');
        $this->getSelect()->where('find_in_set(?, store_ids) or store_ids = 0', (int)$storeId);
        $this->getSelect()->where('find_in_set(?, customer_group_ids)', (int)$customerGroupId);
        
        $this->getSelect()->where('start_time is null or start_time<=?', $now);
        $this->getSelect()->where('end_time is null or end_time="0000-00-00" or end_time="" or end_time>=?', $now);
        $this->getSelect()->order('notification_id');
        return $this;
    }
    
    public function setValidationFilterUpcoming($storeId, $customerGroupId, $now = null)
    {
        if (is_null($now)) {
            $now = Mage::getModel('core/date')->date('Y-m-d');
        }

        $this->getSelect()->where('status=1');
        $this->getSelect()->where('find_in_set(?, store_ids) or store_ids = 0', (int)$storeId);
        $this->getSelect()->where('find_in_set(?, customer_group_ids)', (int)$customerGroupId);
        
        $this->getSelect()->where('start_time is null or start_time>?', $now);
        $this->getSelect()->where('end_time is null or end_time="0000-00-00" or end_time="" or end_time>=?', $now);
        $this->getSelect()->order('notification_id');
        return $this;
    }
}