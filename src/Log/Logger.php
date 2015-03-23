<?php
/**
 * @author bigbigant
 */

namespace Comos\Qpm\Log;

class Logger {
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private static $_impl;
	public static function err($msg, $context = []) {
		if (!self::$_impl) return;
		if ($msg instanceof \Exception) {
			$context = ['exception'=>$msg];
			$msg = 'EXCEPTION[{exception}]';
		}
		self::$_impl->error($msg, $context);
	}
	public static function info($msg, $context = []) {
		if (!self::$_impl) return;
		self::$_impl->info($msg, $context);
	}
	public static function debug($msg, $context = []) {
		if (!self::$_impl) return;
		self::$_impl->debug($msg, $context = []);
	}
	
	/**
	 * 
	 * @param \Psr\Log\LoggerInterface $impl
	 */
	public static function setLoggerImpl($impl) {
		if (!$impl instanceof \Psr\Log\LoggerInterface) {
			throw new \InvalidArgumentException('Logger Impl must be instance of Psr\Log\Test\LoggerInterface');
		}
		self::$_impl = $impl;
	}
	
	public static function useNullLogger() {
		self::$_impl = null;
	}

	public static function useSimpleLogger($pathToLogFile) {
		$impl = new SimpleLogger($pathToLogFile);
		self::setLoggerImpl($impl);
	}
}
