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
				
				$fieldset->addField("product_name", "text", array(
				"label" => Mage::helper("showcase")->__("Product Name"),					
				"class" => "required-entry",
				"required" => true,
				"name" => "product_name",
				));
				
				$fieldset->addField("pjnumber", "text", array(
				"label" => Mage::helper("showcase")->__("PJ Number"),					
				"class" => "required-entry",
				"required" => true,
				"name" => "pjnumber",
				));
			
				$fieldset->addField("description", "textarea", array(
				"label" => Mage::helper("showcase")->__("Description"),
				"name" => "description",
				));
				
				$fieldset->addField("product_id", "text", array(
				"label" => Mage::helper("showcase")->__("Product Id"),
				"class" => "required-entry",
				"required" => true,
				"name" => "product_id",
				));
				
				$fieldset->addField("customer_name", "text", array(
				"label" => Mage::helper("showcase")->__("Customer Name"),
				"class" => "required-entry",
				"required" => true,
				"name" => "customer_name",
				));
				
				$fieldset->addField("link", "text", array(
				"label" => Mage::helper("showcase")->__("Customization Link"),
				"class" => "required-entry",
				"required" => true,
				"name" => "link",
				));
				
				$fieldset->addField("image_url", "text", array(
				"label" => Mage::helper("showcase")->__("Customized Image Url"),
				"class" => "required-entry",
				"required" => true,
				"name" => "image_url",
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
