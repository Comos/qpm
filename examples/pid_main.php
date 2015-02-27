<?php
require __DIR__.'/bootstrap.inc.php';
require_once 'qpm/pidfile/Manager.php';
use qpm\pidfile\Manager;
$man = new Manager(__FILE__.'.pid');
$man->start();
while(true) sleep(10);
