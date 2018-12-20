<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
 
class Gateway3D_PersonaliseIt_ProductsController
	extends Mage_Core_Controller_Front_Action
{
	/**
	 * /product-selector/products/
	 */
	public function indexAction()
	{		
		$this->loadLayout();
		$this->renderLayout();
	}
}
