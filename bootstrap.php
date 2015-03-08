<?php
/**
 * @Author bigbigant
 * This script is to initialize autoloading for QPM examples and Tests.
 * Composer autoload.php(/vendor/autoload.php) is the first choice.
 * If there is no composer file, a simple autoloader would be registered, but some features depend 3rd party packages may not be usable.
 */
$autoloadFile = __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
if (is_file($autoloadFile)) {
    require $autoloadFile;
} else {
    spl_autoload_register(
		function ($class) {
			$prefix = 'qpm\\';
		    $baseDir = __DIR__ . DIRECTORY_SEPARATOR. 'library'. DIRECTORY_SEPARATOR.'qpm';
		    $len = strlen($prefix);
		    if (strncmp($prefix, $class, $len) !== 0) {
		        return;
		    }
		    $relative_class = substr($class, $len);
		    $file = $baseDir .DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
		    if (file_exists($file)) {
		        require $file;
		    }
		}
    );
}
