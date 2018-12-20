<?php

class Gateway3D_PersonaliseIt_CppController
 	extends Mage_Adminhtml_Controller_Action
{
	public function importOptionsAction()
	{
		$id = $this->getRequest()->getParam('id', 0);

		$product = Mage::getModel('catalog/product')
					->load($id);
		
		try
		{
			$model = new Gateway3D_PersonaliseIt_Model_ImportOptions($product);
			$model->import();
		}
		catch(Exception $e)
		{
			$this->_getSession()->addError($e->getMessage());
		}
	}
}
