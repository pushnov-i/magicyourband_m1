<?php

$installer = $this;
$installer->startSetup();

$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer', 'override_company_ref_id', array(
	'type'		=> 'varchar',
	'input'		=> 'text',
	'label'		=> 'Override Company Ref ID',
	'global'	=> 1,
	'visible'	=> 1,
	'required'	=> 0,
	'user_defined' => 1,
	'default'	=> '',
	'visible_on_front' => 0
));

if (version_compare(Mage::getVersion(), '1.6.0', '<='))
{
      $customer = Mage::getModel('customer/customer');
      $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
      $setup->addAttributeToSet('customer', $attrSetId, 'General', 'override_company_ref_id');
}

if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
    Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'override_company_ref_id')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();
}

$installer->endSetup();
