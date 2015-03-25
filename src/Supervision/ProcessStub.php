<?php
/**
 * @author bigbigant
 * @license GPL-3
 */
namespace Comos\Qpm\Supervision;

use Comos\Qpm\Process\Process;

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
            return false;
        }
        
        $this->isDealedWithTimeout = true;
        
        \Comos\Qpm\Log\Logger::info("process[" . $this->getProcess()->getPid() . "] will be killed for timeout");
        $this->invokeOnTimeout();
        try {
            $this->getProcess()->kill();
        } catch (\Exception $e) {
            \Comos\Qpm\Log\Logger::err($e);
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