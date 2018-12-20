<?php

class Gateway3D_PersonaliseIt_Block_Adminhtml_Catalog_Product_Edit_Tab_Options
	extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options
{
	public function __construct()
	{
		parent::__construct();

		$this->setTemplate('personaliseit/catalog/product/edit/options.phtml');
	}

	protected function _prepareLayout()
	{
		$this->setChild('import_from_cpp_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label' => Mage::helper('catalog')->__('Import From Gateway CPP'),
					'class' 	=> 'add',
					'id'		=> 'import_from_cpp_button'
				))
		);

		return parent::_prepareLayout();
	}

	public function getImportFromCppButtonHtml()
	{
		return $this->getChildHtml('import_from_cpp_button');
	}
}
