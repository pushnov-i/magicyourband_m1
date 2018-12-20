<?php

$content = "<p>Free shipping this week</p>";

$cmsBlock = array(
    'title'         => 'top-notification-home',
    'identifier'    => 'top-notification-home',
    'content'       => $content,
    'is_active'     => 1,
    'stores'        => 0
);

Mage::getModel('cms/block')->setData($cmsBlock)->save();
