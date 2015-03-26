<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Process\Status;

class NotExitStatus extends ForkedChildStatus
{

    /**
     *
     * @see \Comos\Qpm\Process\Status\ForkedChildStatus::getTerminationSignal()
     */
    public function getTerminationSignal()
    {
        return null;
    }
    /**
     * @see \Comos\Qpm\Process\Status\ForkedChildStatus::getStopSignal()
     */
    public function getStopSignal()
    {
        return null;
    }

    /**
     *
     * @see \Comos\Qpm\Process\Status\ForkedChildStatus::isNormalExit()
     */
    public function isNormalExit()
    {
        return false;
    }

    /**
     *
     * @see \Comos\Qpm\Process\Status\ForkedChildStatus::getExitCode()
     */
    public function getExitCode()
    {
        return null;
    }

    /**
     *
     * @see \Comos\Qpm\Process\Status\ForkedChildStatus::isSignaled()
     */
    public function isSignaled()
    {
        return false;
    }
}
