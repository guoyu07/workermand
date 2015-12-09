<?php
/**
 * TFramedTransport支持.
 *
 */
namespace Workermand\Thrift;

use Workerman\Worker;
use Workerman\Connection\ConnectionInterface;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TJSONProtocol;
//use Thrift\Transport\TFramedTransport;
use Thrift\TMultiplexedProcessor;

/**
 *  ThriftWorker
 */
class ThriftWorker extends Worker
{

    protected $conf;
    protected $processor;

    /**
     * construct
     *
     * @param string $socket_name Socket(tcp://ip:port)
     * @param array $context_option SoketOpt.
     * @param mixed $conf 配置项.
     */
    public function __construct($socket_name = '', $context_option = array(), $conf = null)
    {
        if (!array_key_exists('gen-php', $conf)
            || !array_key_exists('handler', $conf)
        ) {
            throw new \Exception('gen-php handler is required');
        }

        parent::__construct($socket_name, $context_option);
        $this->_protocol = '\\Workermand\\Thrift\\FrameProtocol';

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

        $loader->registerNamespace('handler', dirname($this->conf['handler']));
        $loader->register();

        $processor = new TMultiplexedProcessor();
        $fp = opendir($this->conf['handler']);
        while($dir = readdir($fp)) {
            if ($dir{0} === '.') {
                continue;
            }
            // handler/Service.php
            $service = substr($dir, 0, -4);
            $handlerClass = '\\handler\\' . $service;
            $processorClass = "\\{$this->conf['service-ns']}\\{$service}Processor";

            $handler = new $handlerClass();
            $p = new $processorClass($handler);
            $processor->registerProcessor($service, $p);

            echo "$service\n";
        }
        closedir($fp);

        $this->processor = $processor;
    }

    /**
     * 完整数据包处理.
     *
     * @param ConnectionInterface $connection 连接.
     * @param mixed $data 数据包.
     *
     * @return void
     */
    public function onMessage(ConnectionInterface $connection, $data)
    {
        $transport = new \Thrift\Transport\TFramedTransport(new TFramedTransport($data, $connection));
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
