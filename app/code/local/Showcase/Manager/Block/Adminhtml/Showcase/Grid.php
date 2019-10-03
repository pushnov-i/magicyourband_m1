<?php

class Showcase_Manager_Block_Adminhtml_Showcase_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("showcaseGrid");
				$this->setDefaultSort("entity_id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}
		protected function _prepareCollection()
		{
				$collection = Mage::getModel("catalog/product")->getCollection()
				->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('pjnumber')
                ->addAttributeToSelect('designed_by')
                ->addAttributeToSelect('add_to_showcase')
				->addAttributeToFilter('attribute_set_id',10)
                ->addFieldToFilter('is_this_design',1);
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("entity_id", array(
				"header" => Mage::helper("catalog")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "entity_id",
				));
				
				$this->addColumn("name", array(
				"header" => Mage::helper("catalog")->__("Product Name"),
				"index" => "name",
				));
				$this->addColumn("pjnumber", array(
				"header" => Mage::helper("catalog")->__("PJ Number"),
				"index" => "pjnumber",
				));

				$this->addColumn("designed_by", array(
				"header" => Mage::helper("catalog")->__("Customer Name"),
				"index" => "designed_by",
				));
				$this->addColumn('add_to_showcase', array(
				'header' => Mage::helper('catalog')->__('Add to Showcase'),
				'index' => 'add_to_showcase',
				'type' => 'options',
				'options'=>Showcase_Manager_Block_Adminhtml_Showcase_Grid::getOptionArray2(),				
				));


				return parent::_prepareColumns();
		}
		public function getRowUrl($row)
		{

			  return Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId()));
		}
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			//$this->getMassactionBlock()->addItem('remove_showcase', array(
			//		 'label'=> Mage::helper('showcase')->__('Remove Showcase'),
			//		 'url'  => $this->getUrl('*/adminhtml_showcase/massRemove'),
			//		 'confirm' => Mage::helper('showcase')->__('Are you sure?')
			//	));
			$this->getMassactionBlock()->addItem('add_to_showcase', array(
					 'label'=> Mage::helper('showcase')->__('Add to Showcase'),
					 'url'  => $this->getUrl('*/adminhtml_showcase/massShowcase'),
					 'confirm' => Mage::helper('showcase')->__('Are you sure?')
				));
			$this->getMassactionBlock()->addItem('remove_from_showcase', array(
					 'label'=> Mage::helper('showcase')->__('Remove From Showcase'),
					 'url'  => $this->getUrl('*/adminhtml_showcase/massRemoveShowcase'),
					 'confirm' => Mage::helper('showcase')->__('Are you sure?')
				));
			return $this;
		}
		static public function getOptionArray2()
		{
            $data_array=array(); 
			$data_array[0]='No';
			$data_array[1]='Yes';
            return($data_array);
		}
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(Showcase_Manager_Block_Adminhtml_Showcase_Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
}