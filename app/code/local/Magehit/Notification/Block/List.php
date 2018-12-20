<?php
class Magehit_Notification_Block_List extends Mage_Core_Block_Template
{
    public function getNotificationCollection()
    {

        $type = $this->getRequest()->getParam('type');
        switch ($type) {
          case 'current':
            $collection = $this->getCurrentNotifications();
            break;
          case 'upcoming':
            $collection = $this->getUpcomingNotifications();
            break;
          default:
            $collection = $this->getCurrentNotifications();
            break;
        }

        return $collection;
    }


    public function getCurrentNotifications(){
        
        $storeId         = Mage::app()->getStore()->getId();
        
        $customerGroupId = 0;
        
        $login = Mage::getSingleton('customer/session')->isLoggedIn();
        
        if($login){
            
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            
        }
        
        $collection = Mage::getModel('notification/notification')->getCollection()->setValidationFilter($storeId, $customerGroupId);
    
        return $collection;
    }

    public function getUpcomingNotifications(){
       
        $storeId         = Mage::app()->getStore()->getId();
        
        $customerGroupId = 0;
        
        $login = Mage::getSingleton('customer/session')->isLoggedIn();
        
        if($login){
            
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            
        }
        
        $collection = Mage::getModel('notification/notification')->getCollection()->setValidationFilterUpcoming($storeId, $customerGroupId);
    
        return $collection; 
    }
}