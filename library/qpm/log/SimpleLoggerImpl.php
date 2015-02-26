<?php
namespace qpm\log;
/**
 * don't use class in production envrionment
 */
class SimpleLoggerImpl {
	private $_file;
	public function __construct($pathToLogFile) {
		$this->_file = $pathToLogFile;
	}
	public function __call($name, $args) {
		$method = $name;
		$msg = isset($args[0])?$args[0]:'';
		$this->_doLog(date('Ymd H:i:s')."\t".\posix_getpid()."\t$name\t$msg\n");
	}
	private function _doLog($msg) {
		\file_put_contents($this->_file, $msg, \FILE_APPEND);
	}
}
