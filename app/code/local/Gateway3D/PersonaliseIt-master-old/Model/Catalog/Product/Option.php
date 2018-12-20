<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Model_Catalog_Product_Option
	extends Mage_Catalog_Model_Product_Option
{
	const OPTION_GROUP_HIDDEN			= 'hidden';
	const OPTION_TYPE_HIDDEN			= 'hidden';
	
	public function getGroupByType($type = null)
    {
        if (is_null($type)) {
            $type = $this->getType();
        }
		
		if($type === self::OPTION_TYPE_HIDDEN)
		{
			return self::OPTION_GROUP_HIDDEN;
		}
        else
		{
			return parent::getGroupByType($type);
		}
    }
	
	public function groupFactory($type)
	{
		if($type === self::OPTION_TYPE_HIDDEN)
		{
			return Mage::getModel('personaliseit/catalog_product_option_type_hidden');
		}
		else
		{
			return parent::groupFactory($type);
		}
	}
}
