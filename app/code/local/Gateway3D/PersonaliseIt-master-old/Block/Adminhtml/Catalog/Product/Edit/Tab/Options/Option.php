<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Option 
	extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option 
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('personaliseit/option.phtml');
	}

	public function getImportFromCppButtonId()
	{
		$buttonId = $this
						->getLayout()
						->getBlock('admin.product.options')
						->getChild('import_from_cpp_button')->getId();

		return $buttonId;						
	}
}
