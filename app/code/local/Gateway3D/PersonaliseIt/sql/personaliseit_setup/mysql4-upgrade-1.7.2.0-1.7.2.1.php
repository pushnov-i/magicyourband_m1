<?php

$installer = $this;
$installer->startSetup();

$installer->addAttribute('catalog_product', 'personaliseit_company_ref_id_2', array(
	'section'			=>'general',
	'group'             => 'Gateway3D Personalise-iT',
	'label'             => '(Supplier) Secondary Company Ref ID',
	'type'              => 'varchar',
	'input'             => 'text',
	'default'           => '',
	'class'             => '',
	'backend'           => '',
	'frontend'          => '',
	'source'            => '',
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

$installer->endSetup();
