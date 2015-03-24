<?php
/**
 * @author bigbigant
 */
require __DIR__.'/bootstrap.inc.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\LineFormatter;

$logger = new Logger('qpm');
$handler = new StreamHandler(__FILE__.'.log');
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

Comos\Qpm\Supervision\Supervisor::oneForOne(array('runnableCallback' =>  'doSomething'))->start();