<?php
class Magehit_Notification_Block_Notificationbottom extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
    
    public function getAllNotificationsActive()
    {
        $storeId         = Mage::app()->getStore()->getId();
        $customerGroupId = 0;
        $login = Mage::getSingleton('customer/session')->isLoggedIn();
        if($login){
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        
        $collection = Mage::getModel('notification/notification')->getCollection()->setValidationFilter($storeId, $customerGroupId);
    
        return $collection;
    }
}