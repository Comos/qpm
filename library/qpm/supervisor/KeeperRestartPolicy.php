<?php
namespace qpm\supervisor;
class KeeperRestartPolicy {
	protected $_list;
	protected $_max;
	protected $_withIn;
	/**
	 * @return \qpm\supervisor\KeeperRestartPolicy
	 */
	public static function create($maxRestartTimes, $withInSeconds) {
		if ($maxRestartTimes < 0) {
			return new KeeperRestartIgnoreAllPolicy();
		}
		if ($withInSeconds < 0) {
			return new KeeperRestartIgnoreTimePolicy($maxRestartTimes);
		}
		return new self($maxRestartTimes, $withInSeconds);
	}
	protected function __construct($maxRestartTimes, $withInSeconds) {
		$this->reset();
		$this->_max = $maxRestartTimes;
		$this->_withIn = $withInSeconds;
	}
	
	public function reset() {
		$this->_list = new \SplDoublyLinkedList();
	}
	/**
	 * @throws OutOfPolicyException out of policy
	 */
	public function check() {
		$current = $this->_list[] = microtime(true);
		while ($first = $this->_list->bottom()) {
			if ($current - $first > $this->_withIn) {
				$this->_list->shift();
				continue;
			}
			break;
		}
		if (count($this->_list) > $this->_max) {
			throw new OutOfPolicyException('out of policy');
		}
	}
}

class KeeperRestartIgnoreAllPolicy extends KeeperRestartPolicy {
	public function __construct() {}
	public function check() {}
	public function reset() {}
}

class KeeperRestartIgnoreTimePolicy extends KeeperRestartPolicy {
	protected $_max;
	protected $_count;
	public function __construct($maxRestartTimes) {
		$this->_max = $maxRestartTimes;
		$this->reset();
	}
	public function check() {
		if(++$this->_count > $this->_max) {
			throw new OutOfPOlicyException('out of policy');
		}
	}
	public function reset() {
		$this->_count = 0;
	}
}
