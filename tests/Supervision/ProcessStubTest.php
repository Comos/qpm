<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Supervision;

class ProcessStubTest extends \PHPUnit_Framework_TestCase

{

    protected function mockOfProcess()
    {
        return $this->getMockBuilder('\\Comos\Qpm\\Process\\Process')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    protected function mockOfConfig()
    {
        return $this->getMockBuilder('\\Comos\Qpm\\Supervision\\Config')
            ->setMethods(array('getTimeout'))
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    public function testIsTimeout()
    {
        $timeout = 5;
        $config = $this->mockOfConfig();
        $config->expects($this->any())
            ->method('getTimeout')
            ->will($this->returnValue($timeout));
        $now = \microtime(true);
        $startTime = $now - 10;
        
        $stub = new ProcessStub($this->mockOfProcess(), $config, 1, $startTime);
        $this->assertTrue($stub->isTimeout());
    }
    
    public function testIsTimeout_DefaultStartTime()
    {
        $timeout = 1;
        $config = $this->mockOfConfig();
        $config->expects($this->any())
        ->method('getTimeout')
        ->will($this->returnValue($timeout));
        
        $stub = new ProcessStub($this->mockOfProcess(), $config);
        $this->assertFalse($stub->isTimeout());
        \usleep(1100*1000);
        $this->assertTrue($stub->isTimeout());
    }
}