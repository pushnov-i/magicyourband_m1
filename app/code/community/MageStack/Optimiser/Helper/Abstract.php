<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Helper_Abstract extends Mage_Core_Helper_Abstract
{

    const TOP_NAV_DEPTH_CONFIG  = 'catalog/navigation/max_depth';
    const STORE_LOGGING_CONFIG  = 'dev/log/active';
    const PROFILER_CONFIG       = 'dev/debug/profiler';
    const FLAT_CATEGORY_CONFIG  = 'catalog/frontend/flat_catalog_category';
    const FLAT_PRODUCT_CONFIG   = 'catalog/frontend/flat_catalog_product';

    const SYSTEM_LOG_CLEANENA   = 'system/log/enabled';
    const SYSTEM_LOG_CLEANDAY   = 'system/log/clean_after_day';

    const CATALOG_FRONT_GRPEP   = 'catalog/frontend/grid_per_page';
    const CATALOG_FRONT_LIPEP   = 'catalog/frontend/grid_per_page';
    const CATALOG_FRONT_ALOAL   = 'catalog/frontend/list_allow_all';

    protected $_requiredCaches = array('config', 'block_html', 'layout', 'collections');
    protected $_allowedIndexes = array('cataloginventory_stock', 'catalog_product_price');

    public function getResource()
    {
        return Mage::getSingleton('core/resource');
    }

    public function getReadConnection()
    {
        return $this->getResource()->getConnection('core_read');
    }

    public function getWriteConnection()
    {
        return $this->getResource()->getConnection('core_write');
    }

    public function getConfigTableName()
    {
        return $this->getResource()->getTableName('core_config_data');
    }
}