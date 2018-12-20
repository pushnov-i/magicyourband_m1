<?php
class Magehit_Notification_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getNotificationUrl()
    {
        return $this->_getUrl('notification/index');
    }
    
    public function getEnabled(){
         return Mage::getStoreConfig('notification/config/enabled', Mage::app()->getStore()->getId());
    }
    
    public function getPosition(){
         return Mage::getStoreConfig('notification/config/position', Mage::app()->getStore()->getId());
    }
    
    public function getPositionFixed(){
         return Mage::getStoreConfig('notification/config/position_fixed', Mage::app()->getStore()->getId());
    }
    
    public function getNumberNotificationsDisplay(){
         return Mage::getStoreConfig('notification/config/numbernotification', Mage::app()->getStore()->getId());
    }
    
    public function checkNotificationDisplay($notificationId){
        $currentLocation = "";
        if(Mage::registry('current_category') != null && Mage::registry('current_product') == null)
        $currentLocation = Magehit_Notification_Model_System_Config_Locationarray::CATEGORY_PAGE;
        
        if(Mage::registry('current_product') != null)
        $currentLocation = Magehit_Notification_Model_System_Config_Locationarray::PRODUCT_PAGE;
        
        if(Mage::getSingleton('cms/page')->getIdentifier() == 'home' && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms')
        $currentLocation = Magehit_Notification_Model_System_Config_Locationarray::HOME_PAGE;
        
        if(Mage::getSingleton('cms/page')->getIdentifier() != 'home' && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms')
        $currentLocation = Magehit_Notification_Model_System_Config_Locationarray::CMS_PAGE;
        
        if($notificationId != ""){
            
            $notification = Mage::getModel('notification/notification')->load($notificationId);
            
            $showLocation = $notification->getShowLocation();
            
            if($showLocation == "," || $showLocation == "") return true;
            
            else{
                
                $showLocationArray = explode(',',$showLocation);
                
                if(in_array($currentLocation,$showLocationArray))
                {
                    if($currentLocation == Magehit_Notification_Model_System_Config_Locationarray::CATEGORY_PAGE){
                        
                        if($notification->getCategoryIds() == "," || $notification->getCategoryIds() == "") return true;
                        
                        $categoryIdsArray = explode(',', $notification->getCategoryIds());
                        
                        $currentCategoryId = Mage::registry('current_category')->getId();
                     
                        if(in_array($currentCategoryId,$categoryIdsArray)) return true;
                        else return false;  
                    }   
                    else return true; 
                }
                
                else return false;
            }
            
        }
        
        return false;
            
    } 
}