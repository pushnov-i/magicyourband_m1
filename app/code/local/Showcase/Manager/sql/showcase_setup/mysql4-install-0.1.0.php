<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `showcase_manager` (
        `id` int(13) NOT NULL AUTO_INCREMENT,
        `name` char(255) NOT NULL,
		`product_name` char(255) NOT NULL,
		`pjnumber` char(255) NOT NULL,
        `description` text,
		`product_id` int(13),
		`customer_name` char(255) NOT NULL,
		`link` text,
		`image_url` text,
        `is_active` tinyint(1) DEFAULT '0',
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 