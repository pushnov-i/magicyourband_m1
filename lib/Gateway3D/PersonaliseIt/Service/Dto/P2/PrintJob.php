<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Service_Dto_P2_PrintJob
	extends Gateway3D_PersonaliseIt_Service_Dto_Abstract
{
	public $print_job_id = 0;
	public $product_id = 0;
	public $print_job_company_id = 0;
	public $items = array();
	
	public function getId()
	{
		return $this->print_job_id;
	}

	public function setId($id)
	{
		$this->print_job_id;
	}
}
