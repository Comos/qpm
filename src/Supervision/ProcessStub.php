<?php
/**
 * @author bigbigant
 * @license GPL-3
 */
namespace Comos\Qpm\Supervision;

use Comos\Qpm\Process\Process;
use Comos\Qpm\Log\Logger;

class ProcessStub
{

    /**
     *
     * @var Process
     */
    private $process;

    /**
     *
     * @var Config
     */
    private $config;

    /**
     *
     * @var float
     */
    private $startTime;

    /**
     *
     * @var mix
     */
    private $groupId;

    /**
     *
     * @var boolean
     */
    private $isDealedWithTimeout = false;
    /**
     * 
     * @var boolean
     */
    private $isDealedWithTermTimeout = false;
    /**
     * 
     * @var float
     */
    private $termTime;

    /**
     *
     * @param Process $process            
     * @param Config $config            
     * @param float $startTime            
     */
    public function __construct(Process $process, Config $config, $groupId = null, $startTime = null)
    {
        $this->process = $process;
        $this->config = $config;
        $this->startTime = is_null($startTime) ?\microtime(true) : $startTime;
        $this->groupId = $groupId;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     *
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     *
     * @return boolean
     */
    public function isTimeout()
    {
        if (! $this->config->isTimeoutEnabled()) {
            return false;
        }
        
        $duration =\microtime(true) - $this->getStartTime();
        return $duration > $this->config->getTimeout();
    }

    /**
     *
     * @return \Comos\Qpm\Process\Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     *
     * @return \Comos\Qpm\Supervision\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *
     * @throws \Comos\Qpm\Process\Exception
     * @return boolean
     */
    public function dealWithTimeout()
    {
        if (! $this->isTimeout()) {
            return false;
        }
        
        if ($this->isDealedWithTimeout) {
            if (!$this->config->isKillOnTimeout()) {
                $this->dealWithTermTimeout();
            }
            return false;
        }
        
        $this->isDealedWithTimeout = true;
        
        Logger::info("process[" . $this->getProcess()->getPid() . "] will be terminated for timeout");
        $this->invokeOnTimeout();
        try {
            if ($this->config->isKillOnTimeout()) {
                $this->getProcess()->kill();
            } else {
                $this->termTime = microtime(true);
                $this->getProcess()->terminate();
            }
            
        } catch (\Exception $e) {
            Logger::err($e);
            return false;
        }
        return true;
    }
    
    protected function dealWithTermTimeout()
    {
        if ($this->isDealedWithTermTimeout) {
            return false;
        }
        if ((microtime(true) - $this->termTime) <= $this->config->getTermTimeout()) {
            return false;
        }
        try {
            Logger::info("process[" . $this->getProcess()->getPid() . "] will be killed for termTimeout");
            $this->getProcess()->kill();
            $this->isDealedWithTermTimeout = true;
        } catch(\Exception $e) {
            Logger::err($e);
            return false;
        }
        return true;
    } 
    /**
     * 
     * @return boolean
     */
    private function invokeOnTimeout()
    {
        $onTimeout = $this->getConfig()->getOnTimeout();
        if (! $onTimeout) {
            return false;
        }
        try {
           \call_user_func($onTimeout, $this->getProcess());
        } catch (\Exception $e) {
            \Comos\Qpm\Log\Logger::err($e);
            return false;
        }
        return true;
    }
}