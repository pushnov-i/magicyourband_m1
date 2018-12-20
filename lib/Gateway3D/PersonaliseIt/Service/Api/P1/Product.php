<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Service_Api_P1_Product
	extends Gateway3D_PersonaliseIt_Service_Api_Abstract
{	
	protected function _getCreateUrl()
	{
		$this->_notSupported();
	}
	
	protected function _getUpdateUrl($id)
	{
		$this->_notSupported();
	}
	
	protected function _getRetrieveUrl($id)
	{
		return "{$this->_url}/?id={$id}";
	}
	
	protected function _newDto()
	{
		return new Gateway3D_PersonaliseIt_Service_Dto_P1_Product;
	}
	
	private function _notSupported()
	{
		Mage::throwException("Operation not supported");
	}
}

