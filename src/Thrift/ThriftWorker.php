<?php
/**
 * TFramedTransport支持.
 *
 */
namespace Workermand\Thrift;

use Workerman\Worker;
use Workerman\Connection\ConnectionInterface;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TFramedTransport;
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
    public function __construct($socket_name = '', $context_option = array())
    {
        parent::__construct($socket_name, $context_option);
        $this->_protocol = '\\Workermand\\Thrift\\FrameProtocol';

        $this->onMessage = array($this, 'onMessage');
        $this->onWorkerStart = array($this, 'onStart');
    }

    /**
     * 进程启动时，运行.
     *
     * @return void
     */
    public function onStart()
    {
        $loader = new ThriftClassLoader();

        /* gen-php 目录的一级子目录，即命名空间. */
        $fp = opendir($this->conf['gen-php']);
        while($ns = readdir($fp)) {
            if ($ns{0} === '.') {
                continue;
            }
            $loader->registerDefinition($ns, $this->conf['gen-php']);
        }
        closedir($fp);

        /* 注册handler, handler/NAMESPACE/SERVICE */
        $loader->registerNamespace('handler', dirname($this->conf['handler']));
        $loader->register();

        $this->processor = new TMultiplexedProcessor();
        $fp = opendir($this->conf['handler'] . DIRECTORY_SEPARATOR . $this->conf['namespace']);
        while($dir = readdir($fp)) {
            if ($dir{0} === '.') {
                continue;
            }
            // handler/NAMESPACE/SERVICE.php
            $service = substr($dir, 0, -4);
            $handlerClass = "\\handler\\{$this->conf['namespace']}\\" . $service;
            $processorClass = "\\{$this->conf['namespace']}\\{$service}Processor";

            $handler = new $handlerClass();
            $p = new $processorClass($handler);

            /**
             * 要求一个service name
             */
            $this->processor->registerProcessor($service, $p);

            w8d_write_stdout("REGISTER: {$this->conf['namespace']} {$service}");
        }
        closedir($fp);
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
        /**
         * TODO 支持 $this->conf['thrift_protocol']
         * 统计处理时间.
         * 处理超时.
         */
        $transport = new TFramedTransport(new WFramedTransport($data, $connection));
        $protocol = new TBinaryProtocol($transport, true, true);

        $this->processor->process($protocol, $protocol);
        //$transport->close();
    }

    public function setConf($conf)
    {
        if (!array_key_exists('gen-php', $conf)
            || !array_key_exists('handler', $conf)
        ) {
            throw new \Exception('gen-php handler is required');
        }

        $conf['gen-php'] = $this->checkPathConf($conf['gen-php']);
        $conf['handler'] = $this->checkPathConf($conf['handler']);

        $this->conf = $conf;
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
