<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Process;
/**
 * A instance of ChildProcess represents a fork in main process.
 * Generally, it returns by MainProcess::fork().
 */
class ChildProcess extends Process {
	/**
	 * @var boolean
	 */
	protected $_exited = false;
	/**
	 * @var int
	 */
	protected $_status;
	/**
	 * @throws FailToGetChildStatusException
	 * @return boolean
	 */
	public function isAlive() {
		$this->_wait();
		return !$this->_exited;
	}
	/**
	 * @return status\ForkedChildStatus 
	 */
	public function getStatus() {
		$this->_wait();
		return Status\ForkedChildStatus::create($this->_status, $this->_exited);
	}
	/**
	 * @throws FailToGetChildStatusException
	 * @return void
	 */
	protected function _wait() {
		if ($this->_exited) {
			return;
		}
		$pid = \pcntl_waitpid($this->getPid(), $this->_status, \WNOHANG|\WUNTRACED);
		if ($pid == $this->getPid()) {
			$this->_exited = true;
		}
		if ($pid == -1) {
			throw new FailToGetChildStatusException('wait returns -1. status is '.$this->_status);
		}
	}
}
