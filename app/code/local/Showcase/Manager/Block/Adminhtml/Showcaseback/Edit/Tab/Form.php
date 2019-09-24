<?php
class Showcase_Manager_Block_Adminhtml_Showcaseback_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("showcaseback_form", array("legend"=>Mage::helper("showcase")->__("Showcase information")));
				$showcaseData='';
				if(Mage::registry("showcase_data")) {
				    $showcaseData = Mage::registry("showcase_data")->getData();
				}
				if(empty($showcaseData['customer_name'])){
					$showcaseData['customer_name']="Guest User";
				}
				
				$fieldset->addField("name", "text", array(
					"label" => Mage::helper("showcase")->__("Showcase Name"),					
					"required" => false,
					"name" => "name",
				));
				
				$fieldset->addField('product_name', 'link', array(
				  "label" => Mage::helper("showcase")->__("Product Name"),
				  'style'   => "",
				  'href' => $showcaseData['link'],
				  'after_element_html' => ''
				));
		
				/*$fieldset->addField('product_name', 'label', array(
				  "label" => Mage::helper("showcase")->__("Product Name"),
				));*/
		
				/*$fieldset->addField('link', 'label', array(
				  "label" => Mage::helper("showcase")->__("Customization Link"),
				));*/
			
				$fieldset->addField('link', 'link', array(
				  "label" => Mage::helper("showcase")->__("Customization Link"),
				  'style'   => "",
				  'href' => $showcaseData['link'].'?pj='.$showcaseData['pjnumber'],
				  'after_element_html' => ''
				));
			
				$fieldset->addField("description", "textarea", array(
				"label" => Mage::helper("showcase")->__("Description"),
				"name" => "description",
				));
				
				$fieldset->addField("customer_name", "text", array(
				"label" => Mage::helper("showcase")->__("Customer Name"),
				"required" => false,
				"name" => "customer_name",
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
				    $form->setValues($showcaseData);
				}
				return parent::_prepareForm();
		}
}
