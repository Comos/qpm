<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Process\Status;

use Comos\Qpm\Process;

class StatusTest extends \PHPUnit_Framework_TestCase
{

    public function testAllMethods()
    {
        parent::setUp();
        $foo = function ()
        {
           \usleep(200 * 1000);
        };
        $child = Process\Process::fork($foo);
        $alive = $child->isAlive();
        $this->assertTrue($alive);
        
        $this->assertNull($child->getStatus()
            ->getTerminationSignal());
        $this->assertNull($child->getStatus()
            ->getExitCode());
        $this->assertNull($child->getStatus()
            ->getStopSignal());
        $this->assertEquals(0, $child->getStatus()
            ->getCode());
        $this->assertFalse($child->getStatus()
            ->isNormalExit());
        $this->assertFalse($child->getStatus()
            ->isSignaled());
        $this->assertFalse($child->getStatus()
            ->isStopped());
        
        usleep(350 * 1000);
        $this->assertFalse($child->isAlive());
        $this->assertFalse($child->getStatus()
            ->isStopped());
        $this->assertTrue($child->getStatus()
            ->isNormalExit());
        $this->assertEquals(0, $child->getStatus()
            ->getTerminationSignal());
        $this->assertEquals(1, $child->getStatus()
            ->getStopSignal());
        $this->assertFalse($child->getStatus()
            ->isSignaled());
        $this->assertEquals(1, $child->getStatus()
            ->getExitCode());
    }

    public function testMethods_ProcessIsStopped()
    {
        $this->markTestInComplete();
    }

    public function testMethods_ProcessIsTerminated()
    {
        $this->markTestInComplete();
    }

    public function testMethods_ProcessIsSignaled()
    {
        $this->markTestInComplete();
    }
}