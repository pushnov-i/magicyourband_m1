<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('mh_notification')};
CREATE TABLE {$this->getTable('mh_notification')} (
  `notification_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `background_color` varchar(255) NOT NULL default '',
  `text_color` varchar(255) NOT NULL default '',
  `show_location` varchar(255) NOT NULL default '',
  `category_ids`  text NOT NULL default '',
  `show_product`  text NOT NULL default '',
  `store_ids` text NOT NULL default '',
  `customer_group_ids` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `start_time` datetime NULL,
  `end_time` datetime NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 