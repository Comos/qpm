<?php
/**
 * @author bigbigant
 */

require __DIR__.'/bootstrap.inc.php';

$func = function() {
	echo posix_getpid(),"\t";echo microtime(),"\n";
	sleep(2);
	echo posix_getpid(),"\t";echo microtime(),"\n";
};
for($i=0; $i<10; $i++) {
	//public function forkByCallable($callable) 
	Qpm\Process\Process::fork($func);
}
sleep(20);
