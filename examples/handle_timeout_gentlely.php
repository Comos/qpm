<?php
use Comos\Qpm\Supervision\Supervisor;
require __DIR__ . '/bootstrap.inc.php';

Supervisor::oneForOne(array(
    'timeout' => 5,
    'termTimeout' => 3,
    'worker' => function ()
    {
        declare(ticks = 1);
        $GLOBALS['stop'] = 0;
        pcntl_signal(SIGTERM, function ()
        {
            echo "TERM\n";
            $GLOBALS['stop'] = 1;
        });
        while (! $GLOBALS['stop']) {
            sleep(1);
            echo '.';
        }
        echo "BYE\n";
    }
))->start();