<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Supervision;

use Comos\Qpm\Supervision\Config;
use Comos\Qpm\Process\Runnable;

class ConfigTest extends \PHPUnit_Framework_TestCase implements Runnable {
	public function test__Construct() {
		$data = array('factoryMethod' => function(){return null;});
		$c = new Config($data);
		$this->assertEquals(1, $c->getQuantity());
		$this->assertTrue($c->getKeeperRestartPolicy() instanceof \Comos\Qpm\Supervision\KeeperRestartIgnoreAllPolicy);

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
     * @dataProvider dataProvider_isTimeoutEnabled
	 */
	public function testTimeout($timeout, $expectedEnabled, $expectedTimeout) {
	    $func = function() {exit;};
	    $configData = array('runnableCallback'=>$func, 'quantity'=> 10);
	    if (!\is_null($timeout)) {
	        $configData['timeout'] = $timeout;
	    }
	    $config = new Config($configData);
	    $this->assertTrue($expectedEnabled === $config->isTimeoutEnabled());
	    $this->assertEquals($expectedTimeout, $config->getTimeout());
	}
	
	public function dataProvider_isTimeoutEnabled() {
	    return array(
	        array(1,true, 1),
	        array(-1, false, -1),
	        array(0, false, 0),
	        array(null, false, -1),
	    );
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
	 * @expectedException Comos\Qpm\Supervision\OutOfPolicyException
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
		$data = array('factoryMethod' => 'xx');
		new Config($data);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage runnableClass must be an implemention of Comos\Qpm\Process\Runnable
	 */
	public function test__Construct_RunnableClassIsNotRunnable() {
		$data = array('runnableClass' => '\ArrayList');
		new Config($data);
	}

	public function testGetFactoryMethod() {
		$data = array('runnableClass' => __CLASS__);
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
		$data = array('runnableCallback' => $callback);
		$config = new Config($data);
	}

	public function testGetFactoryMethod_RunnableCallback() {
		$callback = function() {exit;};
		$data = array('runnableCallback' => $callback);
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
		return array(
			array('_x'=>1),
			array('factoryMethod' => null),
			array('runnableClass' => null),
			array('runnableCallback' => null),
		);
	}
}
