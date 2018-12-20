<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Block_Product_View_Options 
	extends Mage_Catalog_Block_Product_View_Options
{
    public function getOptionHtml(Mage_Catalog_Model_Product_Option $option)
	{
		return $option->getType() == Gateway3D_PersonaliseIt_Model_Catalog_Product_Option::OPTION_TYPE_HIDDEN
			? ""
			: parent::getOptionHtml($option);
	}
}
