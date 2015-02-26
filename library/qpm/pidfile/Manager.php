<?php
namespace qpm\pidfile;
require_once __DIR__.'/Exception.php';
require_once 'qpm/process/Process.php';
use qpm\process\Process;
class Manager {
 private $_file;
 public function __construct($file) {
	if (!strlen($file)) {
		throw new \InvalidArgumentException('pid file cannot be empty');
	}
	$this->_file = $file;
 }
 public function start() {
  if (is_file($this->_file) && !is_dir($this->_file)) {
   $this->_checkAndGetPid();	 
  }
  $this->_updatePIDFile();
 }
 /**
  * @return Process
  * @throws \qpm\pidfile\Exception
  */
 public function getProcess() {
	$pidFromFile = $this->_getPidFromFile();
	if ($this->_processExists($pidFromFile)) {
 		return Process::process($pidFromFile);
	}
	throw new \qpm\pidfile\Exception('process does not exist');
 }
 private function _getPidFromFile() {
	$pidInFile = @file_get_contents($this->_file);  
 	if ($pidInFile ===  false) {
 		throw new \qpm\pidfile\Exception('fail to read file');
	}
	return $pidInFile;
}
 private function _checkAndGetPid() {
	$pidInFile = $this->_getPidFromFile();
	if ($this->_processExists($pidInFile)) {
		throw new \qpm\pidfile\Exception('process exists, no need start a new one');
	}
	return $pidInFile;
 }
 
 private function _processExists($pid) {
  return false !== @\pcntl_getpriority($pid);
 }

 private function _updatePIDFile() {
  $pid = \posix_getpid();
  $r = @\file_put_contents($this->_file, $pid);
  if ($r ===  false) {
	throw new \qpm\pidfile\Exception('fail to write pid file:'.$this->_file);
  }
 }
}
