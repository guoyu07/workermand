<?php
namespace Workers;

use Workerman\Worker;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TJSONProtocol;
use Thrift\Transport\TFramedTransport;

/**
 *  ThriftWorker
 */
class ThriftWorker extends Worker
{

    protected $conf;
    protected $processor;

    /**
     * construct
     */
    public function __construct($socket_name = '', $context_option = array(), $conf = null)
    {
        if (!array_key_exists('gen-php', $conf)
            || !array_key_exists('handler', $conf)
        ) {
            throw new \Exception('gen-php handler is required');
        }

        list($scheme, $address) = explode(':', $socket_name, 2);
        $socket_name = 'ThriftFrame:'.$address;

        parent::__construct($socket_name, $context_option);

        $conf['gen-php'] = $this->checkPathConf($conf['gen-php']);
        $conf['handler'] = $this->checkPathConf($conf['handler']);
        $this->conf = $conf;

        $this->onMessage = array($this, 'onMessage');
        $this->onWorkerStart = array($this, 'onStart');
    }

    public function onStart()
    {
        $loader = new ThriftClassLoader();

        $fp = opendir($this->conf['gen-php']);
        while($dir = readdir($fp)) {
            if ($dir{0} === '.') {
                continue;
            }
            $loader->registerDefinition($dir, $this->conf['gen-php']);
        }
        closedir($fp);

        $loader->registerNamespace('handler', $this->conf['handler']);
        $loader->register();

        $handlerClass = '\\handler\\' . $this->conf['service'];
        $processorClass = "\\{$this->conf['service-ns']}\\{$this->conf['service']}Processor";

        $handler = new $handlerClass();
        $this->processor = new $processorClass($handler);
    }

    public function onMessage($connection, $data)
    {
        $transport = new TFramedTransport(new \Transport\WManConnection($data, $connection));
        $protocol = new TJSONProtocol($transport, true, true);

        //$transport->open();
        $this->processor->process($protocol, $protocol);
        //$transport->close();
    }

    protected function checkPathConf($path)
    {
        if (substr($path, 0, 1) !== '/') {
            $path = WORKERMAND_ROOT . '/' . $path;
        }

        if (!file_exists($path)) {
            throw new \Exception($path . ' not exists');
        }
        return $path;
    }

}
