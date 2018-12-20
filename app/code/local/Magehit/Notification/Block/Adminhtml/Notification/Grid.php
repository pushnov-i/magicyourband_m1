<?php

class Magehit_Notification_Block_Adminhtml_Notification_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('notificationGrid');
      $this->setDefaultSort('notification_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('notification/notification')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('notification_id', array(
          'header'    => Mage::helper('notification')->__('ID'),
          'align'     =>'right',
          'width'     => '20px',
          'index'     => 'notification_id',
      ));
      
      $this->addColumn('title', array(
          'header'    => Mage::helper('notification')->__('Title'),
          'align'     =>'',
          'width'     => '',
          'index'     => 'title',
      ));
      
      $this->addColumn('start_time', array(
          'header'    => Mage::helper('notification')->__('Start Time'),
          'align'     =>'',
          'width'     => '',
          'index'     => 'start_time',
      ));
      
      $this->addColumn('end_time', array(
          'header'    => Mage::helper('notification')->__('End Time'),
          'align'     =>'',
          'width'     => '',
          'index'     => 'end_time',
      ));
    
      $this->addColumn('status', array(
          'header'    => Mage::helper('notification')->__('Status'),
          'align'     => 'left',
          'width'     => '',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('notification')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('notification')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('notification')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('notification')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('notification_id');
        $this->getMassactionBlock()->setFormFieldName('notification');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('notification')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('notification')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('notification/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('notification')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('notification')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}