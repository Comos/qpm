<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Pid;

use Comos\Qpm\Process\Process;

class Manager
{

    private $_file;

    public function __construct($file)
    {
        if (! strlen($file)) {
            throw new \InvalidArgumentException('pid file cannot be empty');
        }
        $this->_file = $file;
    }

    public function start()
    {
        if (\is_file($this->_file) && ! \is_dir($this->_file)) {
            $this->_checkAndGetPid();
        }
        $this->_updatePIDFile();
    }

    /**
     *
     * @return Process
     * @throws Exception
     */
    public function getProcess()
    {
        $pidInfo = $this->_getPidInfoFromFile();
        
        if (!$pidInfo) {
            throw new Exception('process does not exist');
        }
        
        if ($this->_processExists($pidInfo[0], $pidInfo[1])) {
            return Process::process($pidInfo[0]);
        }
        throw new Exception('process does not exist');
    }

    private function _getPidInfoFromFile()
    {
        $pidInfoStr = @file_get_contents($this->_file);
        if ($pidInfoStr === false) {
            throw new Exception('fail to read pid file');
        }
        $pidInfo = @\json_decode($pidInfoStr);
        if (!\is_array($pidInfo)) {
            return null;
        }
        return $pidInfo;
    }

    private function _checkAndGetPid()
    {
        $pidInfo = $this->_getPidInfoFromFile();
        if ($this->_processExists($pidInfo[0], $pidInfo[1])) {
            throw new Exception('process exists, no need to start a new one');
        }
        return $pidInfo[0];
    }

    private function _processExists($pid, $pname)
    {
        if (\is_null($pid)) {
            return false;
        }
        $r = @\posix_kill($pid, 0);
        if ($r === false) {
            return false;
        }
        $cmd = 'ps x | grep ' . escapeshellarg($pid) . ' | grep ' . escapeshellarg($pname) . ' | grep -v "grep"';
        $r = shell_exec($cmd);
        if (empty($r)) {
            return false;
        }
        return true;
    }

    private function _updatePIDFile()
    {
        global $argv;
        $pid =\posix_getpid();
        $pname = $argv[0];
        $content = json_encode(array($pid, $pname));
        $r = @\file_put_contents($this->_file, $content);
        if ($r === false) {
            throw new Exception('fail to write pid file:' . $this->_file);
        }
    }
}
