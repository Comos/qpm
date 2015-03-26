<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Process\Status;

class ForkedChildStatus
{

    protected static $_statuses = array();

    /**
     *
     * @var ForkedChildStatus
     */
    protected static $_notExitStatus;

    /**
     *
     * @param int $statusCode
     * @return ForkedChildStatus
     */
    public static function create($statusCode, $exited = true)
    {
        if (! isset(self::$_statuses[$exited ? 0 : 1][$statusCode])) {
            $clazz = __NAMESPACE__ . '\\' . ($exited ? 'ForkedChildStatus' : 'NotExitStatus');
            self::$_statuses[$exited ? 0 : 1][$statusCode] = new $clazz($statusCode);
        }
        return self::$_statuses[$exited ? 0 : 1][$statusCode];
    }

    protected $_code;

    protected function __construct($code)
    {
        $this->_code = $code;
    }

    /**
     *
     * @return boolean
     */
    public function isNormalExit()
    {
        return \pcntl_wifexited($this->_code);
    }

    /**
     *
     * @return int
     */
    public function getExitCode()
    {
        return \pcntl_wexitstatus($this->_code);
    }

    /**
     *
     * @return boolean
     */
    public function isSignaled()
    {
        return \pcntl_wifsignaled($this->_code);
    }

    /**
     *
     * @return boolean
     */
    public function isStopped()
    {
        return \pcntl_wifstopped($this->_code);
    }

    /**
     *
     * @return int
     */
    public function getTerminationSignal()
    {
        return \pcntl_wtermsig($this->_code);
    }

    /**
     *
     * @return int
     */
    public function getStopSignal()
    {
        return \pcntl_wstopsig($this->_code);
    }

    /**
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }
}
