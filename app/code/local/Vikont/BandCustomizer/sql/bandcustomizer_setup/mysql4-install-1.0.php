<?php

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, Vikont_BandCustomizer_Helper_Data::ATTRIBUTE_CODE_DEFAULT_BACKGROUND_IMAGE_URL, [
		'group'			=> 'General',
		'type'			=> 'varchar',
		'label'			=> 'Background Image URL',
		'input'			=> 'text',
		'visible'		=> true,
		'required'		=> false,
		'visible_on_front' => false,
		'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'note'			=> '',
		'apply_to'		=> 'simple,virtual,bundle,downloadable',

	]);

$this->startSetup();
$this->run("");
$this->endSetup();
