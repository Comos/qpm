<?php
namespace qpm\process;

use \qpm\process\Process;

class ProcessTest extends \PHPUnit_Framework_TestCase {
	protected $_logFile;
	protected function setUp() {
		$this->_logFile = __FILE__.'.log';
		if (false === file_put_contents($this->_logFile, '')) {
			throw new \Exception('fail to init log file:'.$this->_logFile);
		}
	}

	protected function tearDown() {
		$st = 0;
		while(true) {
			$r = \pcntl_wait($st, WNOHANG);
			if ($r == 0 || $r == -1){
				break;
			}
		};
		@unlink($this->_logFile);
	}
	public function testIsCurrent() {
		$this->assertTrue(Process::current()->isCurrent());
	}
	public function testProcess() {
		$this->assertTrue(Process::process(posix_getpid()) instanceof Process);
	}
	public function testCurrent() {
		$current = Process::current();
		$current1 = Process::current();
		$this->assertEquals($current, $current1);
		$this->assertTrue(is_int($current->getPid()));
		$this->assertTrue($current instanceof Process);
	}
	public function testCurrent_AfterFork() {
		$child = Process::current()->forkByCallable(function() {
			file_put_contents($this->_logFile, Process::current()->getPid());
		});
		usleep(1000*20);
		$pidFromLogFile = file_get_contents($this->_logFile);
		$this->assertEquals($child->getPid(), $pidFromLogFile);
		$this->assertEquals(Process::current()->getPid(), posix_getpid());
		$this->assertNotEquals($child->getPid(), Process::current()->getPid());
	}
	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage argument must be a valid callback
	 */
	public function testForkByCallable_Arg0NotAValidCallable() {
		Process::current()->forkByCallable('x');
	}
	public function testForkByCallable() {
		$current = Process::current();
		$func = function() {
			usleep(500*1000);
			file_put_contents($this->_logFile,'x', FILE_APPEND);
		};
		$current->forkByCallable($func);
		$current->forkByCallable($func);
		$c = file_get_contents($this->_logFile);
		$this->assertEquals('', $c);
		usleep(800*1000);
		$c = file_get_contents($this->_logFile);
		$this->assertEquals('xx', $c);
	}
	
	public function testFork() {
		$current = Process::current();
		$current->fork(new ProcessTest_Runnable($this->_logFile));
		$current->fork(new ProcessTest_Runnable($this->_logFile));
		$c = file_get_contents($this->_logFile);
                $this->assertEquals('', $c);
                usleep(800*1000);
                $c = file_get_contents($this->_logFile);
                $this->assertEquals('YY', $c);
	}
	
	public function testIsCurrent_False() {
		$current = Process::current();
		$child = $current->forkByCallable(
			function() use($current) {
				file_put_contents($this->_logFile, $current->isCurrent()?'yes':'no');
			}
		);
		$st = 0;
		$wpid = \pcntl_wait($st);
		$this->assertEquals('no', file_get_contents($this->_logFile));
	}

	public function testKill() {
		$t0 = microtime(true);
		$child = Process::current()->forkByCallable(
			function() {usleep(0.1*1000*1000);}
		);
		$child->kill();
		$st = 0;
		$wpid = \pcntl_wait($st);
		$t1 = microtime(true);
		$this->assertEquals($wpid, $child->getPid());
		$this->assertLessThan(0.05, $t1 - $t0);
	}
}

class ProcessTest_Runnable implements \qpm\process\Runnable {
	public function __construct($file) {
		$this->_logFile = $file;
	}
	/**
	 * @see \qpm\process\Runnable::run()
	 */
	public function run() {
		usleep(500*1000);
		file_put_contents($this->_logFile, 'Y', FILE_APPEND);
	}
}
