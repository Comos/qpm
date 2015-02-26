<?php
namespace qpmtest\supervisor;
require_once 'qpm/supervisor/Config.php';
require_once 'qpm/process/Runnable.php';
use qpm\supervisor\Config;
use qpm\process\Runnable;

class ConfigTest extends \PHPUnit_Framework_TestCase implements Runnable {
	public function test__Construct() {
		$data = array('factoryMethod' => function(){return null;});
		$c = new Config($data);
		$this->assertEquals(1, $c->getQuantity());
		$this->assertTrue($c->getKeeperRestartPolicy() instanceof \qpm\supervisor\KeeperRestartIgnoreAllPolicy);

		$data = array('runnableClass' => __CLASS__);
		new Config($data);
	}

	public function testGetQuantity() {
		$func = function() {exit;};
		$configData = array('runnableCallback'=>$func, 'quantity'=> 10);
		$config = new Config($configData);
		$this->assertEquals(10, $config->getQuantity());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage maxRestartTimes must be integer and cannot be null
	 */
	public function test__Construct_MaxRestartTimesIsNotNumeric() {
		$data = array('factoryMethod' => function(){return null;}, 'maxRestartTimes' => 'x');
		new Config($data);
	}
	/**
	 * @expectedException qpm\supervisor\OutOfPolicyException
	 */
	public function test__Construct_MaxRestartTimesIsStringButNumeric() {
		$data = array('factoryMethod' => function(){return null;}, 'maxRestartTimes' => '1');
		$policy = (new Config($data))->getKeeperRestartPolicy();
		try {
			$policy->check();
		} catch(\Exception $ex) {
			$this->fail();
		}
		$policy->check();
	}

	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage quantity must be positive integer
	 */
	public function test__Construct_QuantityMustBePositiveInteger() {
		$data = array('factoryMethod' => function(){return null;}, 'quantity' => -1);
		new Config($data);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage withInSeconds must be integer and cannot be null
	 */
	public function test__Construct_WithInSecondsMustBeIntegerAndCannotBeNull() {
		$data = array('factoryMethod' => function(){return null;}, 'withInSeconds' => 3.1);
		new Config($data);
	}

	/**
 	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage factoryMethod must be callable
	 */
	public function test__Construct_FactoryMethodIsNotCallable() {
		$data = ['factoryMethod' => 'xx'];
		new Config($data);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage runnableClass must be an implemention of \qpm\process\Runnable
	 */
	public function test__Construct_RunnableClassIsNotRunnable() {
		$data = ['runnableClass' => '\ArrayList'];
		new Config($data);
	}

	public function testGetFactoryMethod() {
		$data = ['runnableClass' => __CLASS__];
		$config = new Config($data);
		$method = $config->getFactoryMethod();
		$this->assertTrue(\is_callable($method));
		$target = call_user_func($method);
		$this->assertTrue(\is_array($target));
		$this->assertTrue(\is_callable($target));
	}
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage runnableCallback must be callable
	 */

	public function testGetFactoryMethod_InvalidRunnableCallback() {
		$callback = array();
		$data = ['runnableCallback' => $callback];
		$config = new Config($data);
	}

	public function testGetFactoryMethod_RunnableCallback() {
		$callback = function() {exit;};
		$data = ['runnableCallback' => $callback];
		$config = new Config($data);
		$factoryMethod = $config->getFactoryMethod();
		$this->assertTrue(\is_callable($factoryMethod));
		$callback1 = \call_user_func($factoryMethod);
		$this->assertEquals($callback, $callback1);
	}

	/**
	 * @dataProvider processLauncherMissedDataProvider
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage the way to start new process is required. optional ways: factoryMethod, runnableClass or runnableCallback
	 */
	public function test__Construct_MissWayToStartNewProcess($data) {
		new Config($data);
	}
	public function processLauncherMissedDataProvider() {
		return [
			['_x'=>1],
			['factoryMethod' => null],
			['runnableClass' => null],
			['runnableCallback' => null],
		];
	}
}
