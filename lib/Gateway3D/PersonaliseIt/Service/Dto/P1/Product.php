<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Service_Dto_P1_Product
	extends Gateway3D_PersonaliseIt_Service_Dto_Abstract
{
	public $id = 0;
	public $text_areas = array();
	public $product_attributes = array();
	
	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}
}
