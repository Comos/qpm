<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Log;

use Comos\Qpm\Log\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase {
	protected $_logFile; 
	protected function setUp()
    {
		parent::setUp();
		$this->_logFile = __FILE__.'.log';
		file_put_contents($this->_logFile, '');
	}

	public function testUseNullLogger() {
		Logger::useNullLogger();
		Logger::info("abc");
		try {
			throw new \Exception();
		} catch (\Exception $ex) {
			Logger::info($ex);
		}
		$this->assertEquals(0, filesize($this->_logFile));
	}

	public function testUseSimpleLogger()
    {
		Logger::useSimpleLogger($this->_logFile);
		Logger::info('xxinfoxx');
		Logger::err('xxerrxx');
		Logger::debug('xxdebugxx');
		$contents = file_get_contents($this->_logFile);
		$ms = null;
		$this->assertEquals(1, preg_match('/xxinfoxx/', $contents, $ms));
		$this->assertEquals(1, preg_match('/xxerrxx/', $contents, $ms));
		$this->assertEquals(1, preg_match('/xxdebugxx/', $contents, $ms));
		Logger::useNullLogger();
		Logger::info('bbbb');
		$contents1 = file_get_contents($this->_logFile);
		$this->assertEquals($contents1, $contents);
	}

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Logger Impl must be instance of Psr\Log\LoggerInterface
     */
    public function testSetLoggerImpl()
    {
        $obj = new \ArrayObject();
        Logger::setLoggerImpl($obj);
    }

	protected function tearDown() {
		Logger::useNullLogger();
		@unlink($this->_logFile);
		parent::tearDown();
	}
}
