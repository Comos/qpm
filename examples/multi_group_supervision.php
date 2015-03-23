<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';

$run1 = function ()
{
    echo "run1,pid:" . posix_getpid() . "\n";
    while (true) {
        echo "---run1,pid:" . posix_getpid() . "\n";
        sleep(3);
        exit();
    }
};
$run2 = function ()
{
    while (true) {
        echo "+++run2,pid:" . posix_getpid() . "\n";
        sleep(3);
        exit();
    }
};

$configs = [
    [
        'runnableCallback' => $run1
    ],
    [
        'runnableCallback' => $run2,
        'quantity' => 2
    ]
];

Qpm\Supervision\Supervisor::multiGroupOneForOne($configs)->start();
