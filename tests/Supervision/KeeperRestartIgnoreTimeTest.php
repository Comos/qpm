<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Supervision;

use \Comos\Qpm\Supervision\KeeperRestartPolicy;

class KeeperRestartIgnoreTimePolicyTest extends \PhpUnit_Framework_TestCase {
	protected $_policy,$_policy1;
	protected function setUp() {
		$this->_policy = KeeperRestartPolicy::create(3, -1);
		$this->_policy1 = KeeperRestartPolicy::create(1, -1);
	}
	
	public function testCreate() {
		$this->assertTrue($this->_policy instanceof \Comos\Qpm\Supervision\KeeperRestartIgnoreTimePolicy);
		$this->assertTrue($this->_policy1 instanceof \Comos\Qpm\Supervision\KeeperRestartIgnoreTimePolicy);
	}
	
	public function testCheck() {
		$i = 3;
		while($i--) { 
			$this->_policy->check();
		}
		$this->_policy1->check();
	}
	/**
	 * @expectedException \Comos\Qpm\Supervision\OutOfPolicyException
	 */
	public function testCheck_OutOfPolicy() {
		$i = 4;
		while($i--) {
			$this->_policy->check();
		}
	}

	/**
	 * @expectedException \Comos\Qpm\Supervision\OutOfPolicyException
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
			$this->assertTrue($ex instanceof \Comos\Qpm\Supervision\OutOfPolicyException);
		}
		
		$this->_policy1->check();
		$this->_policy1->reset();			
		$this->_policy1->check();
		try {
			$this->_policy1->check();
			$this->fail();
		} catch(\Exception $ex) {
			$this->assertTrue($ex instanceof \Comos\Qpm\Supervision\OutOfPolicyException);
		}
	}
	
}
