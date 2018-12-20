<?php

$installer = $this;

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$attributes = array(
	'personaliseit_gl_iframe_url',
	'personaliseit_fl_iframe_url',
	'personaliseit_m_iframe_url',
	'personaliseit_iframe_url',
	'easypromo3d_url',
);

foreach($attributes as $attribute)
{
	$idAttributeOldSelect = $this->getAttribute($entityTypeId, $attribute, 'attribute_id');
	
	$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, 'backend_type', 'text');
}