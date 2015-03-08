<?php
namespace qpm\process;
class Process {
	/**
	 * @var MainProcess
	 */
	protected static $_current;
	/**
	 * @var int
	 */
	private $_pid;
	/**
	 * @var int
	 */
	private $_parentProcessId;
	/**
	 * @param int $pid
	 */
	protected function __construct($pid, $parentProcessId = null) {
		$this->_pid = $pid;
		$this->_parentProcessId = $parentProcessId;
	}
	/**
	 * @return Process
	 */
	public static function process($pid) {
		return new self($pid);
	}
	/**
	 * @return MainProcess
	 */
	public static function current() {
		$pid = \posix_getpid();
		if (!self::$_current || !self::$_current->isCurrent()) {
			self::$_current = new MainProcess($pid, \posix_getppid());
		}
		return self::$_current;
	}
	/**
	 * @return qpm\process\Process
	 * returns null on failure
	 * It cannot be realtime in some cases.
	 * e.g. 
	 * 	$child = Process::current()->folkByCallable($fun);
	 *  echo $child->getParent()->getPid();
	 * If child process changed the parent, you would get the old parent ID.
	 */
	public function getParent() {
		if ($this->_parentProcessId) {
			return self::process($this->_parentProcessId);
		}
		
		if ($this->isCurrent()) {
			$ppid = \posix_getppid();
			if (!$ppid) return null;
			return self::process($ppid);
		}
		
		return null;
	}
	
	/**
	 * @return int
	 */
	public function getPid() {
		return $this->_pid;
	}
	
	/**
	 * @return boolean
	 */
	public function isCurrent() {
		return \posix_getpid() == $this->_pid;
	}
	/**
	 * @throw FailToSendSignalException
	 */
	public function kill() {
		return $this->doKill(SIGKILL);
	}
	/**
	 * @throw FailToSendSignalException
	 */
	public function terminate() {
		return $this->doKill(SIGTERM);
	}
	public function doKill($sig) {
		$result = posix_kill($this->_pid, $sig);
		if(false === $result) {
                        throw new FailToSendSignalException('kill '.$sig.' '.$this->_pid);
        	}
		return $result;
	}
}
