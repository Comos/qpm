<?php
require __DIR__.'/bootstrap.inc.php';
use qpm\pidfile\Manager;
$man = new Manager(__FILE__.'.pid');
$man->start();
while(true) sleep(10);
