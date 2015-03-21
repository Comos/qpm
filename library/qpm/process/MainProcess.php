<?php
namespace qpm\process;

class MainProcess extends Process
{
    /**
     *
     * @throws qpm\process\Exception
     */
    public function toBackground()
    {
        if (0 > posix_setsid()) {
            throw new Exception('fail to set sid of current process');
        }
    }
}
