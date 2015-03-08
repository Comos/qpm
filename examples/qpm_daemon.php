<?php
/*
 进程结构
master
 |-worker
 |-worker
 |_worker

 */
require __DIR__.'/bootstrap.inc.php';

use qpm\process\Process as Process;

//启动Daemon
Process::current()->forkByCallable(
	function() {
		Process::current()->toBackground();
  		master(5);
  	}
);

//启动子工作进程
function startWorker() {
	Process::current()->forkByCallable('worker');
}

//master进程
function master($maxChildren) {
	for($i = 0; $i < $maxChildren; $i++) {
		startWorker();
	}
	//维持子进程数量
	while(true) {
		$status = null;
		pcntl_wait($status);
		startWorker();
	}
}

//worker进程
function worker() {
	sleep(5);
	$msg = sprintf("PID: %d\tPPID:%d\n", Process::current()->getPid(), Process::current()->getParent()->getPid());
	file_put_contents(__FILE__.'.log', $msg, FILE_APPEND);
}

