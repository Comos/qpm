<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Supervision;

/**
 *@example
 *  > new Config([
 *  > 'factoryMethod' => function() {...;},//or 'runnableClass' => 'ClassName'
 *  > //factoryMethod or runnableClass or runnableCallback is required
 *  > 'quantity' => 3,//how many process to keep,default is 1
 *  > 'maxRestartTimes' => 3,//default is -1,-1 means ignore it
 *  > 'withInSeconds' => 10,//default is -1,means ignore it
 *  > 'timeout' => 10,//default is -1, means ignore it
 *  > ]);
 *
 */
class Config {
	const DEFAULT_QUANTITY = 1;
	const DEFAULT_MAX_RESTART_TIMES = -1;
	const DEFAULT_WITH_IN_SECONDS = -1;
	const DEFAULT_TIMEOUT = -1;
	
	protected $_factoryMethod;
	protected $_keeperRestartPolicy;
	protected $_timeout;
	protected $_onTimeout;		
	public function __construct($config) {
		$this->_initFactoryMethod($config);
		$this->_initQuantity($config);
		$this->_initTimeout($config);
		$this->_initKeeperRestartPolicy($config);
		$this->_initOnTimeout($config);
	}
	public function getFactoryMethod() {
		return $this->_factoryMethod;
	}
	
	public function getKeeperRestartPolicy() {
		return clone($this->_keeperRestartPolicy);
	}

	public function getQuantity() {
		return $this->_quantity;
	}
	public function getOnTimeout() {
		return $this->_onTimeout;
	}
	/**
	 * 
	 * @return integer
	 */
	public function getTimeout() {
		return $this->_timeout;
	}
	/**
	 * @return boolean
	 */
	public function isTimeoutEnabled() {
	    return $this->getTimeout() > 0;
	}
	
	private function _initKeeperRestartPolicy($config) {
		$max = self::_fetchIntValue($config, 'maxRestartTimes', self::DEFAULT_MAX_RESTART_TIMES);
		if (!is_int($max) || $max == 0) {
			throw new \InvalidArgumentException('maxRestartTimes must be integer and cannot be null');
		}

		$withIn = self::_fetchIntValue($config, 'withInSeconds', self::DEFAULT_WITH_IN_SECONDS);
		if (!is_int($withIn) || $withIn == 0) {
			throw new \InvalidArgumentException('withInSeconds must be integer and cannot be null');
		}
		
		$this->_keeperRestartPolicy = KeeperRestartPolicy::create($max, $withIn);
	}

	private static function _fetchIntValue($config, $field, $defaultValue) {
		if (!isset($config[$field])) {
			$v = $defaultValue;
		} else {
			$v = $config[$field];
		}
		if (is_string($v) && is_numeric($v)) {
			$v = intval($v);
		}
		return $v;
	}
	
	private function _initQuantity($config) {
		$q = self::_fetchIntValue($config, "quantity", self::DEFAULT_QUANTITY);
		if (!is_int($q) || $q <1) {
			throw new \InvalidArgumentException('quantity must be positive integer');
		}
		$this->_quantity = $q;
		
	}
	private function _initTimeout($config) {
		$q = self::_fetchIntValue($config, "timeout", self::DEFAULT_TIMEOUT);
		if (!\is_int($q) && !\is_float($q)) {
			throw new \InvalidArgumentException('timeout must be num');
		}
		$this->_timeout = $q;
	}

	private function _initOnTimeout($config) {
		$q = isset($config['onTimeout'])?$config['onTimeout']:null;
		if (\is_null($q)) {
			return $q;
		}
		if (!\is_callable($q)) {
			throw new \InvalidArgumentException('onTimeout must be callable');
		}
		$this->_onTimeout = $q;
	}

	private function _initFactoryMethod($config) {
		if (isset($config['factoryMethod'])) {
			if (!\is_callable($config['factoryMethod'])) {
				throw new \InvalidArgumentException('factoryMethod must be callable');
			}
			$this->_factoryMethod = $config['factoryMethod'];
			return;
		}
		if (isset($config['runnableClass'])) {
			$clazz = $config['runnableClass'];
			if (!is_subclass_of($clazz, '\Comos\Qpm\Process\Runnable')) {
				throw new \InvalidArgumentException('runnableClass must be an implemention of Comos\\Qpm\\Process\\Runnable');
			}
			$this->_factoryMethod = function() use($clazz) {
				return array((new $clazz()), 'run');
			};
			return;
		}
		if (isset($config['runnableCallback'])) {
			$callback = $config['runnableCallback'];
			if (!is_callable($callback)) {
				throw new \InvalidArgumentException('runnableCallback must be callable');
			}
			$this->_factoryMethod = function() use($callback) {
				return $callback;
			};
			return;
		}
		throw new \InvalidArgumentException('the way to start new process is required. optional ways: factoryMethod, runnableClass or runnableCallback');
	}
}
