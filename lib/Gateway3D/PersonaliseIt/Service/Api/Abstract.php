<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2015 Gateway 3D Ltd.
 */

/**
 * Abstract class for building API services.
 */
abstract class Gateway3D_PersonaliseIt_Service_Api_Abstract
{
	/**
	 * API endpoint.
	 * 
	 * @var string
	 */
	protected $_url = "";
	
	/**
	 * Http client.
	 * 
	 * @var Zend_Http_Client
	 */
	protected $_client = null;
	
	/**
	 * Http client response.
	 * 
	 * @var Zend_Http_Response
	 */
	protected $_response = null;	
	
	public function __construct($url)
	{
		$this->_url = $url;
	}
	
	/**
	 * Execute a create request on the endpoint.
	 * 
	 * @param Gateway3D_PersonaliseIt_Service_Dto $dto
	 * @param array $options
	 * @return Gateway3D_PersonaliseIt_Service_Dto
	 */
	public function create(Gateway3D_PersonaliseIt_Service_Dto_Abstract $dto, $options = array())
	{
		$url = $this->_getCreateUrl();	
		
		$this->_initClient($url, Zend_Http_Client::POST, $dto->toArray());		
		
		return $this->_marshalToDto($dto, $this->_getResponse());
	}
	
	/**
	 * Execute an update request on the endpoint.
	 * 
	 * @param Gateway3D_PersonaliseIt_Service_Dto $dto
	 * @param array $options
	 * @return Gateway3D_PersonaliseIt_Service_Dto
	 */
	public function update(Gateway3D_PersonaliseIt_Service_Dto_Abstract $dto, $options = array())
	{
		$url = $this->_getUpdateUrl($dto->getId());		
		
		$this->_initClient($url, Zend_Http_Client::PUT, $dto->toArray());
		
		return $this->_marshalToDto($dto, $this->_getResponse());
	}
	
	/**
	 * Retrieves an entity from the endpoint.
	 * 
	 * @param Gateway3D_PersonaliseIt_Service_Dto $dto
	 * @param array $options
	 * @return Gateway3D_PersonaliseIt_Service_Dto
	 */
	public function retrieve($idOrDto, $options = array())
	{
		if($idOrDto instanceof Gateway3D_PersonaliseIt_Service_Dto_Abstract)
		{
			$dto = $idOrDto;
		}
		else
		{
			$dto = $this->_newDto();
			$dto->setId($idOrDto);
		}
		
		$url = $this->_getRetrieveUrl($dto->getId());

		$this->_initClient($url, Zend_Http_Client::GET);
		
		return $this->_marshalToDto($dto, $this->_getResponse());
	}
	
	/**
	 * Builds a URL to be used for creation requests.
	 */
	abstract protected function _getCreateUrl();
	
	/**
	 * Builds a URL to be used for update requests.
	 */
	abstract protected function _getUpdateUrl($id);
	
	/**
	 * Builds a URL to be used for retrieval requests.
	 */
	abstract protected function _getRetrieveUrl($id);
	
	/**
	 * Create a new DTO specific for the current API.
	 */
	abstract protected function _newDto();
	
	/**
	 * Marshals an array into a Dto object.
	 * 
	 * @param Gateway3D_PersonaliseIt_Service_Dto $dto
	 * @param array $data
	 */
	protected function _marshalToDto(Gateway3D_PersonaliseIt_Service_Dto_Abstract $dto, array $data)
	{
		foreach($data as $k => $v)
		{
			if(property_exists($dto, $k))
			{
				$dto->{$k} = $v;
			}
		}

		return $dto;
	}
	
	/**
	 * Initialises the http client.
	 * 
	 * @param string $url
	 * @param string $method
	 * @param array $data Content of request.
	 */
	protected function _initClient($url, $method, array $data = null)
	{
		$this->_client = new Zend_Http_Client($url, array('timeout' => 60));
		$this->_client->setMethod($method);
		
		if(is_array($data))
		{
			$this->_client->setRawData(Zend_Json::encode($data));
		}
	}
	
	protected function _getResponse()
	{
		$this->_response =  $this->_client->request();

		try
		{
			$object = Zend_Json::decode($this->_response->getBody());
		}
		catch(Zend_Json_Exception $e)
		{
			Mage::throwException("Invalid JSON: {$this->_response->getBody()}");
		}

		if($this->_response->isError())
		{
			$message = $object['error']['message'];
			Mage::throwException($message);
		}
		else
		{
			return $object;
		}
	}
}
