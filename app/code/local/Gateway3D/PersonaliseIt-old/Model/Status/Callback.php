
<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
class Gateway3D_PersonaliseIt_Model_Status_Callback
{
	/**
	 * @var Mage_Sales_Model_Order
	 */
	private $_order;
	
	public function load($ref)
	{
		$collection = Mage::getResourceModel('sales/order_collection')
						->addAttributeToSelect('*')
						->addFieldToFilter('status_callback_ref', $ref)
						->addFieldToFilter('sent', 1)
						->addFieldToFilter('shipped', 0)
						->addOrder('created_at', 'desc');
		
		if(count($collection) === 1)
		{
			foreach($collection as $order)
			{
				$this->_order = $order;
				
				break;
			}
		}
		else
		{
			throw new Exception('Invalid ref');
		}
	}
	
	public function update($status, $statusName)
	{
		switch($status)
		{
			case Gateway3D_PersonaliseIt_Model_Sl_Data_Order::STATUS_DISPATCHED:				
			case Gateway3D_PersonaliseIt_Model_Sl_Data_Order::STATUS_SUPPLIER_DISPATCHED_NOTIFIED:
				$this->_ship();
				break;
			
			default:
				$this->_order->addStatusHistoryComment("Status callback: {$statusName} ({$status})");
				$this->_order->save();
				break;
		}
		
		return true;
	}
	
	private function _ship()
	{
		if($this->_order->canShip())
		{		
			$qtys = array();

			foreach($this->_order->getAllItems() as $item)
			{
				$qtys[$item->getId()] = $item->getQtyToShip();
			}

			$shipment = Mage::getModel('sales/service_order', $this->_order)->prepareShipment($qtys);

			$shipment->register();

			$shipment->setEmailSent(true);

			$shipment->getOrder()->setCustomerNoteNotify(true);

			$shipment->getOrder()->setIsInProcess(true);

			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($shipment)
				->addObject($shipment->getOrder())
				->save();

			$shipment->sendEmail(true, '');
			
			$this->_order->shipped = true;
			$this->_order->save();
		}
	}
}