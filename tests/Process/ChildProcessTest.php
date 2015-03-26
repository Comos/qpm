<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Process;

use Comos\Qpm\Process\Process;
use Comos\Qpm\Process\ChildProcess;

class ChildProcessTest extends \PHPUnit_Framework_TestCase {
	public function testProcessFork() {
		$child = Process::fork(function() {exit;});
		$this->assertTrue($child instanceof ChildProcess);
		$st = 0;
		$cpid = pcntl_wait($st);
		$this->assertEquals($cpid, $child->getPid());
	}
	
	public function testGetStatus() {
		$child = Process::fork(function() {usleep(100*1000);exit;});
		$status = $child->getStatus();
		$this->assertTrue($status instanceof \Comos\Qpm\Process\Status\ForkedChildStatus);
		$this->assertTrue($status instanceof \Comos\Qpm\Process\Status\NotExitStatus);
		$this->assertNull($status->getExitCode());
	}
}
