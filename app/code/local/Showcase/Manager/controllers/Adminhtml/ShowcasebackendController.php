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
	   $this->_title($this->__("showcase"));
	   $this->renderLayout();
    }
}