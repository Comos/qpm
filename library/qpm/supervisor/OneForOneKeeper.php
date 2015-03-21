<?php
namespace qpm\supervisor;

use qpm\process\Process;

class OneForOneKeeper
{

    const DEFAULT_RESTART_INTERVAL = 100000;
 // for usleep
    protected $_stoped = false;

    protected $_currentProcess;

    protected $_children = [];

    protected $_configs;

    protected $_policies;

    public function __construct($configs)
    {
        $this->_currentProcess = Process::current();
        $this->_configs = $configs;
    }

    public function restart()
    {
        $this->stop();
        $this->startAll();
        $this->keep();
    }

    public function startAll()
    {
        foreach ($this->_configs as $groupId => $config) {
            $this->_policies[$groupId] = $config->getKeeperRestartPolicy();
            $quantity = $config->getQuantity();
            while ($quantity -- > 0) {
                $this->_startOne($groupId);
            }
        }
    }

    protected function _startOne($groupId)
    {
        $config = $this->_configs[$groupId];
        $target = call_user_func($config->getFactoryMethod());
        $process = Process::fork($target);
        $this->_children[$process->getPid()] = [
            'g' => $groupId,
            'p' => $process
        ];
    }

    /**
     *
     * @throws OutOfPolicyException
     */
    public function keep()
    {
        $this->_stoped = false;
        while (! $this->_stoped) {
            $status = null;
            $pid =\pcntl_wait($status, WNOHANG);
            if ($pid > 0) {
                $this->_processExit($pid);
            } else {
                usleep(self::DEFAULT_RESTART_INTERVAL);
            }
        }
    }

    protected function _processExit($pid)
    {
        if (! isset($this->_children[$pid])) {
            // TODO log
            return;
        }
        $groupId = $this->_children[$pid]['g'];
        unset($this->_children[$pid]);
        try {
            $this->_policies[$groupId]->check();
        } catch (OutOfPolicyException $ex) {
            $this->stop();
            throw $ex;
        }
        $this->_startOne($groupId);
    }

    public function stop()
    {
        if (! $this->_currentProcess->isCurrent()) {
            return;
        }
        $this->_stoped = true;
        foreach ($this->_children as $child) {
            try {
                $child['p']->kill();
            } catch (Exception $ex) {
                // do nothing
            }
        }
        
        while (count($this->_children)) {
            $status = 0;
            $pid =\pcntl_wait($status);
            unset($this->_children[$pid]);
        }
    }
}
