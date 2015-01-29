<?php
require_once dirname(__FILE__).'/../process/Process.php';
$func = function() {
	qpm\process\Process::current()->toBackground();
	for($i = 0; $i<=20; $i++) {
		sleep(5);
		echo posix_getpid(),"\t";echo microtime(),"\n";
	}
};

qpm\process\Process::current()->forkByCallable($func);
sleep(1);
echo posix_getpid()."\tbye\n";
exit();
