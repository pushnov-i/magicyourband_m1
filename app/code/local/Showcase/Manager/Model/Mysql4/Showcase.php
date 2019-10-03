<?php
class Showcase_Manager_Model_Mysql4_Showcase extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("showcase/showcase", "id");
    }
}