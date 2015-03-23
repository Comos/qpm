<?php
/**
 * @author bigbigant
 */

namespace qpm\supervisor;

use qpm\process\Process;
use qpm\log\Logger;

class TaskFactoryKeeper {
	const SLEEP_TIME_AFTER_ERROR = 1000000;
	protected $_stoped = false;
	protected $_currentProcess;
	protected $_checkingInterval = 100000;
	protected $_children = [];
	protected $_config;
	protected $_timeout = -1;//seconds, -1 means no timeout.
	public function __construct($config) {
		$this->_currentProcess = Process::current();
		$this->_config = $config;
		$this->_timeout = $config->getTimeout();
		$this->_onTimeoutCallback = $config->getOnTimeout();
	}
	
	public function restart() {
		Logger::debug(__CLASS__.'::'.__METHOD__.'()');
		$this->stop();
		$this->startAll();
		$this->keep();
	}
	
	public function startAll() {
	}
	/**
	 * @throws qpm\supervisor\StopSignal
	 */
	protected function _startOne() {
		Logger::debug(__CLASS__.'::'.__METHOD__.'()');
		$target = null;
		try {
			$target = \call_user_func($this->_config->getFactoryMethod());
		} catch(StopSignal $ex) {
			Logger::info(__CLASS__.'::'.__LINE__.'() received stop signal');
			throw $ex;
		} catch (\Exception $ex) {
			Logger::err($ex);
			\usleep(self::SLEEP_TIME_AFTER_ERROR);
		}
		if (!$target) {
			Logger::debug('fetched target is null. skipped');
			return;
		}
		try {
			$process = Process::fork($target);
			$this->_children[$process->getPid()] = [$process, microtime(true)]; 
		} catch(\Exception $ex) {
			Logger::err('{exception}', ['exception'=>$ex]);
			usleep(self::SLEEP_TIME_AFTER_ERROR);
		}
	}
	
	/**
	 * @return void
	 */
	public function keep() {
		Logger::debug(__CLASS__.'::'.__METHOD__.'()');
		$this->_stoped = false;
		try {
			while (!$this->_stoped) {
				if (\count($this->_children) < $this->_config->getQuantity()) {
					$this->_startOne();
					continue;
				} else {
					\usleep($this->_checkingInterval);
				}
				$status = null;
				$pid = \pcntl_wait($status, WNOHANG);
				if ($pid > 0) {
					$this->_processExit($pid);
				}
				$this->_checkTimeout();
			}
		} catch (StopSignal $ex) {
			Logger::info(__CLASS__.'::'.__METHOD__.'() received a StopSignal:'.$ex);
			$this->_waitToEnd();
		}
	}
	protected function _checkTimeout() {
		if ($this->_timeout<=0) {
			return;
		}
		$t = \microtime(true);
		foreach ($this->_children as $pid => $child) {
			if ($t - $child[1] >= $this->_timeout)  {
				try {
					\qpm\log\Logger::info("process[".$child[0]->getPid()."] will be killed because of timeout");
					$this->_onTimeout($child[0]);
					$this->_killedChildren[$pid] = $child;
					unset($this->_children[$pid]);
					$child[0]->kill();
				} catch (\Exception $ex) {
					\qpm\log\Logger::err($ex);
				}
			}
		}
	}
	protected function _onTimeout($process) {
		if ($this->_onTimeoutCallback) {
			$m = $this->_onTimeoutCallback;
			$m($process);
		}
	}
	protected function _processExit($pid) {
		Logger::debug(__CLASS__.'::'.__METHOD__."($pid)");
		if (!isset($this->_children[$pid]) && !isset($this->_killedChildren[$pid])) {
			Logger::err(__CLASS__.'::'.__METHOD__.'() none managed childprocess exists');
			return;
		}
		unset($this->_children[$pid]);
		unset($this->_killedChildren[$pid]);
	}
	
	protected function _waitToEnd() {	
		while (count($this->_children)) {
			$this->_checkTimeout();
			$status = 0;
			$pid = \pcntl_wait($status, \WNOHANG);
			if ($pid > 0) {
				$this->_processExit($pid);
			}
			\usleep($this->_checkingInterval);
		}

	}
	public function stop() {
		Logger::debug(__CLASS__.'::'.__METHOD__.'()');
		if (!$this->_currentProcess->isCurrent()) {
			return;
		}
		$this->_stoped = true;
		foreach ($this->_children as $child) {
			try {
				$child->kill();
			} catch (Exception $ex) {
				Logger::err($ex);
			}
		}
		$this->_waitToEnd();
	}
}
