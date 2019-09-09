<?php
class Showcase_Manager_Model_Observer 
{
  /*  public function cartProductUpdateAfter($observer)
    {
        $this->cartProductAddAfter($observer);
    }*/

    public function cartProductAddAfter($observer)
    {
        
        $_item = $observer->getEvent()->getItem();
		$product = $_item->getProduct();
		
        /*$quote = $_item->getQuote();
        $quoteItems = $quote->getItems();*/		
		
		
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
			 
			 $showcaseData['product_id']=$product->getId();
			 $showcaseData['product_name']=$product->getName();
			 $showcaseData['pjnumber']=$refid;
			 $showcaseData['image_url']=$fullImageUrl;
			 
			 if (Mage::getSingleton('customer/session')->isLoggedIn()) {
				$customer = Mage::getSingleton('customer/session')->getCustomer();
				$showcaseData['customer_name']=$customer->getName();
			 }
			 $showcaseData['link']=$product->getProductUrl();
			 /*$showcaseData['customer_id']=$_REQUEST['customer_id'];*/

			 $showcaseCollection = Mage::getModel('showcase/showcase')->getCollection()->addFieldToFilter('pjnumber',$showcaseData['pjnumber']);

			 $showcaseselction= $showcaseCollection->getData();
			 if(empty($showcaseselction)){
				 $model = Mage::getModel("showcase/showcase")
							->addData($showcaseData)
							->save();
			 } else {
			 }
		} else {
		}
		}

        /* Detect Product ID and Qty programmatically 
        $idToAdd = "ANY PRODUCT ID";
        $qty = 1;

        $productToAdd = Mage::getModel('catalog/product');
        /* @var $productToAdd Mage_Catalog_Model_Product 
        $productToAdd->load($idToAdd);

       /* $this->_addProductToCart($productToAdd, $qty);*/
    }

   /* protected function _addProductToCart($product, $qty)
    {
        $cart = Mage::getSingleton('checkout/cart');
        if ($product->getId()) {
            $cart->addProduct($product, $qty);
            return true;
        }
        return false;
    }*/
}