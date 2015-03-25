<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';

$run = function ()
{
    $i = 3;
    while ($i --) {
        echo "#$i PID:" . posix_getpid() . "\n";
        sleep(1);
    }
};

$config = array(
    'worker' => $run,
    'quantity' => 3,
    'maxRestartTimes' => 30,
);

Comos\Qpm\Supervision\Supervisor::oneForOne($config)->start();
