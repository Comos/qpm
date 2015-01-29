<?php
namespace qpm\_tests\supervisor;
require_once __DIR__.'/../BaseTestCaseWithLogFile.php';
require_once 'qpm/supervisor/Supervisor.php';
require_once 'qpm/process/Process.php';
use qpm\supervisor\Supervisor;
use qpm\process\Process;

class SupervisorTest extends \qpm\_tests\BaseTestCaseWithLogFile {
	protected function setUp() {
		parent::setUp();
	}
	/**
	 *@expectedException InvalidArgumentException
	 */
	public function testOneForOne_InvalidArgument() {
		Supervisor::oneForOne([]);
	}
	/**
	 *@dataProvider dataProvider4testMultiGroupOneForOne_InvalidArgument
	 *@expectedException \InvalidArgumentException
	 */
	public function testMultiGroupOneForOne_InvalidArgument($data) {
		Supervisor::multiGroupOneForOne($data);
	}
	public function dataProvider4testMultiGroupOneForOne_InvalidArgument() {
		return [
			[[]],
			[[[],[]]],
			[['x']],
			['x'],
		];
	}
	public function testMultiGroupOneForOne_CreateKeeper() {
		Supervisor::multiGroupOneForOne([
			['runnableCallback' => function() {exit;}],
			['runnableCallback' => function() {exit;}],
			['runnableCallback' => function() {exit;},'quantity' => 3, 'maxRestartTimes' => 3],
		]);
	}
	
	public function testMultiGroupOneForOne() {
		$cmd = sprintf("%s %s %s %s",
			PHP_BINDIR.'/php',
			__FILE__.'.script',
			escapeshellarg($this->_logFile),
			escapeshellarg($this->_logFile1)
		);
		exec($cmd);
		$this->assertEquals(1, preg_match('/^1{4,10}$/', file_get_contents($this->_logFile)),file_get_contents($this->_logFile));
		$this->assertEquals(1, preg_match('/^2{15,25}/', file_get_contents($this->_logFile1)),file_get_contents($this->_logFile1));
	}
}
