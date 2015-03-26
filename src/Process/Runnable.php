<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Process;

interface Runnable
{

    /**
     * Returns exiting code.
     * Zero means ok.
     * 
     * @return int
     */
    public function run();
}
