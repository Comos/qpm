<?php
require dirname(__DIR__) . '/bootstrap.php';

echo serialize(fopen(__FILE__.'.log', 'a'));
echo serialize(fopen(__FILE__.'.log1', 'a'));

exit;
?>
use Comos\Qpm\Process\Process;

$work = function() with($sfd)
{
    
};

Process::fork(function ()
{
    work();
});
