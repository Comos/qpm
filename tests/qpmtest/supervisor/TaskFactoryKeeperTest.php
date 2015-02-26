<?php
namespace qpmtest\supervisor;
require_once 'qpm/supervisor/TaskFactoryKeeper.php';
require_once 'qpm/supervisor/StopSignal.php';
require_once 'qpm/process/Runnable.php';

use qpm\supervisor\TaskFactoryKeeper;
use qpm\supervisor\StopSignal;

class TaskFactoryKeeperTest extends \PHPUnit_Framework_TestCase {
	protected $_count = 0;
	public function mockFetchTask() {
		$count = $this->_count++;
		if ($count == 10) {
			throw new StopSignal();
		}
		if (0 == $count%3) {
			return null;
		}
		return new TaskFactoryKeeperTest_Task($count);
	}
	public function testKeep() {
		
	}
}

class TaskFactoryKeeperTest_Task implements \qpm\process\Runnable {
	public function __construct($id) {
	}
	public function run() {
	}
}
