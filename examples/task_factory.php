<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';

qpm\log\Logger::useSimpleLogger(__FILE__ . '.log');

class Task implements \qpm\process\Runnable
{

    public function __construct($taskId, $sleepTime)
    {
        $this->_taskId = $taskId;
        $this->_sleepTime = $sleepTime;
    }

    public function run()
    {
        $time = date("H:i:s");
        sleep($this->_sleepTime);
        file_put_contents(__FILE__ . '.log', sprintf("%s\t%d\t%d\n", $time, $this->_taskId, $this->_sleepTime), FILE_APPEND);
    }
}

class TaskFactory
{

    private $_plan = [
        1,
        1,
        1,
        3,
        5,
        5,
        null,
        5,
        6,
        7,
        null,
        null,
        null,
        8
    ];

    private $_index = 0;

    private $_lastIsNull = false;

    public function fetchTask()
    {
        $task = $this->_doFetchTask();
        if ($task) {
            $this->_lastIsNull == false;
            return $task;
        }
        if (! $this->_lastIsNull) {
            $this->_lastIsNull = true;
            return null;
        }
        sleep(1);
        return null;
    }

    public function _doFetchTask()
    {
        $index = $this->_index;
        $this->_index ++;
        if (! isset($this->_plan[$index])) {
            return null;
        }
        echo "new Task($index, \$this->_plan[{$index}]\n";
        return new Task($index, $this->_plan[$index]);
    }
}
$taskFactory = new TaskFactory();
$config = [
    'factoryMethod' => [
        $taskFactory,
        'fetchTask'
    ],
    'quantity' => 3
];

qpm\supervisor\Supervisor::taskFactoryMode($config)->start();
