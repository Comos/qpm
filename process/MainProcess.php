<?php
namespace qpm\process;
require_once __DIR__.'/Process.php';
require_once __DIR__.'/ChildProcess.php';
class MainProcess extends Process {
	/**
	 * fork and run qpm\process\Runnable::run() in child process
	 * @throws FailToForkException
	 * @param \qpm\process\Runnable $runnable
	 * @return \qpm\process\ChildProcess
	 */
	public function fork(Runnable $runnable) {
		return $this->forkByCallable(array($runnable, 'run'));
	}
	/**
	 * @param Callable $callable
	 * @return \qpm\process\Process
	 * @throws FailToForkException
	 */
	public function forkByCallable($callable) {
		if (!\is_callable($callable)) {
			throw new \InvalidArgumentException('argument must be a valid callback');
		}
		if (!$this->isCurrent()) {
			require_once __DIR__.'/FailToForkException.php';
			throw new FailToForkException('the instance does not represent current main process.');
		}
		
		$pid = \pcntl_fork();
		
		if ($pid == -1) {
			require_once __DIR__.'/FailToForkException.php';
			throw new FailToForkException('fail to folk.');
		}
		
		if ($pid == 0) {
			try {
				$code = $callable();
			} catch (\Exception $ex) {
				$code = -1;
			}
			if (!\is_int($code)) {
				$code = 1;
			} else if (\is_null($code)) {
				$code = 0;
			}
			exit($code);
		}
		
		return new ChildProcess($pid, $this->getPid());
	}
	/**
	 * @throws qpm\process\Exception
	 */
	public function toBackground() {
		if (0 > posix_setsid()) {
			require_once __DIR__.'/Exception.php';
			throw new Exception('fail to set sid of current process');
		}
	}
}
