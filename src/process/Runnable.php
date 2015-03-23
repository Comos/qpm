<?php
/**
 * @author bigbigant
 */

namespace qpm\process;
interface Runnable {
	/**
	 * Returns exiting code. Zero means ok.
	 * @return int
	 */
	public function run();
}
