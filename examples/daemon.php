<?php
/**
 * @author bigbigant
 */

require __DIR__.'/bootstrap.inc.php';

use Comos\Qpm\Process\Process as Process;

echo "The program will run in background in 50 seconds.
You can see the prints in ".__FILE__.".log\n";
//实际的工作内容
function work() {
  $i = 10;
  while(--$i) {
    file_put_contents(__FILE__.'.log', date('Y-m-d H:i:s')."\n", FILE_APPEND);
    sleep(5);
   };
}

//通过回调启动子进程
Process::current()->fork(
  function() {
    //子进程将自己转入后台
    Process::current()->toBackground();
    work();
  }
);
