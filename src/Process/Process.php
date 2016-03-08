<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Process;

use Comos\Qpm\Log\Logger;

class Process
{

    /**
     *
     * @var Process
     */
    protected static $_current;

    /**
     *
     * @var int
     */
    private $_pid;

    /**
     *
     * @var int
     */
    private $_parentProcessId;

    /**
     *
     * @param int $pid            
     */
    protected function __construct($pid, $parentProcessId = null)
    {
        $this->_pid = $pid;
        $this->_parentProcessId = $parentProcessId;
    }

    /**
     * Factory method to create a new \Comos\Qpm\Process\Process instance
     *
     * @return Process
     */
    public static function process($pid)
    {
        return new self($pid);
    }

    /**
     *
     * @return Process
     */
    public static function current()
    {
        $pid = \posix_getpid();
        if (! self::$_current || ! self::$_current->isCurrent()) {
            self::$_current = new Process($pid, \posix_getppid());
        }
        return self::$_current;
    }

    /**
     *
     * @return Process returns null on failure
     *         It cannot be realtime in some cases.
     *         e.g.
     *         $child = Process::current()->folkByCallable($fun);
     *         echo $child->getParent()->getPid();
     *         If child process changed the parent, you would get the old parent ID.
     */
    public function getParent()
    {
        if ($this->_parentProcessId) {
            return self::process($this->_parentProcessId);
        }
        
        if ($this->isCurrent()) {
            $ppid = \posix_getppid();
            if (! $ppid)
                return null;
            return self::process($ppid);
        }
        
        return null;
    }

    /**
     *
     * @return integer
     */
    public static function getCurrentPid()
    {
        return\posix_getpid();
    }

    /**
     *
     * @return int
     */
    public function getPid()
    {
        return $this->_pid;
    }

    /**
     *
     * @return boolean
     */
    public function isCurrent()
    {
        return \posix_getpid() == $this->_pid;
    }

    /**
     *
     * @throws FailToSendSignalException
     */
    public function kill()
    {
        return $this->sendSignal(\SIGKILL);
    }

    /**
     * @throw FailToSendSignalException
     */
    public function terminate()
    {
        return $this->sendSignal(\SIGTERM);
    }

    /**
     * @throw FailToSendSignalException
     */
    public function sendSignal($sig)
    {
        $result =\posix_kill($this->_pid, $sig);
        if (false === $result) {
            throw new FailToSendSignalException('kill ' . $sig . ' ' . $this->_pid);
        }
        return $result;
    }

    /**
     *
     * @deprecated will be alternated sendSignal
     * @param integer $sig            
     * @return boolean
     */
    public function doKill($sig)
    {
        return $this->sendSignal($sig);
    }

    /**
     * to fork to create a process and run $target in there
     *
     * @param
     *            Runnable | \callable $target
     * @return ChildProcess
     * @throws \InvalidArgumentException
     */
    public static function fork($target)
    {
        if (! \is_callable($target) && ! $target instanceof Runnable) {
            throw new \InvalidArgumentException('$target must be a valid callback or Comos\\Qpm\\Process\\Runnable');
        }
        
        $pid =\pcntl_fork();
        
        if ($pid == - 1) {
            throw new FailToForkException('fail to folk.');
        }
        
        if ($pid == 0) {
            try {
                if ($target instanceof Runnable) {
                    $code = $target->run();
                } else {
                    $code =\call_user_func($target);
                }
            } catch (\Exception $ex) {
                Logger::err($ex);
                $code = - 1;
            }
            if (\is_null($code)) {
                $code = 0;
            }elseif(! \is_int($code)) {
                $code = 1;
            }
            exit($code);
        }
        
        return new ChildProcess($pid, self::getCurrentPid());
    }

    /**
     * let current process run in the background
     *
     * @throws Exception
     */
    public static function toBackground()
    {
        if (0 >\posix_setsid()) {
            throw new Exception('fail to set sid');
        }
    }
}
