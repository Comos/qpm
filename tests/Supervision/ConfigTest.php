<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Supervision;

use Comos\Qpm\Supervision\Config;
use Comos\Qpm\Process\Runnable;

class ConfigTest extends \PHPUnit_Framework_TestCase implements Runnable {
	public function test__Construct() {
		$data = array('factory' => function(){return null;});
		$c = new Config($data);
		$this->assertEquals(1, $c->getQuantity());
		$this->assertTrue($c->getKeeperRestartPolicy() instanceof \Comos\Qpm\Supervision\KeeperRestartIgnoreAllPolicy);

		$data = array('worker' => __CLASS__);
		new Config($data);
	}

	public function testGetQuantity() {
		$func = function() {exit;};
		$configData = array('worker'=>$func, 'quantity'=> 10);
		$config = new Config($configData);
		$this->assertEquals(10, $config->getQuantity());
	}
	
	/**
     * @dataProvider dataProvider_isTimeoutEnabled
	 */
	public function testTimeout($timeout, $expectedEnabled, $expectedTimeout) {
	    $func = function() {exit;};
	    $configData = array('worker'=>$func, 'quantity'=> 10);
	    if (!\is_null($timeout)) {
	        $configData['timeout'] = $timeout;
	    }
	    $config = new Config($configData);
	    $this->assertTrue($expectedEnabled === $config->isTimeoutEnabled());
	    $this->assertEquals($expectedTimeout, $config->getTimeout());
	}
	
	public function dataProvider_isTimeoutEnabled() {
	    return array(
	        array(1,true, 1.0),
	        array(-1, false, -1.0),
	        array(0, false, 0.0),
	        array(null, false, -1.0),
	        array(1.1, true, 1.1),
	        array(0.005, true, 0.005),
	        array(0.0005, true, 0.0005),
	        array('1', true, 1.0),
	        array('1.5', true, 1.5),
	        array('0.0005', true, 0.0005),
	    );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage maxRestartTimes must be integer and cannot be zero
	 */
	public function test__Construct_MaxRestartTimesIsNotNumeric() {
		$data = array('factory' => function(){return null;}, 'maxRestartTimes' => 'x');
		new Config($data);
	}
	/**
	 * @expectedException Comos\Qpm\Supervision\OutOfPolicyException
	 */
	public function test__Construct_MaxRestartTimesIsStringButNumeric() {
		$data = array('factory' => function(){return null;}, 'maxRestartTimes' => '1');
		$c = new Config($data);
		$policy = $c->getKeeperRestartPolicy();
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
		$data = array('factory' => function(){return null;}, 'quantity' => -1);
		new Config($data);
	}

	/**
 	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage factory must be callable
	 */
	public function test__Construct_FactoryIsNotCallable() {
		$data = array('factory' => 'xx');
		new Config($data);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage worker must be instance of Comos\Qpm\Process\Runnable or callable
	 */
	public function test__Construct_WorkerIsNotRunnable() {
		$data = array('worker' => '\ArrayList');
		new Config($data);
	}

	public function testGetFactory() {
		$data = array('worker' => __CLASS__);
		$config = new Config($data);
		$method = $config->getFactoryMethod();
		$this->assertTrue(\is_callable($method));
		$target = call_user_func($method);
		$this->assertTrue(\is_array($target));
		$this->assertTrue(\is_callable($target));
	}
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage worker must be instance of Comos\Qpm\Process\Runnable or callable
	 */

	public function testGetFactory_InvalidRunnableCallback() {
		$callback = array();
		$data = array('worker' => $callback);
		$config = new Config($data);
	}

	public function testGetFactory_RunnableCallback() {
		$callback = function() {exit;};
		$data = array('worker' => $callback);
		$config = new Config($data);
		$factoryMethod = $config->getFactoryMethod();
		$this->assertTrue(\is_callable($factoryMethod));
		$callback1 = \call_user_func($factoryMethod);
		$this->assertEquals($callback, $callback1);
	}

	/**
	 * @dataProvider processLauncherMissedDataProvider
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage factory or worker is required.
	 */
	public function test__Construct_MissWayToStartNewProcess($data) {
		new Config($data);
	}
	public function processLauncherMissedDataProvider() {
		return array(
			array('_x'=>1),
			array('factory' => null),
			array('worker' => null),
		);
	}
}
