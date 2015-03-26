<?php
use Comos\Qpm\Supervision\Supervisor;
use Comos\Qpm\Process\Runnable;
use Comos\Qpm\Log\Logger;

require __DIR__ . '/bootstrap.inc.php';

Logger::useSimpleLogger(__FILE__.'.log');

class Task implements Runnable
{

    private $stop = false;

    public function run()
    {
        declare(ticks = 1);
        pcntl_signal(SIGTERM, array(
            $this,
            'onTerm'
        ));
        echo "B";
        while (! $this->stop) {
            sleep(1);
            echo '.';
        }
        echo "BYE\n";
    }

    public function onTerm()
    {
        echo "TERM\n";
        $this->stop = true;
    }
}

Supervisor::taskFactoryMode(array(
    'timeout' => 5,
    'termTimeout' => 1,
    'quantity' => 3,
    'worker' => 'Task',
))->start();