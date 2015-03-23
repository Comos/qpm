<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Log;
/**
 * don't use the class in production envrionment
 */
class SimpleLogger extends \Psr\Log\AbstractLogger {
	private $_file;
	public function __construct($pathToLogFile) {
		$this->_file = $pathToLogFile;
	}
	
	public function log($level, $message, array $context = array()) {
		$row = $message;
		foreach ($context as $key=>$val) {
			$row = str_replace('{'.$key.'}', strval($val), $row);
		}
		$time = date('Ymd-H:i:s');
		$row = "TIME[$time]\tLEVEL[$level]\t$row\n";
		\file_put_contents($this->_file, $row, \FILE_APPEND);
	}
}
