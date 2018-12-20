<?php

class Magehit_Notification_Block_Adminhtml_Notification_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('notification_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('notification')->__('Item Information'));
      
  }

  protected function _beforeToHtml()
  {             
      $this->addTab('main_section', array(
          'label'     => Mage::helper('notification')->__('General'),
          'title'     => Mage::helper('notification')->__('General'),
          'content'   => $this->getLayout()->createBlock('notification/adminhtml_notification_edit_tab_main')->toHtml(),
      ));
      
      $this->addTab('showpagelocation_section', array(
          'label'     => Mage::helper('notification')->__('Apply to'),
          'title'     => Mage::helper('notification')->__('Apply to'),
          'content'   => $this->getLayout()->createBlock('notification/adminhtml_notification_edit_tab_showpagelocation')->toHtml(),
      ));
      
      $this->addTab('includecategories_section', array(
          'label'     => Mage::helper('notification')->__('Included Categories'),
          'title'     => Mage::helper('notification')->__('Included Categories'),
          'content'   => $this->getLayout()->createBlock('notification/adminhtml_notification_edit_tab_includecategories')->toHtml(),
      ));
      
      if ($tabName = $this->getRequest()->getParam('tab')) {
            $tabName = (strpos($tabName, 'notification_tabs') !== false)
                ? substr($tabName, strlen('notification_tabs'))
                : $tabName . '_section';

            if (isset($this->_tabs[$tabName])) $this->setActiveTab($tabName);
        }
     
      return parent::_beforeToHtml();
  }
}