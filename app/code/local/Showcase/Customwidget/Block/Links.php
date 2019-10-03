<?php
class Showcase_Customwidget_Block_Links extends Mage_Catalog_Block_Product_List implements Mage_Widget_Block_Interface {
  
  protected function _toHtml() {
    $html = '';
	$arrayDesigns = explode(',', $custom_designs);
	$showcaseCollection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('add_to_showcase',1)->setOrder('created_at', 'desc');

    $toolbar = $this->getToolbarBlock();
    $toolbar->setCollection($showcaseCollection);
    $paginationLayout = Mage::getSingleton('core/layout');
    $pager = $paginationLayout->createBlock('page/html_pager');
    $toolbar->setChild('product_list_toolbar_pager', $pager);
	
	$count=0;
	$html .='<div class="pager-display">'.$toolbar->toHtml().'</div>';
	$html .= '<ul class="products-grid row odd">';
	foreach($showcaseCollection as $singleCollection){ 
		$_product =  Mage::getModel('catalog/product')->load($singleCollection->getId());
		$productBlock = $this->getLayout()->createBlock('catalog/product_price');
		
		$productName=$_product->getName();
		$showcaseName=$_product->getName();
		if(!empty($showcaseName)){
			$productName=$showcaseName; 
		}
		$description=$_product->getDescription();
		$customerName = $_product->getDesignedBy();
		if(empty($customerName)){
			$customerName='Guest User';
		}

		$imageUrl=Mage::helper('catalog/image')->init($_product, 'small_image')->resize(210); 
		if (empty($imageUrl)) 
		{ 
			$imageUrl = Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
		} 
		
		$html .='<li class="item first col-xs-12 col-sm-4" style="list-style: none;">
		<div class="grid_wrapper">
			<a href="'.$_product->getProductUrl().'?pj='.$_product->getPjnumber().'" title="'.$productName.'"><img src="'.$imageUrl.'" alt="'.$productName.'"></a>
			<div class="product-shop">'.$productBlock->getPriceHtml($_product); 
			
		$html .= '<h2 class="product-name"><a href="'.$_product->getProductUrl().'?pj='.$_product->getPjnumber().'" title="'.$productName.'">'.$productName.'</a></h2>
				<div class="desc_grid">Designed by '.$customerName.'</div>
				<p class="description"></p>
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

	$html .= '</ul><style>.pager-display .sorter{display:none;}</style>';
    return $html;
  }
}