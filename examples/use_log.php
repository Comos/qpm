<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';

$logFile = __FILE__ . '-simple-Logger.log';

Comos\Qpm\Log\Logger::useSimpleLogger($logFile);

echo "Execute 'tail $logFile' to see the lastest logs.\n";

$func = function ()
{
    $i = 3;
    while (-- $i) {
        sleep(1);
    }
};
try {
    Comos\Qpm\Supervision\Supervisor::oneForOne(array(
        'worker' => $func,
        'quantity' => 3,
        'maxRestartTimes' => 3
    ))->start();
} catch (Exception $ex) {
    Comos\Qpm\Log\Logger::err($ex);
}
