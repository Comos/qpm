<?php
/**
 * @author bigbigant
 */

require __DIR__.'/bootstrap.inc.php';

$func = function() {
	qpm\process\Process::current()->toBackground();
	$current = qpm\process\Process::current();
	for($i = 0; $i<=20; $i++) {
		sleep(2);
		echo "PID:", $current->getPid(), "\t",
			"OriginalPPID:", $current->getParent()->getPid(), "\t",
			"PPID:", posix_getppid(), "\t",
			time(), "\n";
	}
};

qpm\process\Process::fork($func);
sleep(1);
echo posix_getpid()."\tbye\n";
exit();
