<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Supervision;
use Comos\Qpm\Log\Logger;

class Supervisor {
	/**
	 *@ return Comos\Qpm\Supervision\Supervisor
	 */
	public static function taskFactoryMode($conf) {
		$config = new Config($conf);
		return new self(new TaskFactoryKeeper($config));
	}
	/**
	 * @return Comos\Qpm\Supervision\Supervisor
	 */
	public static function oneForOne($config) {
		$configs = array(new Config($config));
		return self::_oneForOne($configs);
	}
	/**
	 * @return Comos\Qpm\Supervision\Supervisor
	 */
	public static function multiGroupOneForOne($configs) {
		if (!is_array($configs) and !($configs instanceof \Iterator)) {
			throw new \InvalidArgumentException('exptects an array or Iterator'); 
		}
		if (!count($configs)) {
			throw new \InvalidArgumentException('at least 1 item');
		}
		$cs = array();
		foreach($configs as $c) {
			$cs[] = new Config($c);
		}
		return self::_oneForOne($cs);
	}
	/**
	 * @return Comos\Qpm\Supervision\Supervisor
	 */
	private static function _oneForOne($configs) {
		return new self(new OneForOneKeeper($configs));
	}
	
	private $_keeper;
	public function __construct($keeper) {
		$this->_keeper = $keeper;
	}
	
	public function getKeeper() {
		return $this->_keeper;
	}
	
	public function start() {
		Logger::debug(__METHOD__.' before-startall');
		$this->_keeper->startAll();
		Logger::debug(__METHOD__.' before-keep');
		$this->_keeper->keep();
		Logger::debug(__METHOD__.' after-keep');
	}
	
	public function stop() {
		Logger::debug(__METHOD__.' before-stop');
		$this->_keeper->stop();
		Logger::debug(__METHOD__.' after-stop');
	}
	
	public function registerSignalHandler() {
		\pcntl_signal(\SIGTERM, array($this, 'stop'));
	}
}
