<?php
class Showcase_Customwidget_Helper_Data extends Mage_Core_Helper_Abstract
{
	 public function getCustomDesign()
	 {
		return $this->getData('custom_design');
	 }
}