<?php
set_include_path(__DIR__.'/../../'.PATH_SEPARATOR.get_include_path());
require_once 'qpm/supervisor/Supervisor.php';

$run1 = function() {
	echo "run1,pid:".posix_getpid()."\n";
	while(true) {
		echo "---run1,pid:".posix_getpid()."\n";
		sleep(3);
		exit();
	}
};
$run2 = function() {
	while(true) {
		echo "+++run2,pid:".posix_getpid()."\n";
		sleep(3);
		exit();
	}
};

$configs = [
	['runnableCallback'=>$run1],
	['runnableCallback'=>$run2, 'quantity'=>2]
];

qpm\supervisor\Supervisor::multiGroupOneForOne($configs)->start();
