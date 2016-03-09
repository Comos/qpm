<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Process;

class ProcessTest extends \PHPUnit_Framework_TestCase
{

    protected $_logFile;

    protected function setUp()
    {
        $this->_logFile = __FILE__ . '.log';
        if (false === \file_put_contents($this->_logFile, '')) {
            throw new \Exception('fail to init log file:' . $this->_logFile);
        }
    }

    protected function tearDown()
    {
        $st = 0;
        while (true) {
            $r =\pcntl_wait($st, WNOHANG);
            if ($r == 0 || $r == - 1) {
                break;
            }
        }
        ;
        @unlink($this->_logFile);
    }

    public function testGetParent_GetParentOfCurrent()
    {
        $current = Process::current();
        $current1 = Process::process($current->getPid());
        $parent = $current->getParent();
        $parent1 = $current1->getParent();

        $this->assertInstanceOf(get_class($current), $parent);
        $this->assertInternalType('int', $parent->getPid());
        $this->assertEquals($parent1->getPid(), $parent->getPid());
        $this->assertGreaterThan(0, $parent->getPid());
    }

    public function testGetParent()
    {
        $process = Process::fork(function(){usleep(1000);});
        $parent = $process->getParent();
        $this->assertEquals($parent->getPid(), Process::current()->getPid());
        pcntl_wait($st);
    }

    public function testGetParent_ProcessInBackground()
    {
        $process = Process::fork(function(){
            Process::toBackground();
            usleep(5000);
        });
        usleep(2000);
        $parent = Process::process($process->getPid())->getParent();
        $this->assertNull($parent);
    }

    public function testIsCurrent()
    {
        $this->assertTrue(Process::current()->isCurrent());
    }

    public function testProcess()
    {
        $this->assertTrue(Process::process(posix_getpid()) instanceof Process);
    }

    public function testCurrent()
    {
        $current = Process::current();
        $current1 = Process::current();
        $this->assertEquals($current, $current1);
        $this->assertTrue(is_int($current->getPid()));
        $this->assertTrue($current instanceof Process);
    }

    public function testCurrent_AfterFork()
    {
        $logFile = $this->_logFile;
        $child = Process::fork(function () use($logFile)
        {
            \file_put_contents($logFile, Process::current()->getPid());
        });
        \usleep(1000 * 20);
        $pidFromLogFile = file_get_contents($this->_logFile);
        $this->assertEquals($child->getPid(), $pidFromLogFile);
        $this->assertEquals(Process::current()->getPid(), posix_getpid());
        $this->assertNotEquals($child->getPid(), Process::current()->getPid());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $target must be a valid callback or Comos\Qpm\Process\Runnable
     */
    public function testForkByCallable_Arg0NotAValidCallable()
    {
        Process::fork('x');
    }

    public function testForkByCallable()
    {
        $current = Process::current();
        $logFile = $this->_logFile;
        $func = function () use($logFile)
        {
            usleep(500 * 1000);
            \file_put_contents($logFile, 'x', \FILE_APPEND);
        };
        Process::fork($func);
        Process::fork($func);
        $c = \file_get_contents($this->_logFile);
        $this->assertEquals('', $c);
        \usleep(800 * 1000);
        $c = \file_get_contents($this->_logFile);
        $this->assertEquals('xx', $c);
    }

    public function testFork()
    {
        $current = Process::current();
        Process::fork(new ProcessTest_Runnable($this->_logFile));
        Process::fork(new ProcessTest_Runnable($this->_logFile));
        $c = \file_get_contents($this->_logFile);
        $this->assertEquals('', $c);
        usleep(800 * 1000);
        $c = \file_get_contents($this->_logFile);
        $this->assertEquals('YY', $c);
    }

    public function testIsCurrent_False()
    {
        $current = Process::current();
        $logFile = $this->_logFile;
        $child = Process::fork(function () use($current, $logFile)
        {
            \file_put_contents($logFile, $current->isCurrent() ? 'yes' : 'no');
        });
        $st = 0;
        $wpid =\pcntl_wait($st);
        $this->assertEquals('no', \file_get_contents($this->_logFile));
    }

    public function testTerminate()
    {
        $child = Process::fork(function () {\usleep(1000);});
        $child->terminate();
        $pid =\pcntl_wait($st);
        $this->assertEquals(\SIGTERM, \pcntl_wtermsig($st));
        $this->assertEquals($pid, $child->getPid());
    }

    public function testKill()
    {
        $t0 = microtime(true);
        $child = Process::fork(function ()
        {
            \usleep(0.1 * 1000 * 1000);
        });
        $child->kill();
        $st = 0;
        $wpid =\pcntl_wait($st);
        $t1 = microtime(true);
        $this->assertEquals($wpid, $child->getPid());
        $this->assertLessThan(0.05, $t1 - $t0);
    }

    public function testSendSignal_Failed()
    {
        $child = Process::fork(function ()
        {
            \usleep(0.1 * 1000 * 1000);
        });
        $errorSignal = 1001;
        try {
            $child->sendSignal($errorSignal);
        }catch (\Exception $ex) {
            $this->assertTrue($ex instanceof FailToSendSignalException );
        }
        \pcntl_wait($st);
    }
}

class ProcessTest_Runnable implements Runnable
{

    public function __construct($file)
    {
        $this->_logFile = $file;
    }

    /**
     *
     * @see \Comos\Qpm\Process\Runnable::run()
     */
    public function run()
    {
        \usleep(500 * 1000);
        \file_put_contents($this->_logFile, 'Y', FILE_APPEND);
    }
}
