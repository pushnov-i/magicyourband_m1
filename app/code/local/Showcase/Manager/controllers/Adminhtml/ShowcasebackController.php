<?php
class Showcase_Manager_Adminhtml_ShowcasebackController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{	
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("New Showcase Manager"));
	   $this->renderLayout();
    }
	
	// public function editAction()
	// {			    
			// $this->_title($this->__("Showcase"));
			// $this->_title($this->__("Showcase"));
			// $this->_title($this->__("Edit Item"));
			
			// $id = $this->getRequest()->getParam("id");
			// $model = Mage::getModel("showcase/showcase")->load($id);
			// if ($model->getId()) {
				// Mage::register("showcase_data", $model);
				// $this->loadLayout();
				// $this->_setActiveMenu("showcase/showcase");
				// $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Showcase Manager"), Mage::helper("adminhtml")->__("Showcase Manager"));
				// $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Showcase Description"), Mage::helper("adminhtml")->__("Showcase Description"));
				// $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
				// $this->_addContent($this->getLayout()->createBlock("showcaseback/adminhtml_showcaseback_edit"))->_addLeft($this->getLayout()->createBlock("showcaseback/adminhtml_showcaseback_edit_tabs"));
				// $this->renderLayout();
			// } 
			// else {
				// Mage::getSingleton("adminhtml/session")->addError(Mage::helper("showcase")->__("Item does not exist."));
				// $this->_redirect("*/*/");
			// }
	// }
	public function saveAction()
	{

		$post_data=$this->getRequest()->getPost();
			if ($post_data) {
				try {
					$model = Mage::getModel("catalog/product")
					->addData($post_data)
					->setId($this->getRequest()->getParam("id"))
					->save();
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Showcase was successfully saved"));
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