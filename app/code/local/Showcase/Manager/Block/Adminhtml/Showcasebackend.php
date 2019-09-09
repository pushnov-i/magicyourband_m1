<?php  

class Showcase_Manager_Block_Adminhtml_Showcasebackend extends Mage_Adminhtml_Block_Template {
	public function __construct()
	{
		$this->_controller = "adminhtml_showcasebackend";
		$this->_blockGroup = "showcasebackend";
		$this->_headerText = Mage::helper("showcase")->__("New Showcase Manager");
		/*$this->_addButtonLabel = Mage::helper("showcase")->__("Add New Item");*/
		parent::__construct();
		/*$this->_removeButton('add');*/
	}
}