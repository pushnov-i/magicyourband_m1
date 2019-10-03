<?php  

class Showcase_Manager_Block_Adminhtml_Showcasebackend extends Mage_Adminhtml_Block_Template {
	private $pager;
	private $currentPage;
	public function __construct()
	{
		$this->_controller = "adminhtml_showcasebackend";
		$this->_blockGroup = "showcasebackend";
		$this->_headerText = Mage::helper("showcase")->__("New Showcase Manager");
		/*$this->_addButtonLabel = Mage::helper("showcase")->__("Add New Item");*/
		parent::__construct();
		
		$showcaseCollection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('is_this_design',1)->setOrder('entity_id', 'desc');
		$showcaseCollection->setPageSize(18);
		$curPage = $this->getRequest()->getParam('page');
		if(!empty($curPage)){
			$showcaseCollection->setCurPage($curPage);
		} 
		$this->setCollection($showcaseCollection);
				/*
		$showcaseCollection2 = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('add_to_showcase',0)->setOrder('created_at', 'desc');
		$showcaseCollection2->setPageSize(20);
		$curPage = $this->getRequest()->getParam('page');
		if(!empty($curPage)){
			$showcaseCollection2->setCurPage($curPage);
		} 
		
		$this->setCollection2($showcaseCollection2);*/

		parent::_construct();
	}
	
	protected function _prepareLayout()
    {
        parent::_prepareLayout();
       /* $pager = $this->getLayout()->createBlock('page/html_pager');
        
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;*/
    }	
		
}