<?php
class Showcase_Customwidget_Block_Links extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface {
  
  
	
  protected function _toHtml() {
    $html = '';
	 $custom_designs = $this->getData('custom_designs');
      
    if (empty($custom_designs)) {
      return $html;
    }
	
	$arrayDesigns = explode(',', $custom_designs);
 
	$showcaseCollection = Mage::getModel('showcase/showcase')->getCollection() ->addFieldToFilter('id', array('in'=> $arrayDesigns));
	$count=0;
	$html .=$link_options;
	$html .= '<ul class="products-grid row odd">';
	foreach($showcaseCollection as $singleCollection){ 
		$_product =  Mage::getModel('catalog/product')->load($singleCollection->getProductId());
		$productBlock = $this->getLayout()->createBlock('catalog/product_price');
    
		$html .= '<li class="item first col-xs-12 col-sm-4" style="list-style: none;">
			<div class="grid_wrapper">
				<a href="'.$singleCollection->getLink().'?pj='.$singleCollection->getPjnumber().' title="'.$singleCollection->getProductName().'"><img src="'.$singleCollection->getImageUrl().'" alt="'.$singleCollection->getProductName().'"></a>
				<div class="product-shop">'.$productBlock->getPriceHtml($_product); 
				
		$html .= '<h2 class="product-name"><a href="'.$singleCollection->getLink().'?pj='.$singleCollection->getPjnumber().'" title="'.$singleCollection->getProductName().'">'.$singleCollection->getProductName().'</a></h2>
					<div class="desc_grid">Designed by '.$singleCollection->getCustomerName().'</div>
				</div>
				<div class="label-product">             
				</div>
			</div>
		</li>';
		$count++;
		if($count%3==0){ 
			$html .= '</ul><ul class="products-grid row odd">';
		}
	}

	$html .= '</ul>';
    return $html;
  }
}