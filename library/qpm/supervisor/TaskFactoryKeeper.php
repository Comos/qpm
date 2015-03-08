<?php
namespace qpm\supervisor;

use qpm\process\Process;
use qpm\log\Logger;

class TaskFactoryKeeper {
	const SLEEP_TIME_AFTER_ERROR = 2000000;
	protected $_stoped = false;
	protected $_currentProcess;
	protected $_children = [];
	protected $_config;
	public function __construct($config) {
		$this->_currentProcess = Process::current();
		$this->_config = $config;
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
			$target = call_user_func($this->_config->getFactoryMethod());
		} catch(StopSignal $ex) {
			Logger::info(__CLASS__.'::'.___LINE__.'() received stop signal');
			throw $ex;
		} catch (\Exception $ex) {
			Logger::err($ex);
			usleep(self::SLEEP_TIME_AFTER_ERROR);
		}
		if (!$target) {
			return;
		}
		$forkMethod = ($target instanceof \qpm\process\Runnable) ? 'fork' : 'forkByCallable';
		try {
			$process = Process::current()->$forkMethod($target);
			$this->_children[$process->getPid()] = $process; 
		} catch(\Exception $ex) {
			Logger::err('[ALARM]'.$ex);
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
				if (count($this->_children) < $this->_config->getQuantity()) {
					$this->_startOne();
				}
				$status = null;
				$pid = \pcntl_wait($status, WNOHANG);
				if ($pid > 0) {
					$this->_processExit($pid);
				}
			}
		} catch (StopSignal $ex) {
			$this->stop();
			//TODO more options
		}
	}
	protected function _processExit($pid) {
		Logger::debug(__CLASS__.'::'.__METHOD__."($pid)");
		if (!isset($this->_children[$pid])) {
			Logger::err(__CLASS__.'::'.__METHOD__.'() none managed childprocess exists');
			return;
		}
		unset($this->_children[$pid]);
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
		
		while (count($this->_children)) {
			$status = 0;
			$pid = \pcntl_wait($status);
			unset($this->_children[$pid]);
		}
	}
}
