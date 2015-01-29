<?php
namespace qpm\process;
require_once __DIR__.'/MainProcess.php';
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
	 * @param int $pid
	 */
	protected function __construct($pid) {
		$this->_pid = $pid;
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
		$pid = posix_getpid();
		if (!self::$_current || !self::$_current->isCurrent()) {
			self::$_current = new MainProcess($pid);
		}
		return self::$_current;
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
		return posix_getpid() == $this->_pid;
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
                        require_once __DIR__.'/FailToSendSignalException.php';
                        throw new FailToSendSignalException('kill '.$sig.' '.$this->_pid);
        	}
		return $result;
	}
}
