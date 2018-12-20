<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
class Gateway3D_PersonaliseIt_Model_Resource_Eav_Mysql4_Setup 
	extends Mage_Eav_Model_Entity_Setup
{
	public function getDefaultEntities(){
		return array(
			'catalog_product' => array(
				'entity_model'      => 'catalog/product',
				'attribute_model'   => 'catalog/resource_eav_attribute',
				'table'             => 'catalog/product',
				'additional_attribute_table' => 'catalog/eav_attribute',
				'entity_attribute_collection' => 'catalog/product_attribute_collection',
				'attributes'        => array(
					
					'personaliseit_gl_iframe_url'=> array(
						'section'			=>'general',
						'group'             => 'Gateway3D Personalise-iT',
						'label'             => 'WebGL iFrame URL',
						'type'              => 'text',
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
					),
					
					'personaliseit_fl_iframe_url'=> array(
						'section'			=>'general',
						'group'             => 'Gateway3D Personalise-iT',
						'label'             => 'Flash iFrame URL',
						'type'              => 'text',
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
					),
					
					'personaliseit_m_iframe_url'=>array(
						'section'			=>'general',
						'group'             => 'Gateway3D Personalise-iT',
						'label'             => 'Default Mobile iFrame URL',
						'type'              => 'text',
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
					),
					
					'personaliseit_iframe_url'=>array(
						'section'=>'general',
						'group'             => 'Gateway3D Personalise-iT',
						'label'             => 'Default iFrame URL',
						'type'              => 'text',
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
					),
					
					'easypromo3d_url'=>array(
						'section'=>'general',
						'group'             => 'Gateway3D Personalise-iT',
						'label'             => 'Legacy Easypromo3D URL',
						'type'              => 'text',
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
					),
					
					'personaliseit_company_ref_id'=>array(
						'section'=>'general',
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
					),
					
					'personaliseit_pod_ref'=>array(
						'section'=>'general',
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
					),
					
					'personaliseit_is_plain' => array(
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
					),
				),
			),
		);
	}
}

