<?php

class Showcase_Manager_Block_Adminhtml_Showcaseback_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("showcasebackGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$showcaseCollection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('is_design',1)->setOrder('created_at', 'desc');
				$this->setCollection($showcaseCollection);
				return parent::_prepareCollection();
		}
		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}
}