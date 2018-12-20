<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
 
class Gateway3D_PersonaliseIt_ProductController
	extends Mage_Core_Controller_Front_Action
{
	/**
	 * /personalise-it/product/epa/
	 */
	public function epaAction()
	{
		$model = new Gateway3D_PersonaliseIt_Model_Product_Epa();
		
		$sku = $this->getRequest()->getParam('sku', '');
		$id = $this->getRequest()->getParam('id', '');		
		$callback = $this->getRequest()->getParam('callback', '');
		$callback = preg_replace("/[^0-9a-zA-Z\$_]/", "", $callback);
		
		//
		try
		{
			$model->load($id, $sku);
			$data = $model->getData();
		}
		catch(Exception $e)
		{
			$data = array(
				'error'	=> array(
					'message'	=> $e->getMessage()
				)
			);
		}
		
		//
		$json = Zend_Json::encode($data);
		$jsonp = "{$callback}({$json})";
		
		$this->getResponse()->clearAllHeaders();
		$this->getResponse()->setHeader('Content-Length', strlen($jsonp));
		$this->getResponse()->setHeader('Content-Type', 'text/plain');
		$this->getResponse()->sendHeaders();
		
		echo $jsonp;
		
		exit;
	}
	
	public function ratingAction()
	{
		$model = new Gateway3D_PersonaliseIt_Model_Product_Rating();
		
		$sku = $this->getRequest()->getParam('sku', '');
		$id = $this->getRequest()->getParam('id', '');		
		$rating = $this->getRequest()->getParam('rating', 0);
		$callback = $this->getRequest()->getParam('callback', '');
		$callback = preg_replace("/[^0-9a-zA-Z\$_]/", "", $callback);
		
		//
		try
		{
			$model->load($id, $sku);
			$data = $model->setRating($rating);
		}
		catch(Exception $e)
		{
			$data = array(
				'error'	=> array(
					'message'	=> $e->getMessage()
				)
			);
		}
		
		//
		$json = Zend_Json::encode($data);
		$jsonp = "{$callback}({$json})";
		
		$this->getResponse()->clearAllHeaders();
		$this->getResponse()->setHeader('Content-Length', strlen($jsonp));
		$this->getResponse()->setHeader('Content-Type', 'text/plain');
		$this->getResponse()->sendHeaders();
		
		echo $jsonp;
		
		exit;
	}
	
	public function callbackAction()
	{
		$data = $this->_getData();
		$model = new Gateway3D_PersonaliseIt_Model_Product_Callback($data);
		
		$sku = $this->getRequest()->getParam('sku', '');
		$id = $this->getRequest()->getParam('id', '');
		
		// Fallback to SKU specified in data object
		if(!$id && !$sku && isset($data['sku']))
		{
			$sku = $data['sku'];
		}
		
		// Load the model
		$model->load($id, $sku);
		
		//
		$this->loadLayout();
		
		$block = $this->getLayout()->getBlock('root');
		
		//
		try
		{
			$model->process();
		
			$block->setData('message', __('Please wait while we download your custom design to the shopping basket...'));
			$block->setData('redirect', Mage::getUrl('checkout/cart'));
			
		}
		catch(Exception $e)
		{
			$block->setData('message', $e->getMessage());
			$block->setData('redirect', $model->getProduct()->getProductUrl());
		}
		
		//
		
		$this->renderLayout();
	}
	
	private function _getData()
	{
		$type = $_SERVER['CONTENT_TYPE'];
		
		switch($type)
		{
			case 'application/json':
				$json = $this->getRequest()->getRawBody();
				break;
			
			case 'application/x-www-form-urlencoded':
				$json = $_POST['data'];
				break;
			
			default:
				throw new Exception('Invalid content-type');
		}
		
		$data = Zend_Json::decode($json);
		
		return $data;
	}
}
