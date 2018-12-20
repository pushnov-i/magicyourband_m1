<?php

    
$installer = $this;

$installer->startSetup();

    $sql = "ALTER TABLE {$this->getTable('mh_notification')}
CHANGE COLUMN `content` `content_notification` text NOT NULL default ''";
    $installer->run($sql);
    $installer->endSetup();