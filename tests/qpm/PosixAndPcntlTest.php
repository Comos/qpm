<?php
/**
 * to test the behavior of posix_get_pid() under HHVM
 * 
 * @author bigbigant
 */

namespace qpm;


class PosixAndPcntlTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPid() 
    {
        $pid = \posix_getpid();
        $this->assertTrue(is_numeric($pid), "pid[$pid] is numeric");
        $this->assertTrue(is_integer($pid), "pid[$pid] is integer");
    }
    
    public function testGetPriority()
    {
        $p = \pcntl_getpriority(\posix_getpid());
        $this->assertTrue(is_integer($p));
        
        $p = @\pcntl_getpriority('xxx');
        $err = \error_get_last();
        $this->assertFalse($p);        
    }
}