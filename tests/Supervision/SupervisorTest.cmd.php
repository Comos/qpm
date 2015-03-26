<?php
namespace Comos\Qpm\Supervision;

require \dirname(__DIR__) . '/bootstrap.inc.php';

use Comos\Qpm\Process\Process;

$file = $argv[1];
$file1 = $argv[2];
$configs = array(
    array(
        'worker' => function () use($file)
        {
            \file_put_contents($file, 1, \FILE_APPEND);
            \usleep(1000 * 1000);
        }
    ),
    array(
        'worker' => function () use($file1)
        {
            \file_put_contents($file1, '2', \FILE_APPEND);
            \usleep(1000 * 500);
        },
        'quantity' => 2
    )
);
$supProcessCallback = function () use($configs)
{
    $supervisor = Supervisor::multiGroupOneForOne($configs);
    $supervisor->start();
    $supervisor->registerSignalHandler();
};
$supProcess = Process::fork($supProcessCallback);
usleep(5000 * 1000);
$supProcess->terminate();
usleep(5000 * 1000);