<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Process;

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
        $pid =\posix_getpid();
        if (! self::$_current || ! self::$_current->isCurrent()) {
            self::$_current = new Process($pid,\posix_getppid());
        }
        return self::$_current;
    }

    /**
     *
     * @return Comos\Qpm\Process\Process returns null on failure
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
            $ppid =\posix_getppid();
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
        return \posix_getpid();
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
        return\posix_getpid() == $this->_pid;
    }

    /**
     * @throw FailToSendSignalException
     */
    public function kill()
    {
        return $this->doKill(\SIGKILL);
    }

    /**
     * @throw FailToSendSignalException
     */
    public function terminate()
    {
        return $this->doKill(\SIGTERM);
    }

    public function doKill($sig)
    {
        $result = \posix_kill($this->_pid, $sig);
        if (false === $result) {
            throw new FailToSendSignalException('kill ' . $sig . ' ' . $this->_pid);
        }
        return $result;
    }

    /**
     * to fork to create a process and run $target in there
     *
     * @param
     *            \Comos\Qpm\Process\Runnable | \callable $target
     * @return \Comos\Qpm\Process\ChildProcess
     */
    public static function fork($target)
    {
        if ($target instanceof \Comos\Qpm\Process\Runnable) {
            $target = array(
                $target,
                'run'
            );
        }
        if (!\is_callable($target)) {
            throw new \InvalidArgumentException('$target must be a valid callback or Comos\Qpm\\Process\\Runnable');
        }
        
        $pid = \pcntl_fork();
        
        if ($pid == - 1) {
            throw new FailToForkException('fail to folk.');
        }
        
        if ($pid == 0) {
            try {
                $code = call_user_func($target);
            } catch (\Exception $ex) {
                $code = - 1;
            }
            if (!\is_int($code)) {
                $code = 1;
            } else 
                if (\is_null($code)) {
                    $code = 0;
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
        if (0 > \posix_setsid()) {
            throw new Exception('fail to set sid');
        }
    }
}
