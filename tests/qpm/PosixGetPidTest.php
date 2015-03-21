<?php
/**
 * to test the behavior of posix_get_pid() under HHVM
 * 
 * @author bigbigant
 */

namespace qpm;


class PosixGetPidTest extends \PHPUnit_Framework_TestCase
{
    public function testGet() 
    {
        $pid = \posix_getpid();
        $this->assertTrue(is_numeric($pid), "pid[$pid] is numeric");
        $this->assertTrue(is_integer($pid), "pid[$pid] is integer");
    }
}