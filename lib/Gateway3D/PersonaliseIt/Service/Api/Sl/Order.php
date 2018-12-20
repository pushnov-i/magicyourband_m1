<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Service_Api_Sl_Order
	extends Gateway3D_PersonaliseIt_Service_Api_Abstract
{
	protected $_key = "";
	
	public function __construct($url, $key) 
	{
		parent::__construct($url);
		
		$this->_key = $key;
	}
	
	protected function _getCreateUrl()
	{
		return "{$this->_url}?k={$this->_key}";
	}
	
	protected function _getUpdateUrl($id)
	{
		return "{$this->_url}?o={$id}&k={$this->_key}";
	}
	
	protected function _getRetrieveUrl($id)
	{
		return "{$this->_url}?o={$id}&k={$this->_key}";
	}
	
	protected function _newDto()
	{
		return new Gateway3D_PersonaliseIt_Service_Dto_Sl_Order;
	}
}

