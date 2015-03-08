<?php
namespace qpm\process;
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
			throw new FailToForkException('the instance does not represent current main process.');
		}
		
		$pid = \pcntl_fork();
		
		if ($pid == -1) {
			throw new FailToForkException('fail to fork.');
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
			throw new Exception('fail to set sid of current process');
		}
	}
}
