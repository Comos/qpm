<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';

Qpm\Log\Logger::useSimpleLogger(__FILE__ . '-simple-Logger.log');

$func = function ()
{
    $i = 3;
    while (-- $i) {
        sleep(1);
    }
};
try {
    Qpm\Supervision\Supervisor::oneForOne([
        'runnableCallback' => $func,
        'quantity' => 3,
        'maxRestartTimes' => 3
    ])->start();
} catch (Exception $ex) {
    Qpm\Log\Logger::err($ex);
}
