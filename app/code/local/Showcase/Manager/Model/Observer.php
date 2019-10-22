<?php
class Showcase_Manager_Model_Observer 
{

    public function cartProductAddAfter($observer)
    {
		
		$_item = $observer->getEvent()->getItem();
		$product = $_item->getProduct();
		
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = 'SELECT `value` FROM `sales_flat_quote_item_option` WHERE `item_id`='.$_item->getId().' AND `code`="info_buyRequest"';
		$results = $readConnection->fetchAll($query);
		$refid='';
		$unserialized = array();
		if(!empty($results)){
			foreach($results as $each){
				$unserialized[] = unserialize($each['value']);
				
			}
			$i=0;

			foreach ($unserialized as $each) {
				$i=0;
				foreach($each['options'] as $e){
					if($i==1){$refid=$e;}
					$i++;
				}
				
			}
			unset($resource,$readConnection);
			
		$isVisibleProduct = $product->isVisibleInSiteVisibility();
		$canApplyMsrp = Mage::helper('catalog')->canApplyMsrp($_item->getProduct(), Mage_Catalog_Model_Product_Attribute_Source_Msrp_Type::TYPE_BEFORE_ORDER_CONFIRM);
		$productOptions = $product->getOptions();
		$checkoutBlockRenderar = new Mage_Checkout_Block_Cart_Item_Renderer();
		$checkoutBlockRenderar->setItem($_item);

		$options = $checkoutBlockRenderar->getOptionList();

		$productThumbnail = false;
		if ($productOptions && $options) {
			foreach($options as $option) {
				if (	isset($productOptions[$option['option_id']])
					&&	$productOptions[$option['option_id']]->getSku() == "thumburl"
				) {
					$productThumbnail = $option['value'];
					break;
				}
			}
		}
		if ($productThumbnail) {
			$thumbnailImageUrl = $productThumbnail;
			$fullImageUrl = $productThumbnail;
		} else {
			$p = Mage::getModel('catalog/product')->load($product->getId());
			$thumbnailImageUrl = (string) Mage::helper('catalog/image')->init($p, 'thumbnail')->resize(75);
			$fullImageUrl = (string) Mage::helper('catalog/image')->init($p, 'image')->resize(500);
		}
		
		if(!empty($refid)){	
			$loadProductCollection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('pjnumber',$refid);
		}		
		
		if(!empty($refid) && count($loadProductCollection)<1){	
			 
			 
			 
			  $orgProduct = Mage::getModel('catalog/product')->load($product->getId());
			  
			/*  $categoryIds = $orgProduct->getCategoryIds();	*/

            $categoryIds=array(105);
			 Mage::log("cat ids".json_encode($orgProduct->getCategoryIds()), null, 'add-to-cart-observer.log', true);
				 
			try{
			
				$new = Mage::getModel('catalog/product');
				$new->setData($orgProduct->getData());
				$new->setName($orgProduct->getName());	
				$new->setPjnumber($refid);		
				if (Mage::getSingleton('customer/session')->isLoggedIn())
				{	
					$customer = Mage::getSingleton('customer/session')->getCustomer();	
					$new->setDesignedBy($customer->getName());		
				} 
				else 
				{	
				$new->setDesignedBy('Guest User');	
				}			
				$new->setAddToShowcase(0);	
				$new->setIsThisDesign(1);
				$new->setId(null);
				$new->setUrlPath($orgProduct->getData('url_path'));

				   
				$new->setSku($orgProduct->getSku().strtotime('now'));

				$new->setAttributeSetId(10);

				$new->setCategoryIds($categoryIds);

				Mage::log('Image url from showcase ' .$fullImageUrl, null, 'add-to-cart-observer.log', true);
				
				$image_url  = $fullImageUrl;
				$image_type = substr(strrchr($image_url,"."),1);
				
				Mage::log('Image type ' .$image_type, null, 'add-to-cart-observer.log', true);
				$filename   = $orgProduct->getSku().strtotime('now').'.'.$image_type;
				$filepath   = Mage::getBaseDir('media') . DS . 'import'. DS . $filename;
				file_put_contents($filepath, file_get_contents(trim($image_url)));
				$mediaAttribute = array (
						'thumbnail',
						'small_image',
						'image'
				);
				
				$new->addImageToMediaGallery($filepath, $mediaAttribute, false, false);
				$new->setIsMassupdate(true)->setExcludeUrlRewrite(true);
				Mage::log('File Path ' . $filepath . ', image url ' . $image_url, null, 'add-to-cart-observer.log', true);

                /**
                 * set product as disabled MAG-2
                 * by WebMeridian (c) 2019 all right reserved
                 */
                $new->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);

                /**
                 * set product as disabled MAG-2
                 * by WebMeridian (c) 2019 all right reserved
                 */
				$new->save();
				
				$stockItem = Mage::getModel('cataloginventory/stock_item');
				$stockItem->assignProduct($new);
				$stockItem->setData('is_in_stock', 1);
				$stockItem->setData('stock_id', 1);
				$stockItem->setData('store_id', 1);
				$stockItem->setData('manage_stock', 0);
				$stockItem->setData('use_config_manage_stock', 1);
				$stockItem->setData('min_sale_qty', 1);
				$stockItem->setData('use_config_min_sale_qty', 0);
				$stockItem->setData('max_sale_qty', 1000);
				$stockItem->setData('use_config_max_sale_qty', 0);
				$stockItem->setData('qty', 10000);
				$stockItem->save();

			}catch(Exception $e){
				 Mage::log($e->getMessage(), null, 'add-to-cart-observer-error.log', true);
			}

			 /*$showcaseData['product_id']=$product->getId();
			 $showcaseData['product_name']=$product->getName();
			 $showcaseData['pjnumber']=$refid;	
			 $showcaseData['image_url']=$fullImageUrl;
			 if (Mage::getSingleton('customer/session')->isLoggedIn()) {
				$customer = Mage::getSingleton('customer/session')->getCustomer();
				$showcaseData['customer_name']=$customer->getName();
			 } else {
				 $showcaseData['customer_name']='Guest User';
			 }
			 $showcaseData['link']=$product->getProductUrl();

			 $showcaseCollection = Mage::getModel('showcase/showcase')->getCollection()->addFieldToFilter('pjnumber',$showcaseData['pjnumber']);

			 $showcaseselction= $showcaseCollection->getData();
			 if(empty($showcaseselction)){
				 $model = Mage::getModel("showcase/showcase")
							->addData($showcaseData)
							->save();
			 } else {
			 }*/
		
		
		} else {
		}
		}

    }

}