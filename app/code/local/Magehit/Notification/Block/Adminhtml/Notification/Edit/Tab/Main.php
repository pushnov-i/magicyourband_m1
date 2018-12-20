<?php
class Magehit_Notification_Block_Adminhtml_Notification_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {    
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('notification_form', array('legend'=>Mage::helper('notification')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('notification')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
      $fieldset->addField('content_notification', 'editor', array(
            'name'      => 'content_notification',
            'label'     => Mage::helper('notification')->__('Content'),
            'title'     => Mage::helper('notification')->__('notification'),
            'required'  => false,
            'config'    => $wysiwygConfig
        ));
		
		$fieldset->addField('link-preview', 'link', array(
          'label'		=> Mage::helper('notification')->__('Preview'),
          'style'   	=> "background:red;text-align:center;color:#fff;",
          'href'		=> 'javascript:void();',
		  'onclick'		=> 'functionPreview()',
          'value'		=> 'Preview',
		  /* 'before_element_html' => '<a id="link-preview-2" style="background-color: red; text-align: center; color: #fff; padding: 5px 10px; border-radius: 5px; cursor: pointer;">PREVIEW</a>', */
          'after_element_html' => '<div style="width:800px;padding:5px;" id="mh-preview-content"></div>'
      ));

      $fieldset->addField('background_color', 'text', array(
          'label'     => Mage::helper('notification')->__('Background Color'),
          'required'  => true,
          'name'      => 'background_color',
	  ));
      
      $fieldset->addField('text_color', 'text', array(
          'label'     => Mage::helper('notification')->__('Text Color'),
          'required'  => true,
          'name'      => 'text_color',
      ));
      
       $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('start_time', 'date', array(
            'name' => 'start_time',
            'label' => Mage::helper('notification')->__('Start Time') ,
            'title' => Mage::helper('notification')->__('Start Time') ,
            'image' => $this->getSkinUrl('images/grid-cal.gif') ,
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'required'  => true,
            'format' => $dateFormatIso
        ));
        $fieldset->addField('end_time', 'date', array(
            'name' => 'end_time',
            'label' => Mage::helper('notification')->__('End Time') ,
            'title' => Mage::helper('notification')->__('End Time') ,
            'image' => $this->getSkinUrl('images/grid-cal.gif') ,
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'required'  => true,
            'format' => $dateFormatIso
            //'after_element_html' => '</br><span class="note"><small>' . $this->__('Leave blank if no limit') . '</small></span>',
        ));
	    
        // Store View
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'multiselect', array(
                'name' => 'store_ids[]',
                'label' => Mage::helper('notification')->__('Store View') ,
                'title' => Mage::helper('notification')->__('Store View') ,
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true) ,
            ));
        }
        else {
            $fieldset->addField('store_ids', 'hidden', array(
                'name' => 'store_ids[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
        }
        
        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name' => 'customer_group_ids[]',
            'label' => Mage::helper('notification')->__('Customer Groups') ,
            'title' => Mage::helper('notification')->__('Customer Groups') ,
            'required' => true,
            'values' => $customerGroups,
        ));
        	
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('notification')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('notification')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('notification')->__('Disabled'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getNotificationData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getNotificationData());
          Mage::getSingleton('adminhtml/session')->setNotificationData(null);
      } elseif ( Mage::registry('notification_data') ) {
          $form->setValues(Mage::registry('notification_data')->getData());
      }
      return parent::_prepareForm();
  }
}