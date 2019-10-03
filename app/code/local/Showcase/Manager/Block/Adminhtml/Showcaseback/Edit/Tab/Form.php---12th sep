<?php
class Showcase_Manager_Block_Adminhtml_Showcasebackend_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("showcasebackend_form", array("legend"=>Mage::helper("showcase")->__("Showcase information")));
				
				$fieldset->addField("name", "text", array(
				"label" => Mage::helper("showcase")->__("Showcase Name"),					
				"required" => false,
				"name" => "name",
				));
				
				$fieldset->addField('product_name', 'label', array(
				  "label" => Mage::helper("showcase")->__("Product Name"),
				));
		
				
				$fieldset->addField('pjnumber', 'label', array(
				  "label" => Mage::helper("showcase")->__("PJ Number"),
				));
			
			
				$fieldset->addField("description", "textarea", array(
				"label" => Mage::helper("showcase")->__("Description"),
				"name" => "description",
				));
				
				$fieldset->addField('product_id', 'label', array(
				  "label" => Mage::helper("showcase")->__("Product Id"),
				));
			
				
				$fieldset->addField("customer_name", "text", array(
				"label" => Mage::helper("showcase")->__("Customer Name"),
				"class" => "required-entry",
				"required" => true,
				"name" => "customer_name",
				));
				
				$fieldset->addField('link', 'label', array(
				  "label" => Mage::helper("showcase")->__("Customization Link"),
				));
				
				$fieldset->addField("is_active", "select", array(
				"label" => Mage::helper("showcase")->__("Add to Showcase"),
				"name" => "is_active",
				'value'  => '1',
				'values' => array('0' => 'No','1' => 'Yes'),
				));
				if (Mage::getSingleton("adminhtml/session")->getShowcaseData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getShowcaseData());
					Mage::getSingleton("adminhtml/session")->getShowcaseData(null);
				} 
				elseif(Mage::registry("showcase_data")) {
				    $form->setValues(Mage::registry("showcase_data")->getData());
				}
				return parent::_prepareForm();
		}
}
