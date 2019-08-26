<?php
class Showcase_Manager_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("showcase"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("showcase", array(
                "label" => $this->__("Customer Designs"),
                "title" => $this->__("Customer Designs")
		   ));

      $this->renderLayout(); 
	  
    }
	
	public function saveshowcaseAction() {

		if(!empty($_REQUEST['product_id']) &&
		!empty($_REQUEST['product_name']) &&
		!empty($_REQUEST['pj_number']) &&
		!empty($_REQUEST['image_url']) &&
		!empty($_REQUEST['customer_name']) &&
		!empty($_REQUEST['product_url'])){
			 
			$showcaseData['product_id']=$_REQUEST['product_id'];
			$showcaseData['product_name']=$_REQUEST['product_name'];
			$showcaseData['pjnumber']=$_REQUEST['pj_number'];
			$showcaseData['image_url']=$_REQUEST['image_url'];
			$showcaseData['customer_name']=$_REQUEST['customer_name'];
			$showcaseData['link']=$_REQUEST['product_url'];
			$showcaseData['customer_id']=$_REQUEST['customer_id'];

			$showcaseCollection = Mage::getModel('showcase/showcase')->getCollection()->addFieldToFilter('pjnumber',$showcaseData['pjnumber']);

			$showcaseselction= $showcaseCollection->getData();
			if(empty($showcaseselction)){
				$model = Mage::getModel("showcase/showcase")
						->addData($showcaseData)
						->save();

				Mage::getSingleton('core/session')->addSuccess(Mage::helper("showcase")->__("Design Submitted to showcase"));
			} else {
				Mage::getSingleton("core/session")->addError(Mage::helper("showcase")->__("This Design Already Submitted to showcase"));
			}
		} else {
			Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Something went wrong, please try again later"));
		}
		$this->_redirect('checkout/cart');
	}
}