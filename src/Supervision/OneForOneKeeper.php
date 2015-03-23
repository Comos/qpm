<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Supervision;

use Comos\Qpm\Process\Process;
use Comos\Qpm\Log\Logger;

class OneForOneKeeper
{

    /**
     * microseconds to sleep after a non-blocking pcntl_wait
     *
     * @var integer
     */
    const DEFAULT_RESTART_INTERVAL = 100000;
    /**
     * 
     * @var boolean
     */
    protected $_stoped = false;
    /**
     * 
     * @var Process
     */
    protected $_currentProcess;
    /**
     * 
     * @var ProcessStub[]
     */
    protected $_children = array();
    /**
     * 
     * @var Config[]
     */
    protected $_configs;
    /**
     * 
     * @var KeeperRestartPolicy[]
     */
    protected $_policies;

    /**
     *
     * @param Config[] $configs            
     */
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
                $this->_startOne($groupId, $config);
            }
        }
    }

    protected function _startOne($groupId, Config $config)
    {
        $target = call_user_func($config->getFactoryMethod());
        $process = Process::fork($target);
        $this->_children[$process->getPid()] = new ProcessStub($process, $config, $groupId);
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
            $pid = \pcntl_wait($status, \WNOHANG);
            if ($pid > 0) {
                $this->_processExit($pid);
            } else {
                usleep(self::DEFAULT_RESTART_INTERVAL);
            }
            $this->_checkTimeout();
        }
    }

    protected function _checkTimeout()
    {
        foreach ($this->_children as $pid => $stub) {
            
            if (! $stub->isTimeout()) {
                continue;
            }
            try {
                \Comos\Qpm\Log\Logger::info("process[" . $stub->getProcess->getPid() . "] will be killed for timeout");
                $this->_onTimeout($stub);
                $this->_killedChildren[$pid] = $stub;
                unset($this->_children[$pid]);
                $stub->getProcess()->kill();
            } catch (\Exception $ex) {
                \Comos\Qpm\Log\Logger::err($ex);
            }
        }
    }

    protected function _onTimeout(ProcessStub $stub)
    {
        $onTimeoutCallback = $stub->getConfig()->getOnTimeout();
        if ($onTimeoutCallback) {
            $onTimeoutCallback($stub->getProcess());
        }
    }

    protected function _processExit($pid)
    {
        if (! isset($this->_children[$pid])) {
            Logger::err("an unknown child process exited PID[{$pid}]");
            return;
        }
        $stub = $this->_children[$pid];
        unset($this->_children[$pid]);
        try {
            $this->_policies[$stub->getGroupId()]->check();
        } catch (OutOfPolicyException $ex) {
            $this->stop();
            throw $ex;
        }
        $this->_startOne($stub->getGroupId(), $stub->getConfig());
    }

    public function stop()
    {
        if (! $this->_currentProcess->isCurrent()) {
            return;
        }
        $this->_stoped = true;
        foreach ($this->_children as $stub) {
            try {
                $stub->getProcess()->kill();
            } catch (Exception $ex) {
                Logger::err('fail to kill process', array('exception'=>$ex));
            }
        }
        
        while (count($this->_children)) {
            $status = 0;
            $pid = \pcntl_wait($status);
            unset($this->_children[$pid]);
        }
    }
}
