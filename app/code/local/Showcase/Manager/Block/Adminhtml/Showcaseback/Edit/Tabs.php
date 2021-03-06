<?php
class Showcase_Manager_Block_Adminhtml_Showcaseback_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("showcaseback_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("showcase")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("showcase")->__("Item Information"),
				"title" => Mage::helper("showcase")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("showcaseback/adminhtml_showcaseback_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
