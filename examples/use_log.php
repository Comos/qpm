<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';

Comos\Qpm\Log\Logger::useSimpleLogger(__FILE__ . '-simple-Logger.log');

$func = function ()
{
    $i = 3;
    while (-- $i) {
        sleep(1);
    }
};
try {
    Comos\Qpm\Supervision\Supervisor::oneForOne(array(
        'runnableCallback' => $func,
        'quantity' => 3,
        'maxRestartTimes' => 3
    ))->start();
} catch (Exception $ex) {
    Comos\Qpm\Log\Logger::err($ex);
}
