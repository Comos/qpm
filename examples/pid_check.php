<?php
require __DIR__.'/bootstrap.inc.php';
require_once 'qpm/pidfile/Manager.php';
use qpm\pidfile\Manager;
$man = new Manager(__DIR__.'/pid_main.php.pid');
echo $man->getProcess()->getPid();
