<?php
/**
 * @author bigbigant
 */

namespace qpm\pidfile;

use qpm\pidfile\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase {
	protected $_pidFile;
	protected $_pidFile1;
	protected $_xdir;
	
	protected function setUp() {
		$this->_pidFile = __FILE__.'.pid';
		$this->_pidFile1 = __FILE__.'.pid1';
		$this->_xdir = __DIR__;
	}
	
	protected function tearDown() {
		@unlink($this->_pidFile);
		@unlink($this->_pidFile1);
	}
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test__Construct_EmptyArugument() {
		new Manager('');

	}
	/**
	 * @expectedException \qpm\pidfile\Exception
	 * @expectedExceptionMessage fail to write pid file:
	 */
	public function testStart_PidFileIsADir() {
		$man = new Manager($this->_xdir);
		$man->start();
	}

	public function testStart_PidFileContainsIllegalString() {
		file_put_contents($this->_pidFile, 'xxxx');
		$man = new Manager($this->_pidFile);
		$man->start();
		$r = file_get_contents($this->_pidFile);
		$ms = null;
		$this->assertEquals(1, preg_match('/^[0-9]+$/', $r, $ms));
	}
	public function testStart() {
		$man = new Manager($this->_pidFile);
		$man1 = new Manager($this->_pidFile1);
		$man->start();
		$man1->start();
	}
	/**
	 * @expectedException \qpm\pidfile\Exception
	 * @expectedExceptionMessage process exists, no need to start a new one 
	 */
	public function testStart_Start2TimesWithOnePidFile() {
		$man = new Manager($this->_pidFile);
		$man1 = new Manager($this->_pidFile);
		$man->start();
		$man1->start();

	}
	public function testStart_Start2ProcessWithOnePidFile() {
		$pidfile = $this->_pidFile;
		$process = \qpm\process\Process::fork(function() use($pidfile) {
			$man = new Manager($pidfile);
			$man->start();
			usleep(200*1000);
		});
		
		usleep(100*1000);
		$man1 = new Manager($pidfile);
		$process1 = $man1->getProcess();
		$this->assertTrue($process1 instanceof \qpm\process\Process);
		try {
			$man2 = new Manager($this->_pidFile);
			$man2->start();
			$this->fail('expects Exception');
		} catch(\Exception $e) {
			$st = 0;
			$pid = pcntl_wait($st);
			$this->assertEquals($pid, $process->getPid());
			$this->assertEquals($pid, $process1->getPid());
			$this->assertTrue(\pcntl_wifexited($st));
			$this->assertTrue($e instanceof \qpm\pidfile\Exception);
			$this->assertEquals('process exists, no need to start a new one', $e->getMessage());
		}
	}
}
