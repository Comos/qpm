<?php
require __DIR__ . '/bootstrap.inc.php';

qpm\log\Logger::useSimpleLogger(__FILE__ . '-simple-logger.log');

$func = function ()
{
    $i = 3;
    while (-- $i) {
        sleep(1);
    }
};
try {
    qpm\supervisor\Supervisor::oneForOne([
        'runnableCallback' => $func,
        'quantity' => 3,
        'maxRestartTimes' => 3
    ])->start();
} catch (Exception $ex) {
    qpm\log\Logger::err($ex);
}
