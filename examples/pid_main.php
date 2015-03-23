<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';
use Qpm\pidfile\Manager;
$man = new Manager(__FILE__ . '.pid');
$man->start();
while (true) {
    sleep(10);
}
