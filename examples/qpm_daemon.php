<?php
/**
 * @author bigbigant
 * 
 *    进程结构
 *    master
 *    |-worker
 *    |-worker
 *    |_worker
 *
 */
require __DIR__ . '/bootstrap.inc.php';

use qpm\process\Process as Process;

// Start Daemon
Process::fork(function ()
{
    Process::current()->toBackground();
    master(5);
});

/**
 * start a worker(child) process.
 */
function startWorker()
{
    Process::fork('worker');
}

/**
 * master.
 * 
 * to start and mantaince child processes.
 * 
 * @param integer $maxChildren
 */
function master($maxChildren)
{
    for ($i = 0; $i < $maxChildren; $i ++) {
        startWorker();
    }
    // 维持子进程数量
    while (true) {
        $status = null;
        pcntl_wait($status);
        startWorker();
    }
}

/**
 * worker
 * 
 * executes in child process
 */
function worker()
{
    sleep(5);
    $msg = sprintf("PID: %d\tPPID:%d\n", Process::current()->getPid(), Process::current()->getParent()->getPid());
    file_put_contents(__FILE__ . '.log', $msg, FILE_APPEND);
}

