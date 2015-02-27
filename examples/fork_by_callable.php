<?php
require __DIR__.'/bootstrap.inc.php';

require_once 'qpm/process/Process.php';
$func = function() {
	echo posix_getpid(),"\t";echo microtime(),"\n";
	sleep(2);
	echo posix_getpid(),"\t";echo microtime(),"\n";
};
for($i=0; $i<10; $i++) {
	//public function forkByCallable($callable) 
	qpm\process\Process::current()->forkByCallable($func);
}
sleep(20);
