<?php
	
class Showcase_Manager_Block_Adminhtml_Showcaseback_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "showcaseback";
				$this->_controller = "adminhtml_showcaseback";
				$this->_updateButton("save", "label", Mage::helper("showcase")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("showcase")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("showcase")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("showcase_data") && Mage::registry("showcase_data")->getId() ){

				    return Mage::helper("showcase")->__("Edit Showcase '%s'", $this->htmlEscape(Mage::registry("showcase_data")->getId()));

				} 
				else{

				     return Mage::helper("showcase")->__("Add Showcase");

				}
		}
}