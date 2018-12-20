<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Service_Api_P2_PrintJob
	extends Gateway3D_PersonaliseIt_Service_Api_Abstract
{	
	const OPTION_IMPLICIT = 'implicit';
	
	public function create(Gateway3D_PersonaliseIt_Service_Dto_Abstract $dto, $options = array())
	{
		$url = $this->_getCreateUrl();
		
		$data = array('options' => $options, 'print_job' => $dto->toArray());
		
		$this->_initClient($url, Zend_Http_Client::POST, $data);
		
		return $this->_marshalToDto($dto, $this->_getResponse());
	}
	
	protected function _getCreateUrl()
	{
		return $this->_url;
	}
	
	protected function _getUpdateUrl($id)
	{
		$this->_notSupported();
	}
	
	protected function _getRetrieveUrl($id)
	{
		$this->_notSupported();
	}
	
	protected function _newDto()
	{
		return new Gateway3D_PersonaliseIt_Service_Dto_P2_PrintJob;
	}
	
	private function _notSupported()
	{
		Mage::throwException("Operation not supported");
	}
}

