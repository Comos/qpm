<?php
namespace qpm\process;
require_once __DIR__.'/Process.php';
require_once __DIR__.'/status/ForkedChildStatus.php';
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
		return status\ForkedChildStatus::create($this->_status, $this->_exited);
	}
	/**
	 * @throws FailToGetChildStatusException
	 * @return void
	 */
	protected function _wait() {
		if ($this->_exited) {
			return;
		}
		$pid = pcntl_waitpid($this->getPid(), $this->_status, WNOHANG|WUNTRACED);
		if ($pid == $this->getPid()) {
			$this->_exited = true;
		}
		if ($pid == -1) {
			require_once __DIR__.'/FailToGetChildStatusException.php';
			throw new FailToGetChildStatusException('wait returns -1. status is '.$this->_status);
		}
	}
}
