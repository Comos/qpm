<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Supervision;

use Comos\Qpm\Supervision\Supervisor;
use Comos\Qpm\Supervision\StopSignal;
use Comos\Qpm\Process\Process;

class TaskFactoryKeeperTest extends \PHPUnit_Framework_TestCase
{

    protected $logFile;
    /**
     * 
     * @var Process[]
     */
    protected $timeoutProcesses;
    
    /**
     * @var integer For fetchTask methods
     */
    protected $lastTaskId = 0;

    protected function setUp()
    {
        parent::setUp();
        $this->logFile = __FILE__ . '.log';
        @\unlink($this->logFile);
        $this->lastTaskId = 0;
        $this->timeoutProcesses = array();
    }

    protected function tearDown()
    {
        @\unlink($this->logFile);
    }

    

    public function fetchTask()
    {
        $count = $this->lastTaskId ++;
        if ($count == 10) {
            throw new StopSignal();
        }
        if (0 == $count % 3) {
            return null;
        }
        return new TaskFactoryKeeperTest_Task($count, $this->logFile);
    }

    public function testRun()
    {
        Supervisor::taskFactoryMode(array(
            'quantity' => 3,
            'factoryMethod' => array(
                $this,
                'fetchTask'
            )
        ))->start();
        $content =\file_get_contents($this->logFile);
        $arr = array_filter(explode(',', $content), function ($i)
        {
            return $i !== '';
        });
        sort($arr);
        $this->assertEquals(array(
            '1',
            '2',
            '4',
            '5',
            '7',
            '8'
        ), $arr);
    }

    public function fetchTask_WithSleeping()
    {
        $count = $this->lastTaskId ++;
        if ($count == 15) {
            throw new StopSignal();
        }
        if (0 == $count % 3) {
            return null;
        }
        $sleepTime = in_array($count, array(
            2,
            8,
            11
        )) ? 1.1 : 0;
        return new TaskFactoryKeeperTest_Task($count, $this->logFile, $sleepTime);
    }

    public function testRun_WithTimeout()
    {
        Supervisor::taskFactoryMode(array(
            'quantity' => 3,
            'timeout' => 1,
            'factoryMethod' => array(
                $this,
                'fetchTask_WithSleeping'
            )
        ))->start();
        $content =\file_get_contents($this->logFile);
        $arr = array_filter(explode(',', $content), function ($i)
        {
            return $i !== '';
        });
        sort($arr);
        $this->assertEquals(array(
            '1',
            '4',
            '5',
            '7',
            '10',
            '13',
            '14'
        ), $arr);
    }
    
    public function testRun_WithTimeout_WithOnTimeout()
    {
        Supervisor::taskFactoryMode(array(
        'quantity' => 3,
        'timeout' => 1,
        'onTimeout' => array($this, 'onTimeout'),
        'factoryMethod' => array($this, 'fetchTask_WithSleeping'),
        ))->start();
        $content =\file_get_contents($this->logFile);
        $arr = array_filter(explode(',', $content), function ($i)
        {
            return $i !== '';
        });
        sort($arr);
        $this->assertEquals(array(
            '1',
            '4',
            '5',
            '7',
            '10',
            '13',
            '14'
        ), $arr);
        $this->assertEquals(3, \count($this->timeoutProcesses));
    }
    
    public function onTimeout($process) {
        $this->timeoutProcesses[] = $process;
    }
}

class TaskFactoryKeeperTest_Task implements \Comos\Qpm\Process\Runnable
{

    private $id;

    private $logFile;

    private $sleepTime;

    public function __construct($id, $logFile, $sleepTime = 0)
    {
        $this->id = $id;
        $this->logFile = $logFile;
        $this->sleepTime = $sleepTime;
    }

    public function run()
    {
        if ($this->sleepTime) {
            usleep($this->sleepTime * 1000 * 1000);
        }
        file_put_contents($this->logFile, $this->id . ',', \FILE_APPEND);
    }
}
