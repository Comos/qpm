<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Supervision;

use Comos\Qpm\Process\Process;
use Comos\Qpm\Log\Logger;

class TaskFactoryKeeper {
	const SLEEP_TIME_AFTER_ERROR = 1000000;
	protected $_stoped = false;
	protected $_currentProcess;
	protected $_checkingInterval = 60000;
	protected $_factoryMethod = null;
	protected $_factoryIterator = null;
	/**
	 * 
	 * @var ProcessStub[]
	 */
	protected $_children = array();
	/**
	 * 
	 * @var Config
	 */
	protected $_config;
	protected $_timeout = -1;//seconds, -1 means no timeout.
	public function __construct($config) {
		$this->_currentProcess = Process::current();
		$this->_config = $config;
		$this->_timeout = $config->getTimeout();
		$this->_onTimeoutCallback = $config->getOnTimeout();
		$this->_factoryMethod = $config->getFactoryMethod();
	}
	
	public function restart() {
		Logger::debug(__METHOD__.'()');
		$this->stop();
		$this->startAll();
		$this->keep();
	}
	
	public function startAll() {
	}
	/**
	 * @throws Comos\Qpm\Supervision\StopSignal
	 */
	protected function _startOne() {
		Logger::debug(__METHOD__.'()');
		$target = null;
		try {
			$target = \call_user_func($this->_config->getFactoryMethod());
			if($this->_factoryIterator === null){
				$res = \call_user_func($this->_config->getFactoryMethod());
				if($res instanceof \Iterator){
					$this->_factoryIterator = $res;
				}else{
					$target = $res;
					$this->_factoryIterator = false;
				}
			}
			if($this->_factoryIterator instanceof \Iterator){
				$this->_factoryIterator->next();
				$target = $this->_factoryIterator->current();
			}else if($target === null){
				$target = \call_user_func($this->_config->getFactoryMethod());
			}
		} catch(StopSignal $ex) {
			Logger::debug('received stop signal');
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
			$this->_children[$process->getPid()] = new ProcessStub($process, $this->_config);
		} catch(\Exception $ex) {
			Logger::err('exception', array('exception'=>$ex));
			\usleep(self::SLEEP_TIME_AFTER_ERROR);
		}
	}
	
	/**
	 * @return void
	 */
	public function keep() {
		Logger::debug(__METHOD__.'()');
		$this->_stoped = false;
		try {
			while (!$this->_stoped) {
				if (\count($this->_children) < $this->_config->getQuantity()) {
					$this->_startOne();
					continue;
				}
				
				$status = null;
				$pid = \pcntl_wait($status, \WNOHANG);
				if ($pid > 0) {
					$this->_processExit($pid);
					continue;
				}
				$this->_checkTimeout();
				\usleep($this->_checkingInterval);
			}
		} catch (StopSignal $ex) {
			Logger::info('Received a StopSignal');
			$this->_waitToEnd();
		}
	}
	protected function _checkTimeout() {
		if ($this->_timeout<=0) {
			return;
		}
		foreach ($this->_children as $child) {
		    $child->dealWithTimeout();
		}
	}
	
	protected function _processExit($pid) {
		Logger::debug(__METHOD__."($pid)");
		if (!isset($this->_children[$pid])) {
			Logger::err(__METHOD__.'() none managed childprocess exists');
			return;
		}
		unset($this->_children[$pid]);
	}
	
	protected function _waitToEnd() {	
		while (count($this->_children)) {
			$status = 0;
			$pid = \pcntl_wait($status, \WNOHANG);
			if ($pid > 0) {
				$this->_processExit($pid);
			}
			$this->_checkTimeout();
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
				$child->getProcess()->kill();
			} catch (Exception $ex) {
				Logger::err($ex);
			}
		}
		$this->_waitToEnd();
	}
}
