<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Supervision;
use \Comos\Qpm\Supervision\KeeperRestartPolicy;

class KeeperRestartPolicyTest extends \PhpUnit_Framework_TestCase {
	protected $_policy, $_policyMax2WithIn3;
	protected function setUp() {
		$this->_policy = KeeperRestartPolicy::create(1, 1);
		$this->_policyMax3WithIn2 = KeeperRestartPolicy::create(3, 2);
	}
	/**
	 * @testdox
	 */	
	public function testCheck() {
		$this->_policy->check();
		$this->_policyMax3WithIn2->check();

		usleep(1000*1010);

		$this->_policy->check();

		$this->_policyMax3WithIn2->check();
		$this->_policyMax3WithIn2->check();
	}
	/**
	 * @expectedException \Comos\Qpm\Supervision\OutOfPolicyException
	 */
	public function testCheck_OutOfPolicy() {
		$this->_policy->check();
		$this->_policy->check();
	}
	
	public function testCheck_Mix() {
		$this->_policyMax3WithIn2->check();	
		$this->_policyMax3WithIn2->check();
		$this->_policyMax3WithIn2->check();
		usleep(1000*1001*3);
		$this->_policyMax3WithIn2->check();
		$this->_policyMax3WithIn2->check();
		$this->_policyMax3WithIn2->check();
		
		usleep(1000*1001*3);
		$this->_policyMax3WithIn2->check();
		$this->_policyMax3WithIn2->check();
		$this->_policyMax3WithIn2->check();
		
		$this->setExpectedException('\Comos\Qpm\Supervision\OutOfPolicyException');
		usleep(1000);	
		$this->_policyMax3WithIn2->check();
	}
}
