<?php

/**
 * Process Manager
 *
 * @author Fabrizio Branca
 * @since 2013-10-11
 */
class Aoe_Scheduler_Model_ProcessManager
{

    CONST XML_PATH_MARK_AS_ERROR = 'system/cron/mark_as_error_after';

    /**
     * Get all schedules running on this server
     *
     * @param string host
     * @return object
     */
    public function getAllRunningSchedules($host = null)
    {
        $collection = Mage::getModel('cron/schedule')->getCollection();
        $collection->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_RUNNING);
        if (!is_null($host)) {
            $collection->addFieldToFilter('host', $host);
        }
        return $collection;
    }

    /**
     * Get all schedules marked as to be killed
     *
     * @param null $host
     * @return object
     */
    public function getAllKillRequests($host = null)
    {
        $collection = $this->getAllRunningSchedules($host);
        $collection->addFieldToFilter('kill_request', array('lt' => strftime('%Y-%m-%d %H:%M:00', time())));
        return $collection;
    }

    /**
     * Check if there's alread a job running with the given code
     *
     * @param string $jobCode
     * @param int $ignoreId
     * @return bool
     */
    public function isJobCodeRunning($jobCode, $ignoreId = NULL)
    {
        $collection = Mage::getModel('cron/schedule')
            ->getCollection()
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_RUNNING)
            ->addFieldToFilter('job_code', $jobCode);
        if (!is_null($ignoreId)) {
            $collection->addFieldToFilter('schedule_id', array('neq' => $ignoreId));
        }
        foreach ($collection as $schedule) { /* @var $schedule Aoe_Scheduler_Model_Schedule */
            $alive = $schedule->isAlive();
            if ($alive !== false) { // TODO: how do we handle null (= we don't know because might be running on a different server?
                return true;
            }
        }
        return false;
    }


    /**
     * Check running jobs
     *
     * @return void
     */
    public function checkRunningJobs()
    {

        foreach ($this->getAllRunningSchedules(gethostname()) as $schedule) { /* @var $schedule Aoe_Scheduler_Model_Schedule */
            $schedule->isAlive(); // checks pid and updates record
        }

        // fallback (where process cannot be checked or if one of the servers disappeared)
        // if a task wasn't seen for some time it will be marked as error
        $maxAge = time() - Mage::getStoreConfig(self::XML_PATH_MARK_AS_ERROR) * 60;

        $schedules = Mage::getModel('cron/schedule')->getCollection() /* @var $schedules Mage_Cron_Model_Resource_Schedule_Collection */
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_RUNNING)
            ->addFieldToFilter('last_seen', array('lt' => strftime('%Y-%m-%d %H:%M:00', $maxAge)))
            ->load();

        foreach ($schedules as $schedule) { /* @var $schedule Aoe_Scheduler_Model_Schedule */
            $schedule->markAsDisappeared(sprintf('Host "%s" has not been available for a while now to update the status of this task and the task is not reporting back by itself', $schedule->getHost()));
        }

    }



    /**
     * Process kill requests
     *
     * @return void
     */
    public function processKillRequests()
    {
        foreach ($this->getAllKillRequests(gethostname()) as $schedule) { /* @var $schedule Aoe_Scheduler_Model_Schedule */
            $schedule->kill();
        }
    }


    /**
     * Run maintenance
     */
    public function watchdog() {
        $this->checkRunningJobs();
        $this->processKillRequests();
    }

}
