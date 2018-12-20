<?php
    class Magehit_Notification_IndexController extends Mage_Core_Controller_Front_Action
    {
        public function indexAction()
        {
            if(!(Mage::helper('notification')->getEnabled()))
            {
                $this->norouteAction();

                return;
            }

            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('checkout/session');
            $this->getLayout()->getBlock('head')->setTitle($this->__('Promotions Notification'));

            $this->renderLayout();
        }
        
        public function resultAction(){
            if ($this->getRequest()->isAjax()) {
                $this->loadLayout();    
                $this->renderLayout();
                return $this;
            }    
        }
    }