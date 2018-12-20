<?php
class Magehit_Notification_Model_System_Config_Locationarray extends Mage_Core_Model_Abstract

{
    const SPACE = '""';
    const HOME_PAGE = 'home';
    const CATEGORY_PAGE = 'category';
    const PRODUCT_PAGE = 'product';
    const CMS_PAGE = 'cms';
    
    public static function toOptionArray($display = true)

    {
        $options = self::toShortOptionArray($display);
        $values = array();
        foreach($options as $k => $v) $values[] = array(
            'value' => $k,
            'label' => $v
        );
        return $values;
    }
    public static function toShortOptionArray($display = true)

    {
        $result = array();
        $result[self::SPACE] = Mage::helper('notification')->__('');
        $result[self::HOME_PAGE] = Mage::helper('notification')->__('Home Page');
        $result[self::CATEGORY_PAGE] = Mage::helper('notification')->__('Category Page');
        $result[self::PRODUCT_PAGE] = Mage::helper('notification')->__('Product Page');
        $result[self::CMS_PAGE] = Mage::helper('notification')->__('CMS Page');
        return $result;
    }
}
?>