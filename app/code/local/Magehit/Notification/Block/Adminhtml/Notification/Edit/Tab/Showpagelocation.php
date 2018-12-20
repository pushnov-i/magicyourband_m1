<?php
class Magehit_Notification_Block_Adminhtml_Notification_Edit_Tab_Showpagelocation extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()

    {
        return Mage::helper('notification')->__('Apply to page');
    }
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()

    {
        return Mage::helper('notification')->__('Apply to page');
    }
    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()

    {
        return true;
    }
    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()

    {
        return false;
    }
    protected function _prepareForm()
    {
        $model = Mage::registry('notification_data');
        $form = new Varien_Data_Form();
        //$form->setHtmlIdPrefix('notification_');
        $fieldset = $form->addFieldset('action_fieldset', array(
            'legend' => Mage::helper('notification')->__('Show page location')
        ));
        $fieldset->addField('show_location', 'multiselect', array(
            'name' => 'show_location',
            'label' => Mage::helper('notification')->__('Apply to') ,
            'title' => Mage::helper('notification')->__('Apply to') ,
            'required' => false,
            'values' => Mage::getSingleton('notification/system_config_locationarray')->toOptionArray(true)
        ));
      
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}