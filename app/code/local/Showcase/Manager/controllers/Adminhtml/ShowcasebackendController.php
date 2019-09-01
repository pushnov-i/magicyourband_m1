<?php


class Showcase_Manager_Adminhtml_ShowcasebackendController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{		return Mage::getSingleton('admin/session')->isAllowed('showcase/showcasebackend');
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("New Showcase Manager"));
	   $this->renderLayout();
    }
	
	
	public function saveAction()
    { 
		$post_data=$this->getRequest()->getPost();
		if ($post_data) {
			try {
				$model = Mage::getModel("showcase/showcase")
				->addData($post_data)
				->setId($this->getRequest()->getParam("id"))
				->save();
				if($post_data['is_active']){
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Design Successfully Added to Showcase"));
				} else {
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Design Successfully Removed from Showcase"));
				}
				Mage::getSingleton("adminhtml/session")->setShowcaseData(false);

				if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("id" => $model->getId()));
					return;
				}
				$this->_redirect("*/*/");
				return;
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				Mage::getSingleton("adminhtml/session")->setShowcaseData($this->getRequest()->getPost());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
			return;
			}
		}
		$this->_redirect("*/*/");
    }
}