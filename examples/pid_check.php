<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';
use Comos\Qpm\pidfile\Manager;
$man = new Manager(__DIR__ . '/pid_main.php.pid');
echo $man->getProcess()->getPid();
