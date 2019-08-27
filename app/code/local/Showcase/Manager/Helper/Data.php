<?php
class Showcase_Manager_Helper_Data extends Mage_Core_Helper_Abstract
{
	 public function getCustomDesign()
	 {
		return $this->getData('custom_design');
	 }
}