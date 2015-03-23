<?php
/**
 * @author bigbigant
 */
use Comos\Qpm\Process\Runnable;
require __DIR__ . '/bootstrap.inc.php';

/**
 * 任务工厂，必须实现 fetchTask方法。
 * 该方法正常返回
 */
class SpiderTaskFactory
{

    private $_fh;

    public function __construct($input)
    {
        $this->_input = $input;
        $this->_fh = fopen($input, 'r');
        if ($this->_fh === false) {
            throw new Exception('fopen failed:' . $input);
        }
    }

    public function fetchTask()
    {
        while (true) {
            if (feof($this->_fh)) {
                throw new Comos\Qpm\Supervision\StopSignal();
            }
            $line = trim(fgets($this->_fh));
            if ($line == 'END') {
                throw new Comos\Qpm\Supervision\StopSignal();
            }
            
            if (empty($line)) {
                continue;
            }
            
            break;
        }
        
        return new SpiderTask($line);
    }
}

/**
 * 在子进程中执行任务的类
 * 必须实现 Comos\Qpm\Process\Runnable 接口
 */
class SpiderTask implements Comos\Qpm\Process\Runnable
{

    private $_target;

    public function __construct($target)
    {
        $this->_target = $target;
    }
    // 在子进程中执行的部分
    public function run()
    {
        $r = @file_get_contents($this->_target);
        if ($r === false) {
            throw new Exception('fail to crawl url:' . $this->_target);
        }
        file_put_contents($this->getLocalFilename(), $r);
    }

    private function getLocalFilename()
    {
        $filename = str_replace('/', '~', $this->_target);
        $filename = str_replace(':', '_', $filename);
        $filename = $filename . '-' . date('YmdHis');
        return __DIR__ . '/_spider/' . $filename . '.html';
    }
}

// 如果没有从参数指定输入，把spider_task_factory_data.txt作为数据源
$input = isset($argv[1]) ? $argv[1] : __DIR__ . '/spider_task_factory_data.txt';

$spiderTaskFactory = new SpiderTaskFactory($input);
$config = array(
    // 指定taskFactory对象和工厂方法
    'factoryMethod' => array(
        $spiderTaskFactory,
        'fetchTask'
    ),
    // 指定最大并发数量为3
    'quantity' => 3
);
// 启动Supervisor
Comos\Qpm\Supervision\Supervisor::taskFactoryMode($config)->start();
