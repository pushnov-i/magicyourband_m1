<?php

class Elsner_Multicurrency_Model_Multicurrency extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('elsner_multicurrency/multicurrency');
    }

    public function getRowByIncrementId($incrementId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('order_increment_id',$incrementId);
        $data = $collection->getFirstItem();
        return $data;
    }

    public function getRowByTransection($incrementId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('authorize_transaction_id',$incrementId);
        $data = $collection->getFirstItem();
        return $data;
    }

    public function addRow($incrementId,$currency,$discription)
    {
        $data = $this->getRowByIncrementId($incrementId)->getData();
        $setData = $this;
        $addArray = array('order_increment_id'=>$incrementId,
                          'paypal_currency_code'=>$currency,
                          'order_id'=>0,
                          'date_time'=>date('Y-m-d H:i:s'),
                          'discription'=>$discription);
        $setData->setData($addArray);
        if(empty($data) !== true){
            $setData->setId($data['multicurrency_id']);
        }
        $setData->save();
        return $setData;
    }
}