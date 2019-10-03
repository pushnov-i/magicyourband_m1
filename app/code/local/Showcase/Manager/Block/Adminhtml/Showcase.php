<?php
class Showcase_Manager_Block_Adminhtml_Showcase extends Mage_Adminhtml_Block_Widget_Grid_Container{
	public function __construct()
	{

	$this->_controller = "adminhtml_showcase";
	$this->_blockGroup = "showcase";
	$this->_headerText = Mage::helper("showcase")->__("Showcase Manager");
	/*$this->_addButtonLabel = Mage::helper("showcase")->__("Add New Item");*/
	parent::__construct();
	$collection = Mage::getModel("catalog/product")->getCollection()->addFieldToFilter('is_this_design',1);
	$this->setCollection($collection);
	/*$this->_removeButton('add');*/
	}
}