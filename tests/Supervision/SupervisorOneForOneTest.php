<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Supervision;

use Comos\Qpm\Log\Logger;
class SupervisorOneForOneTest extends \PHPUnit_Framework_TestCase
{

    private $logFile;
    
    protected function setUp()
    {
        Logger::useSimpleLogger(__FILE__.'.log');
        parent::setUp();
        $this->lastId = 0;
        $this->logFile = __FILE__ . '.data';
        @\unlink($this->_logFile);
    }

    protected function tearDown()
    {
        @\unlink($this->logFile);
        parent::tearDown();
    }

    public function testOneForOne_WithTimeout()
    {
        $conf = array(
            'runnableCallback' => array($this, 'runnableCallback'),
            'quantity' => 2,
            'timeout' => 1,
            'maxRestartTimes' => 10,
            'withIn' => 30
        );
        try {
            Supervisor::oneForOne($conf)->start();
            $this->fail('expects OutOfPolicyException');
        } catch (\Exception $ex) {
            $this->assertInstanceOf('\\Comos\\Qpm\\Supervision\\OutOfPolicyException', $ex);
        }
        $data = \file_get_contents($this->logFile);
        $ms = null;
        $this->assertEquals(1, preg_match('/^b{10,12}$/', $data, $ms));
    }

    public function runnableCallback()
    {
        \file_put_contents($this->logFile, 'b', \FILE_APPEND);
        \usleep(3*1000*1000);
        \file_put_contents($this->logFile, 'a', \FILE_APPEND);
    }
}
