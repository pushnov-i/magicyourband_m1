<?php
class Showcase_Customwidget_Model_Options {
  public function toOptionArray() {
	  
	$showcaseCollection = Mage::getModel('showcase/showcase')->getCollection()->addFieldToFilter('is_active', 1);
	$showcaseArray = array();
	$arrayofShowcaseArray = array();
	foreach($showcaseCollection as $singleCollection){			
			$showcaseArray['value']=$singleCollection->getId();
			$showcaseArray['label']=$singleCollection->getId().' '.$singleCollection->getProductName();
			$arrayofShowcaseArray[]=$showcaseArray;
	}
    return $arrayofShowcaseArray;
  }
}