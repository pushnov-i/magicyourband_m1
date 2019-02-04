<?php
/**
* @author XJ Team
* @copyright Copyright (c) 2013 XhtmlJunkies.  <support@xhtmljunkies.com> / All rights reserved.
* @package XJ_Swatchplus
*/
$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('elsner_multicurrency/multicurrency')};
CREATE TABLE {$this->getTable('elsner_multicurrency/multicurrency')} (
  `multicurrency_id` int(11) unsigned NOT NULL auto_increment,
  `order_increment_id` varchar(300) NOT NULL,
  `paypal_currency_code` varchar(300) NOT NULL,
  `authorize_transaction_id` varchar(300) NOT NULL,
  `order_id` int(11) NOT NULL,
  `date_time` datetime DEFAULT NULL,
  `discription` varchar(500) NOT NULL,
  PRIMARY KEY (`multicurrency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();