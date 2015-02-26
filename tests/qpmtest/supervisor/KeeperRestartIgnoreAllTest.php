<?php
namespace qpmtest\supervisor;
require_once 'qpm/supervisor/KeeperRestartPolicy.php';
use \qpm\supervisor\KeeperRestartPolicy;

class KeeperRestartIgnoreAllPolicyTest extends \PhpUnit_Framework_TestCase {
	protected $_policy,$_policy1;
	protected function setUp() {
		$this->_policy = KeeperRestartPolicy::create(-1, -1);
		$this->_policy1 = KeeperRestartPolicy::create(-1, 1);
	}
	
	public function testCreate() {
		$this->assertTrue($this->_policy instanceof \qpm\supervisor\KeeperRestartIgnoreAllPolicy);
		$this->assertTrue($this->_policy1 instanceof \qpm\supervisor\KeeperRestartIgnoreAllPolicy);
	}
	
	public function testCheck() {
		$i = 10;
		while($i--) { 
			$this->_policy->check();
			$this->_policy1->check();
		}
	}
	public function testReset() {
		$this->_policy->check();
		$this->_policy->reset();
	}
}
