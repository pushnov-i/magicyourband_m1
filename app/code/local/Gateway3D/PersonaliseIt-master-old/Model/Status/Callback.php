<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
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
	
	public function update(array $request)
	{
		if(isset($request['new_shipments']) && count($request['new_shipments']))
		{
			$this->_doShipments($request['new_shipments']);
		}

		if(isset($request['new_cancellations']) && count($request['new_cancellations']))
		{
			$this->_doCancellations($request['new_cancellations']);
		}

		switch($request['status'])
		{
			case Gateway3D_PersonaliseIt_Service_Dto_Sl_Order::STATUS_DISPATCHED:				
			case Gateway3D_PersonaliseIt_Service_Dto_Sl_Order::STATUS_SUPPLIER_DISPATCHED_NOTIFIED:
				$this->_ship($request['shipping_tracking']);
				break;
			
			default:
				$this->_order->addStatusHistoryComment("Status callback: {$request['status_name']} ({$request['status']})");
				$this->_order->save();
				break;
		}
		
		return true;
	}

	private function _doCancellations(array $cancellations)
	{
		foreach($cancellations as $cancellation)
		{
			$qtys = array();

			foreach($cancellation['items'] as $item)
			{
				list($mageOrderId, $mageItemId) = explode('-', $item['external_ref']);

				$qtys[$mageItemId] = $item['quantity'];
			}

			$this->_doCancellation($qtys);
		}
	}

	private function _doShipments(array $shipments)
	{
		foreach($shipments as $shipment)
		{
			$qtys = array();

			foreach($shipment['items'] as $item)
			{
				list($mageOrderId, $mageItemId) = explode('-', $item['external_ref']);

				$qtys[$mageItemId] = $item['quantity'];
			}

			$this->_doShipment($qtys, $shipment['tracking']);
		}
	}

	private function _doCancellation($qtys)
	{
		$creditMemo = Mage::getModel('sales/service_order', $this->_order)
						->prepareCreditmemo(array(
							'qtys' => $qtys,
							'shipping_amount' => 0
						));

		$creditMemo
			->setRefundRequested(true)
			->setOfflineRequested(false)
			->register();

		Mage::getModel('core/resource_transaction')
			->addObject($creditMemo)
			->addObject($creditMemo->getOrder())
			->save();
	}

	protected function _doShipment($qtys, $tracking)
	{
		if($this->_order->canShip())
		{
			$shipment = Mage::getModel('sales/service_order', $this->_order)->prepareShipment($qtys);

			$shipment->register();

			$shipment->setEmailSent(true);
            
			$arrTracking = array(
				'carrier_code' => '',
				'title' => 'Custom tracking number',
				'number' => $tracking,
			);
 
			$track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
			$shipment->addTrack($track);

			$shipment->getOrder()->setCustomerNoteNotify(true);

			$shipment->getOrder()->setIsInProcess(true);

			Mage::getModel('core/resource_transaction')
				->addObject($shipment)
				->addObject($shipment->getOrder())
				->save();

			$shipment->sendEmail(true, '');

			if(!$this->_order->canShip())
			{
				$this->_order->shipped = true;
				$this->_order->save();
			}				
		}
	}
	
	private function _ship($tracking)
	{
		$qtys = array();

		foreach($this->_order->getAllItems() as $item)
		{
			$qtys[$item->getId()] = $item->getQtyToShip();
		}

		$this->_doShipment($qtys, $tracking);
	}
}
