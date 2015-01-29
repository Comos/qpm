<?php
namespace qpm\log;
class Logger {
	private static $_impl;
	public static function err($msg) {
		if (!self::$_impl) return;
		if ($msg instanceof \Exception) {
			$str = $msg->getMessage();
			$str.="\n".strval($msg);
			$msg = $str;
		}
		self::$_impl->err($msg);
	}
	public static function info($msg) {
		if (!self::$_impl) return;
		self::$_impl->info($msg);
	}
	public static function debug($msg) {
		if (!self::$_impl) return;
		self::$_impl->debug($msg);
	}
	
	public static function setLoggerImpl($impl) {
		self::$_impl = $impl;
	}
	
	public static function useNullLogger() {
		self::setLoggerImpl(null);
	}

	public static function useSimpleLogger($pathToLogFile) {
		require_once(__DIR__.'/SimpleLoggerImpl.php');
		$impl = new SimpleLoggerImpl($pathToLogFile);
		self::setLoggerImpl($impl);
	}
}
