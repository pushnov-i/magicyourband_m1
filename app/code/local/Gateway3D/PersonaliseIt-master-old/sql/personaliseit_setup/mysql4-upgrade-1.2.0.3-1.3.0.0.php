<?php

$installer = $this;

$installer->addAttribute('catalog_product', 'personaliseit_is_plain', array(
	'section'			=> 'general',
	'group'             => 'Gateway3D Personalise-iT',
	'label'             => 'Is Plain?',
	'type'              => 'int',
	'input'             => 'boolean',
	'default'           => '',
	'class'             => '',
	'backend'           => '',
	'frontend'          => '',
	'source'            => 'eav/entity_attribute_source_table',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible'           => true,
	'required'          => false,
	'user_defined'      => false,
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'visible_in_advanced_search' => false,
	'unique'            => false
));
