<?php
/**
 * @author bigbigant
 */

require __DIR__.'/bootstrap.inc.php';

$func = function() {
	Comos\Qpm\Process\Process::toBackground();
	$current = Comos\Qpm\Process\Process::current();
	for($i = 0; $i<=20; $i++) {
		sleep(2);
		echo "PID:", $current->getPid(), "\t",
			"OriginalPPID:", $current->getParent()->getPid(), "\t",
			"PPID:", posix_getppid(), "\t",
			time(), "\n";
	}
};

Comos\Qpm\Process\Process::fork($func);
sleep(1);
echo posix_getpid()."\tbye\n";
exit();