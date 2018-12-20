<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

class Gateway3D_PersonaliseIt_Helper_MessageEvent
{
	private $_id;
	
	public function getId()
	{
		if(!$this->_id)
		{
			$this->_id = md5(uniqid());
		}
		
		return $this->_id;
	}
	
	public function getOrigin($uri)
	{		
		$parsed = parse_url($uri);
		
		if(!isset($parsed['host']))
		{
			return "";
		}
		
		if(!isset($parsed['scheme']))
		{
			$parsed['scheme'] = Mage::app()->getStore()->isCurrentlySecure()
								? 'https'
								: 'http';
		}
		
		return "{$parsed['scheme']}://{$parsed['host']}";
	}
}