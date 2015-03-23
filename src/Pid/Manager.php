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
        if (is_file($this->_file) && ! is_dir($this->_file)) {
            $this->_checkAndGetPid();
        }
        $this->_updatePIDFile();
    }

    /**
     *
     * @return Process
     * @throws \Comos\Qpm\pidfile\Exception
     */
    public function getProcess()
    {
        $pidFromFile = $this->_getPidFromFile();
        if ($this->_processExists($pidFromFile)) {
            return Process::process($pidFromFile);
        }
        throw new \Comos\Qpm\pidfile\Exception('process does not exist');
    }

    private function _getPidFromFile()
    {
        $pidInFile = @file_get_contents($this->_file);
        if ($pidInFile === false) {
            throw new \Comos\Qpm\pidfile\Exception('fail to read file');
        }
        if (!\is_numeric($pidInFile)) {
            return null;
        }
        return $pidInFile;
    }

    private function _checkAndGetPid()
    {
        $pidInFile = $this->_getPidFromFile();
        if ($this->_processExists($pidInFile)) {
            throw new \Comos\Qpm\Pid\Exception('process exists, no need to start a new one');
        }
        return $pidInFile;
    }

    private function _processExists($pid)
    {
        if (\is_null($pid)) {
            return false;
        }
        return false !== @\pcntl_getpriority($pid);
    }

    private function _updatePIDFile()
    {
        $pid =\posix_getpid();
        $r = @\file_put_contents($this->_file, $pid);
        if ($r === false) {
            throw new \Comos\Qpm\Pid\Exception('fail to write pid file:' . $this->_file);
        }
    }
}
