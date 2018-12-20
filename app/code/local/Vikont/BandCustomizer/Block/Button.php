<?php

class Vikont_BandCustomizer_Block_Button extends Mage_Core_Block_Template
{
	protected $_customizeItUrl = null;


	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vikont/bandcustomizer/catalog/product/view/button.phtml');
	}


	protected function _toHtml()
	{
		if ($this->getCustomizeItUrl()) return parent::_toHtml();
	}


	public function getCustomizeItUrl()
	{
		if (null === $this->_customizeItUrl) {
			$product = $this->getProduct();
			if ($product) {
				$imageUrl = $product->getData(Vikont_BandCustomizer_Helper_Data::ATTRIBUTE_CODE_DEFAULT_BACKGROUND_IMAGE_URL);
				if ($imageUrl) {
					$url = $this->getTemplateProductUrl();
					$url .= ((false === strpos($url, '?')) ? '?' : '&')
							. Vikont_BandCustomizer_Helper_Data::BACKGROUND_IMAGE_URL_PARAM_NAME
							. '='
							. urlencode($imageUrl);
					$this->_customizeItUrl = $url;
				}
			}

			if (!$this->_customizeItUrl) {
				$this->_customizeItUrl = false;
			}
		}
		return $this->_customizeItUrl;
	}


	public function getProduct()
	{
		return Mage::registry('current_product');
	}


	public function getTemplateProductUrl()
	{
		$templateProductSku = Mage::getStoreConfig(Vikont_BandCustomizer_Helper_Data::TEMPLATE_PRODUCT_SKU_PATH);
		if (!$templateProductSku) return false;

		$templateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $templateProductSku);
		if (!$templateProduct->getId()) return false;

		return $templateProduct->getProductUrl();
	}

}
