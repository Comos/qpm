<?php
namespace qpm\supervisor;

use \qpm\supervisor\OneForOneKeeper;
use \qpm\supervisor\Config;

class OneForOneKeeperTest extends \PHPUnit_Framework_TestCase {
	protected $_logFile;
	protected function setUp() {
		$this->_logFile = __FILE__.'.log';
		$r = \file_put_contents($this->_logFile, "");
		if ($r === false) {
			throw new \Exception('fail to init log file:'.$this->_logFile);
		}
		OneForOneKeeperTest_Runnable::setLogFile($this->_logFile);
	}
	
	public function testIsSubClassOf() {
		$this->assertTrue(is_subclass_of(__NAMESPACE__.'\OneForOneKeeperTest_Runnable', '\qpm\process\Runnable'));
	}
	public function configsForTestStartAll() {
		return[[['factoryMethod' => 
				function(){return new OneForOneKeeperTest_Runnable();},
			]],
			[['runnableClass' => '\\'.__NAMESPACE__.'\OneForOneKeeperTest_Runnable']],
		];
	}
	/**
	 * @dataProvider configsForTestStartAll
	 */
	public function testStartAll($baseConfig) {
		$this->_doTestStartAll($baseConfig);
	}
	/**
	 * @dataProvider configsForTestStartAll
	 */
	public function testStartAll_UseFactoryMethodToCreateKeeper($baseConfig) {
		$this->_doTestStartAll($baseConfig, true);
	}
	protected function _doTestStartAll($baseConfig, $useFactoryMethodToCreateKeeper= false) {
		$config = $baseConfig;
		$config['quantity'] = $quantity = 3;
		if (!$useFactoryMethodToCreateKeeper) {
			$keeper = new OneForOneKeeper([new Config($config)]);
		} else {
			$keeper = \qpm\supervisor\Supervisor::oneForOne($config)->getKeeper();
		}
		$keeper->startAll();
		$pids = [];
		for ($i = 0; $i < $quantity; $i++) {
			$status = 0;
			$pids[pcntl_wait($status)] = true;
		}
		$currentPid = \posix_getpid();
		$r = \file_get_contents($this->_logFile);
		$lines = explode("\n", $r);
		$count = 0;
		foreach($lines as $line) {
			if(trim($line)) {
				list($pid, $ppid) = explode(',', $line);
				$count++;
				$this->assertTrue($pids[$pid]);
				$this->assertEquals($currentPid, $ppid);
			}
		}
		$this->assertEquals($quantity, $count);	
	}

	protected function tearDown() {
		@unlink($this->_logFile);
		if (is_file($this->_logFile)) {
			throw new \Exception('fail to unlink '.$this->_logFile);
		}
	}
}

class OneForOneKeeperTest_Runnable implements \qpm\process\Runnable {
	private static $_file;
	public static function setLogFile($file) {
		self::$_file = $file;
	}
	public function run() {
		\file_put_contents(self::$_file, \posix_getpid().",".\posix_getppid()."\n", FILE_APPEND);
	}
}
