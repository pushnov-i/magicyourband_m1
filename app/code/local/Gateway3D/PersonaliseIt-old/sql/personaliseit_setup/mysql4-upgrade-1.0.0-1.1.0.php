<?php

$installer = $this;

$installer->addAttribute('catalog_product', 'personaliseit_gl_iframe_url', array(
	'section'			=>'general',
	'group'             => 'Gateway3D Personalise-iT',
	'label'             => 'WebGL iFrame URL',
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

$installer->addAttribute('catalog_product', 'personaliseit_fl_iframe_url', array(
	'section'			=>'general',
	'group'             => 'Gateway3D Personalise-iT',
	'label'             => 'Flash iFrame URL',
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

$installer->addAttribute('catalog_product', 'easypromo3d_url', array(
	'section'			=> 'general',
	'group'             => 'Gateway3D Personalise-iT',
	'label'             => 'Legacy Easypromo3D URL',
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

$installer->addAttribute('catalog_product', 'personaliseit_company_ref_id', array(
	'section'			=>'general',
	'group'             => 'Gateway3D Personalise-iT',
	'label'             => '(Supplier) Company Ref ID',
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

$installer->addAttribute('catalog_product', 'personaliseit_pod_ref', array(
	'section'			=>'general',
	'group'             => 'Gateway3D Personalise-iT',
	'label'             => 'Print-on-Demand Sample Ref',
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

$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'sent', 'int not null default 0');
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'shipped', 'int not null default 0');
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'sl_order_id', 'int not null default 0');