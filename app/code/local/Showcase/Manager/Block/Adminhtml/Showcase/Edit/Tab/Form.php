<?php
class Showcase_Manager_Block_Adminhtml_Showcase_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("showcase_form", array("legend"=>Mage::helper("showcase")->__("Showcase information")));
				
				$fieldset->addField("name", "text", array(
				"label" => Mage::helper("showcase")->__("Showcase Name"),					
				"required" => false,
				"name" => "name",
				));
				
				
				$fieldset->addField('product_name', 'label', array(
				  "label" => Mage::helper("showcase")->__("Product Name"),
				));
		
		
				/*$fieldset->addField("product_name", "text", array(
				"label" => Mage::helper("showcase")->__("Product Name"),					
				"required" => false,
				"name" => "product_name",
				));*/
				
				$fieldset->addField('pjnumber', 'label', array(
				  "label" => Mage::helper("showcase")->__("PJ Number"),
				));
				
				/*$fieldset->addField("pjnumber", "text", array(
				"label" => Mage::helper("showcase")->__("PJ Number"),					
				"class" => "required-entry",
				"required" => false,
				"name" => "pjnumber",
				));*/
			
				$fieldset->addField("description", "textarea", array(
				"label" => Mage::helper("showcase")->__("Description"),
				"name" => "description",
				));
				
				$fieldset->addField('product_id', 'label', array(
				  "label" => Mage::helper("showcase")->__("Product Id"),
				));
				
				/*$fieldset->addField("product_id", "text", array(
				"label" => Mage::helper("showcase")->__("Product Id"),
				"required" => false,
				"name" => "product_id",
				));*/
				
				$fieldset->addField("customer_name", "text", array(
				"label" => Mage::helper("showcase")->__("Customer Name"),
				"class" => "required-entry",
				"required" => true,
				"name" => "customer_name",
				));
				
				$fieldset->addField('link', 'label', array(
				  "label" => Mage::helper("showcase")->__("Customization Link"),
				));
				
				/*$fieldset->addField("link", "text", array(
				"label" => Mage::helper("showcase")->__("Customization Link"),
				"required" => false,
				"name" => "link",
				));*/
				
				/*$fieldset->addField("image_url", "text", array(
				"label" => Mage::helper("showcase")->__("Customized Image Url"),
				"required" => false,
				"name" => "image_url",
				));*/
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
