<?php
/**
 * @author bigbigant
 */

require __DIR__.'/bootstrap.inc.php';

use Comos\Qpm\Process\Process as Process;

//实际的工作内容
function work() {
  while(true) {
    file_put_contents(__FILE__.'.log', date('Y-m-d H:i:s')."\n", FILE_APPEND);
    sleep(10);
   };
}

//通过回调启动子进程
Process::current()->forkByCallable(
  function() {
    //子进程将自己转入后台
    Process::current()->toBackground();
    work();
  }
);
