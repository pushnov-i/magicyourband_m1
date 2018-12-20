<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
 
class Gateway3D_PersonaliseIt_StatusController
	extends Mage_Core_Controller_Front_Action
{
	/**
	 * /personalise-it/status/callback/
	 */
	public function callbackAction()
	{
		$model = new Gateway3D_PersonaliseIt_Model_Status_Callback();
		
		$ref = $this->getRequest()->getParam('ref', '');
		
		$request = Zend_Json::decode($this->getRequest()->getRawBody());
		$status = @$request['status'];
		$statusName = @$request['status_name'];
		
		//
		try
		{
			$model->load($ref);
			$response = $model->update($status, $statusName);
		}
		catch(Exception $e)
		{
			$response = array(
				'error'	=> array(
					'message'	=> $e->getMessage()
				)
			);
		}
		
		//
		$json = Zend_Json::encode($response);		
		
		$this->getResponse()->clearAllHeaders();		
		$this->getResponse()->setHeader('Content-Type', 'application/json');
		$this->getResponse()->sendHeaders();
		
		echo $json;
		
		exit;
	}
}
