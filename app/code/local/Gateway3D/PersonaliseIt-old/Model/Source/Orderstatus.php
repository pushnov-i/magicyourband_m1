<?php

class Gateway3D_PersonaliseIt_Model_Source_Orderstatus
{
	public function toOptionArray()
	{
		return array(
			array('value' => Gateway3D_PersonaliseIt_Model_Sl_Data_Order::STATUS_ITEMS_READY, 'label' => 'Received'),
			array('value' => Gateway3D_PersonaliseIt_Model_Sl_Data_Order::STATUS_ON_HOLD, 'label' => 'On Hold')
		);
	}
}