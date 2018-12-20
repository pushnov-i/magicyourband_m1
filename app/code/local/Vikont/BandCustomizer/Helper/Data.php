<?php

class Vikont_BandCustomizer_Helper_Data extends Mage_Core_Helper_Data
{
	// the name for the attribute used to set location of the background for regular product
	const ATTRIBUTE_CODE_DEFAULT_BACKGROUND_IMAGE_URL = 'bgimage';

	// path at system settings to store the template product SKU
	const TEMPLATE_PRODUCT_SKU_PATH = 'bandcustomizer/general/template_sku';

	// this is used to pass bg image location to customized product's iframe
	const BACKGROUND_IMAGE_URL_PARAM_NAME = 'bgimage';

	
}