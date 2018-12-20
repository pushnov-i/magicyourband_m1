<?php
class Magehit_Notification_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_notification';
    $this->_blockGroup = 'notification';
    $this->_headerText = Mage::helper('notification')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('notification')->__('Add Item');
    parent::__construct();
  }
}