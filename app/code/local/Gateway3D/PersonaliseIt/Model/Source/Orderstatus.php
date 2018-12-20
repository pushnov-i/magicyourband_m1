<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Model_Source_Orderstatus
{
	public function toOptionArray()
	{
		return array(
			array('value' => Gateway3D_PersonaliseIt_Service_Dto_Sl_Order::STATUS_ITEMS_READY, 'label' => 'Received'),
			array('value' => Gateway3D_PersonaliseIt_Service_Dto_Sl_Order::STATUS_ON_HOLD, 'label' => 'On Hold')
		);
	}
}