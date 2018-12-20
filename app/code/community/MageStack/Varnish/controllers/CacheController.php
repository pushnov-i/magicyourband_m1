<?php

/*
* @category    Module
* @package     MageStack_Varnish
*/

require_once('Mage/Adminhtml/controllers/CacheController.php');
class MageStack_Varnish_CacheController extends Mage_Adminhtml_CacheController {

    /**
     * Overwrites Mage_Adminhtml_CacheController massRefreshAction
     */
    public function massRefreshAction(){
        // Handle varnish type
        $types = $this->getRequest()->getParam('types');

        if (Mage::app()->useCache('varnish') ) {
            if( (is_array($types) && in_array('varnish', $types)) || $types="varnish") {
                Mage::helper('varnish')->purgeAll();
                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("Varnish cache type purged ."));
            }
        }

        // Allow parrent handle core cache types
        parent::massRefreshAction();
    }
}
