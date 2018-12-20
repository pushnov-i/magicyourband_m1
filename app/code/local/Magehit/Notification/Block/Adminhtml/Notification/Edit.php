<?php

class Magehit_Notification_Block_Adminhtml_Notification_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'notification';
        $this->_controller = 'adminhtml_notification';
        
        $this->_updateButton('save', 'label', Mage::helper('notification')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('notification')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('content_notification') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content_notification');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content_notification');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            Event.observe(window, 'load', function() {
                if(jQuery('#background_color').val()!= '')
                jQuery('#background_color').css('background',jQuery('#background_color').val());
                
                if(jQuery('#text_color').val()!= '')
                jQuery('#text_color').css('background',jQuery('#text_color').val());   
				loadContentPreview();
            })
            
            
            jQuery('#background_color').colpick({
                layout:'hex',
                submit:0,
                colorScheme:'dark',
                onChange:function(hsb,hex,rgb,el,bySetColor) {
                    jQuery(el).css('background','#'+hex);
                    jQuery(el).val('#'+hex);
					loadContentPreview();
                }
            }).keyup(function(){
                jQuery(this).colpickSetColor(this.value);
				loadContentPreview();
            });
            
            
            jQuery('#text_color').colpick({
                layout:'hex',
                submit:0,
                colorScheme:'dark',
                onChange:function(hsb,hex,rgb,el,bySetColor) {
                    jQuery(el).css('background','#'+hex);
                    jQuery(el).val('#'+hex);
					loadContentPreview();
                }
            }).keyup(function(){
                jQuery(this).colpickSetColor(this.value);
				loadContentPreview();
            });
			
			jQuery('#link-preview-2').click(function(){
				loadContentPreview();
			});
			function loadContentPreview(){
				var content = jQuery('#content_notification').val();
				var bg = jQuery('#background_color').val();
				var tc = jQuery('#text_color').val();
				jQuery('#mh-preview-content').css('background',bg);
				jQuery('#mh-preview-content').css('color',tc);
				jQuery('#mh-preview-content').css('text-align','center');
				jQuery('#mh-preview-content').html(content);
			}
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('notification_data') && Mage::registry('notification_data')->getId() ) {
            return Mage::helper('notification')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('notification_data')->getTitle()));
        } else {
            return Mage::helper('notification')->__('Add Item');
        }
    }
}