<?php
/**
 * @author bigbigant
 */
namespace Comos\Qpm\Supervision;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
/**
 *
 * @example > new Config([
 *          > 'factoryMethod' => function() {...;},//or 'runnableClass' => 'ClassName'
 *          > //factoryMethod or runnableClass or worker is required
 *          > 'quantity' => 3,//how many process to keep,default is 1
 *          > 'maxRestartTimes' => 3,//default is -1,-1 means ignore it
 *          > 'withInSeconds' => 10,//default is -1,means ignore it
 *          > 'timeout' => 10,//default is -1, means ignore it
 *          > ]);
 *         
 */
class Config
{

    const DEFAULT_QUANTITY = 1;

    const DEFAULT_MAX_RESTART_TIMES = - 1;

    const DEFAULT_WITH_IN_SECONDS = - 1;

    const DEFAULT_TIMEOUT = - 1.0;
    
    const DEFAULT_TERM_SIG = \SIGKILL;
    
    const DEFAULT_TERM_TIMEOUT = -1.0;

    protected $_factory;

    protected $_keeperRestartPolicy;

    protected $_timeout;

    protected $_onTimeout;
    
    protected $_termTimeout;

    public function __construct($config)
    {
        $this->_initFactory($config);
        $this->_initQuantity($config);
        $this->_initTimeout($config);
        $this->_initKeeperRestartPolicy($config);
        $this->_initOnTimeout($config);
        $this->_initTermTimeout($config);
    }

    public function getFactoryMethod()
    {
        return $this->_factory;
    }

    public function getKeeperRestartPolicy()
    {
        return clone ($this->_keeperRestartPolicy);
    }
    /**
     * @return float
     */
    public function getTermTimeout()
    {
        return $this->_termTimeout;
    }
    /**
     * @return boolean
     */
    public function isKillOnTimeout()
    {
        return $this->_termTimeout <= 0.0;
    }
    
    public function getQuantity()
    {
        return $this->_quantity;
    }

    public function getOnTimeout()
    {
        return $this->_onTimeout;
    }

    /**
     *
     * @return integer
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     *
     * @return boolean
     */
    public function isTimeoutEnabled()
    {
        return $this->getTimeout() > 0.0;
    }
    
    private function _initTermTimeout($config) {
        $q = self::_fetchFloatValue($config, "termTimeout", self::DEFAULT_TERM_TIMEOUT);
        $o = $q;
        if ($q <= 0.0) {
            $q = -1.0;
        }
        $this->_termTimeout = $q;
    }
    
    private function _initKeeperRestartPolicy($config)
    {
        $max = self::_fetchIntValue($config, 'maxRestartTimes', self::DEFAULT_MAX_RESTART_TIMES);
        if ($max == 0) {
            throw new \InvalidArgumentException('maxRestartTimes must be integer and cannot be zero');
        }
        
        $withIn = self::_fetchIntValue($config, 'withInSeconds', self::DEFAULT_WITH_IN_SECONDS);
        if ($withIn == 0) {
            throw new \InvalidArgumentException('withInSeconds must be integer and cannot be zero');
        }
        
        $this->_keeperRestartPolicy = KeeperRestartPolicy::create($max, $withIn);
    }

    private static function _fetchIntValue($config, $field, $defaultValue)
    {
        if (! isset($config[$field])) {
            return $defaultValue;
        }
        $v = $config[$field];
        
        if (is_float($v)) {
            return intval($v);
        } else {
            return $v;
        }
        
        if (is_string($v)) {
            if (is_numeric($v)) {
                return intval($v);
            }
        }
        throw new \InvalidArgumentException("$field must be int");
    }

    private static function _fetchFloatValue($config, $field, $defaultValue)
    {
        if (! isset($config[$field])) {
            return $defaultValue;
        }
        $v = $config[$field];
        if (is_int($v)) {
            return floatval($v);
        } else {
            return $v;
        }
        
        if (is_string($v) && is_numeric($v)) {
            return floatval($v);
        }
        throw new \InvalidArgumentException("$field must be float");
    }

    private function _initQuantity($config)
    {
        $q = self::_fetchIntValue($config, "quantity", self::DEFAULT_QUANTITY);
        if (! is_int($q) || $q < 1) {
            throw new \InvalidArgumentException('quantity must be positive integer');
        }
        $this->_quantity = $q;
    }

    private function _initTimeout($config)
    {
        $q = self::_fetchFloatValue($config, "timeout", self::DEFAULT_TIMEOUT);
        $this->_timeout = $q;
    }

    private function _initOnTimeout($config)
    {
        $q = isset($config['onTimeout']) ? $config['onTimeout'] : null;
        if (\is_null($q)) {
            return $q;
        }
        if (! \is_callable($q)) {
            throw new \InvalidArgumentException('onTimeout must be callable');
        }
        $this->_onTimeout = $q;
    }

    private function _initFactory($config)
    {
        if (isset($config['factory'])) {
            if (! \is_callable($config['factory'])) {
                throw new \InvalidArgumentException('factory must be callable');
            }
            $this->_factory = $config['factory'];
            return;
        }
        
        if (isset($config['worker'])) {
            $worker = $config['worker'];
            if (is_callable($worker)) {
                $this->_factory = function () use($worker)
                {
                    return $worker;
                };
                
                return;
            }
            if (is_subclass_of($worker, '\Comos\Qpm\Process\Runnable')) {
                $this->_factory = function () use($worker)
                {
                    $workerInst = new $worker();
                    return array(
                        $workerInst,
                        'run'
                    );
                };
                return ;
            }
            throw new \InvalidArgumentException('worker must be instance of Comos\Qpm\Process\Runnable or callable');
        }
        throw new \InvalidArgumentException('factory or worker is required.');
    }
}
