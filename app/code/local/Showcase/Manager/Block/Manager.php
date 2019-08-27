<?php
class Showcase_Manager_Block_Manager
 extends Mage_Core_Block_Template
 implements Mage_Widget_Block_Interface
{
  protected function _toHtml()
  {
	$showcasecollection = Mage::getModel('catalogsearch/query')
			   ->getResourceCollection()
			   ->setOrder('popularity', 'desc');
   $showcasecollection->getSelect()->limit(3,0);
   $html  = '<div id="widget-showcase-container">' ;
   $html .= '<div class="widget-showcase-title">Showcase List</div>';
  foreach($showcasecollection as $showcase){
	 $html .= '<div class="widget-showcase-text">' . $search->query_text . "</div>";
   }
   $html .= "</div>";
   return $html;
 }
};