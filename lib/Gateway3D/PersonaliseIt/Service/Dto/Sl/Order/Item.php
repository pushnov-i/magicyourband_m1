<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Service_Dto_Sl_Order_Item
	extends Gateway3D_PersonaliseIt_Service_Dto_Abstract
{
	const TYPE_UNKNOWN			= 0;
	const TYPE_EXTERNAL_URL		= 1;
	const TYPE_PRINT_JOB		= 2;
	const TYPE_PRINT_ON_DEMAND	= 3;
	const TYPE_PLAIN			= 4;
	
	const STATUS_UNKNOWN		= 0;
	const STATUS_READY			= 1;
	const STATUS_IN_PRODUCTION	= 2;
	const STATUS_DISPATCHED		= 3;
	
	public $id					= 0;
	public $order_id			= 0;			
	public $sku					= '';
	public $description			= '';
	public $quantity			= 0;
	public $type				= 0;
	public $status				= 0;
	public $ref					= '';
	public $external_ref		= '';
	public $print_job_id		= 0;
	public $print_on_demand_ref	= '';
	
	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}
}
