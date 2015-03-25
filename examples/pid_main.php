<?php
/**
 * @author bigbigant
 */

require __DIR__ . '/bootstrap.inc.php';
use Comos\Qpm\Pid\Manager;

echo "Process is running. Ctrl+c to quit.
You can execute `php pid_check.php` to check the PID of current process.\n";

$man = new Manager(__FILE__ . '.pid');
$man->start();
while (true) {
    sleep(10);
}
