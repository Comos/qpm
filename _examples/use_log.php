<?php
set_include_path(__DIR__.'/../../'.PATH_SEPARATOR.get_include_path());
require_once 'qpm/supervisor/Supervisor.php';
require_once 'qpm/log/Logger.php';

qpm\log\Logger::useSimpleLogger(__FILE__.'-simple-logger.log');

$func = function() {
	$i = 3;
	while(--$i) {
		sleep(1);
	}
};
try {
	qpm\supervisor\Supervisor::oneForOne(
		[
			'runnableCallback' => $func,
			'quantity' => 3,
 			'maxRestartTimes' => 3,
		]
	)->start();
} catch (Exception $ex) {
	qpm\log\Logger::err($ex);
}
