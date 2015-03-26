<?php
/**
 * @author bigbigant
 */
require __DIR__.'/bootstrap.inc.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\LineFormatter;

$logFile = __FILE__.'.log';
echo "Running... Use ctrl+c to quit.
Execute 'tail $logFile' to see the lastest logs.\n";

$logger = new Logger('qpm');
$handler = new StreamHandler($logFile);
new LineFormatter();
$logger->pushHandler($handler);
$formatter = new LineFormatter();
$handler->setFormatter($formatter);

Comos\Qpm\Log\Logger::setLoggerImpl($logger);

function doSomething()
{
  sleep(1);
  throw new Exception('xxx');
}

Comos\Qpm\Supervision\Supervisor::oneForOne(array('worker' =>  'doSomething'))->start();