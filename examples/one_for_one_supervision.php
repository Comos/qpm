<?php
require __DIR__ . '/bootstrap.inc.php';

$run = function ()
{
    $i = 10;
    while ($i --) {
        echo "#$i PID:" . posix_getpid() . "\n";
        sleep(1);
    }
};

$config = [
    'runnableCallback' => $run,
    'quantity' => 3
];

qpm\supervisor\Supervisor::oneForOne($config)->start();
