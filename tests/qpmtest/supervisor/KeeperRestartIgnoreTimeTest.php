<?php
namespace qpmtest\supervisor;
require_once 'qpm/supervisor/KeeperRestartPolicy.php';
use \qpm\supervisor\KeeperRestartPolicy;

class KeeperRestartIgnoreTimePolicyTest extends \PhpUnit_Framework_TestCase {
	protected $_policy,$_policy1;
	protected function setUp() {
		$this->_policy = KeeperRestartPolicy::create(3, -1);
		$this->_policy1 = KeeperRestartPolicy::create(1, -1);
	}
	
	public function testCreate() {
		$this->assertTrue($this->_policy instanceof \qpm\supervisor\KeeperRestartIgnoreTimePolicy);
		$this->assertTrue($this->_policy1 instanceof \qpm\supervisor\KeeperRestartIgnoreTimePolicy);
	}
	
	public function testCheck() {
		$i = 3;
		while($i--) { 
			$this->_policy->check();
		}
		$this->_policy1->check();
	}
	/**
	 * @expectedException \qpm\supervisor\OutOfPolicyException
	 */
	public function testCheck_OutOfPolicy() {
		$i = 4;
		while($i--) {
			$this->_policy->check();
		}
	}

	/**
	 * @expectedException \qpm\supervisor\OutOfPolicyException
	 */
	public function testCheck_OutOfPolicy1() {
		$i = 2;
		while($i--) {
			$this->_policy1->check();
		}
	}

	public function testReset() {
		$i = 3;
		while($i--) {
			$this->_policy->check();
		}
		$this->_policy->reset();
		$this->_policy->check();
	}
	public function testReset1() {
		$i = 3;
		while($i--) {
			$this->_policy->check();
		}
		$this->_policy->reset();
		$i = 4;
		try {
			while($i--) {
				$this->_policy->check();
			}
			$this->fail();
		} catch(\Exception $ex) {
			$this->assertEquals(0, $i);
			$this->assertTrue($ex instanceof \qpm\supervisor\OutOfPolicyException);
		}
		
		$this->_policy1->check();
		$this->_policy1->reset();			
		$this->_policy1->check();
		try {
			$this->_policy1->check();
			$this->fail();
		} catch(\Exception $ex) {
			$this->assertTrue($ex instanceof \qpm\supervisor\OutOfPolicyException);
		}
	}
	
}
