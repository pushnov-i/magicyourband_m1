<?php

$installer = $this;

$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'status_callback_ref', 'varchar(256) not null default ""');
