<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';

$run1 = function ()
{
    echo "run1,pid:" . \posix_getpid() . "\n";
    while (true) {
        echo "---run1,pid:" . \posix_getpid() . "\n";
        sleep(3);
        exit();
    }
};
$run2 = function ()
{
    while (true) {
        echo "+++run2,pid:" . \posix_getpid() . "\n";
        sleep(3);
        exit();
    }
};

$configs = array(
    array(
        'runnableCallback' => $run1
    ),
    array(
        'runnableCallback' => $run2,
        'quantity' => 2
    ),
);

Comos\Qpm\Supervision\Supervisor::multiGroupOneForOne($configs)->start();
