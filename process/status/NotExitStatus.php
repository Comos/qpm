<?php
namespace qpm\process\status;
class NotExitStatus extends ForkedChildStatus {
	/**
	 * @see \qpm\process\status\ForkedChildStatus::isNormalExit()
	 */
	public function isNormalExit() {
		return false;
	}
	/**
	 * @see \qpm\process\status\ForkedChildStatus::getExitCode()
	 */
	public function getExitCode() {
		return null;
	}
	/**
	 * @see \qpm\process\status\ForkedChildStatus::isSignaled()
	 */
	public function isSignaled() {
		return false;
	}
}
