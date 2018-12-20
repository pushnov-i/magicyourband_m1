<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

abstract class Gateway3D_PersonaliseIt_Service_Dto_Abstract
	implements Gateway3D_PersonaliseIt_Service_Dto_Interface
{
	public function toArray()
	{
		return (array)$this;
	}
}