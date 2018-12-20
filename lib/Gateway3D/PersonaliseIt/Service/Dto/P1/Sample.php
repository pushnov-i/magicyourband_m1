<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Service_Dto_P1_Sample
	extends Gateway3D_PersonaliseIt_Service_Dto_Abstract
{
	public $sample_id = 0;
	public $product_id = 0;
		
	public function getId()
	{
		return $this->sample_id;
	}

	public function setId($id)
	{
		$this->sample_id = $id;
	}
}
