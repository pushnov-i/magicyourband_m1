<?php
/**
 * Class ${NAME}
 *
 * @author Fabrizio Branca
 * @since 2014-08-10
 */
class Aoe_Scheduler_Model_Job_Db extends Aoe_Scheduler_Model_Job_Abstract
{

    /**
     * Initialize resource
     */
    public function _construct()
    {
        $this->_init('aoe_scheduler/job_db', 'job_code');
        parent::_construct();
    }

    public function loadByCode($jobCode)
    {
        $this->load($jobCode, 'job_code');
        return $this;
    }

    public function copyFrom(Aoe_Scheduler_Model_Job_Abstract $job)
    {
        $this->setData($job->getData());
    }

    public function getXmlJob()
    {
        if (!$this->getJobCode()) {
            return false;
        }
        $jobFactory = Mage::getModel('aoe_scheduler/job_factory'); /* @var $jobFactory Aoe_Scheduler_Model_Job_Factory */
        return $jobFactory->loadByCode($this->getJobCode(), true);
    }

}