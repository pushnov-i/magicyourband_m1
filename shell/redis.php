<?php

count($argc) or die('<pre>:P</pre>');
ini_set('memory_limit','1024M');
set_time_limit(0);
error_reporting(E_ALL | E_STRICT);
require_once '../app/Mage.php';
Mage::app()->getCache()->getBackend()->clean('old');

if (class_exists('Enterprise_PageCache_Model_Cache'))
  Enterprise_PageCache_Model_Cache::getCacheInstance()->getFrontend()->getBackend()->clean('old');
