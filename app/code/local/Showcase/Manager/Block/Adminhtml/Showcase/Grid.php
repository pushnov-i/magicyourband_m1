<?php

class Showcase_Manager_Block_Adminhtml_Showcase_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("showcaseGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}
		protected function _prepareCollection()
		{
				$collection = Mage::getModel("showcase/showcase")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("showcase")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("name", array(
				"header" => Mage::helper("showcase")->__("Showcase"),
				"index" => "name",
				));
				
				$this->addColumn("product_name", array(
				"header" => Mage::helper("showcase")->__("Product Name"),
				"index" => "product_name",
				));
				$this->addColumn("pjnumber", array(
				"header" => Mage::helper("showcase")->__("PJ Number"),
				"index" => "pjnumber",
				));
				$this->addColumn("description", array(
				"header" => Mage::helper("showcase")->__("Description"),
				"index" => "description",
				));
				$this->addColumn("customer_name", array(
				"header" => Mage::helper("showcase")->__("Customer Name"),
				"index" => "customer_name",
				));
				$this->addColumn('is_active', array(
				'header' => Mage::helper('showcase')->__('Add to Showcase'),
				'index' => 'is_active',
				'type' => 'options',
				'options'=>Showcase_Manager_Block_Adminhtml_Showcase_Grid::getOptionArray2(),				
				));
						
				//$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
				//$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}
		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_showcase', array(
					 'label'=> Mage::helper('showcase')->__('Remove Showcase'),
					 'url'  => $this->getUrl('*/adminhtml_showcase/massRemove'),
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