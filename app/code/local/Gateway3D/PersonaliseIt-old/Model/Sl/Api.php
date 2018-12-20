<?php

class Gateway3D_PersonaliseIt_Model_Sl_Api
{
	private $_url;
	private $_key;
	
	public function __construct($apiUrl, $apiKey)
	{
		$this->_url = $apiUrl;
		$this->_key = $apiKey;
	}
	
	public function create(Gateway3D_PersonaliseIt_Model_Sl_Data_Order $order)
	{
		$response = $this->_curl($this->_getFullUrl(), Zend_Form::METHOD_POST, $order);
		
		$order->id = $response->id;
		$order->ref = $response->ref;
		
		return $order;
	}
	
	public function update(Gateway3D_PersonaliseIt_Model_Sl_Data_Order $order)
	{
		
		return $order;
	}
	
	public function retrieve(Gateway3D_PersonaliseIt_Model_Sl_Data_Order $order)
	{		
		$response = $this->_curl($this->_getFullUrl($order->id), Zend_Form::METHOD_GET);
		
		foreach($response as $key => $value)
		{
			if(isset($order->{$key}))
			{
				$order->{$key} = $response->{$key};
			}
		}
		
		return $order;
	}
	
	private function _getFullUrl($orderId = 0)
	{
		$params = array('k' => $this->_key);
		
		if($orderId)
		{
			$params['o'] = $orderId;
		}
		
		return $this->_url . "?" . http_build_query($params);
	}

	private function _curl($url, $verb, $data = null)
	{
		$ch = curl_init();
			
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER		=> true,
			CURLOPT_URL					=> $url,
			CURLOPT_CUSTOMREQUEST		=> strtoupper($verb),
			CURLOPT_SSL_VERIFYHOST		=> false,
			CURLOPT_SSL_VERIFYPEER		=> false
		));
		
		if($data)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}
		
		$response = curl_exec($ch);
		
		if($response)
		{
			$response = json_decode($response);
			
			if(isset($response->error))
			{
				throw new Gateway3D_PersonaliseIt_Model_Sl_Api_Exception($response->error->message);
			}
			else
			{
				return $response;
			}
		}
		else
		{
			throw new Gateway3D_PersonaliseIt_Model_Sl_Api_Exception(curl_error($ch));
		}
	}
}