<?php
/**
 *  EchoWorker
 */
namespace Workers;

use Workerman\Worker;

class EchoWorker extends Worker 
{

    /**
     * construct
     */
    public function __construct($socket_name)
    {
        parent::__construct($socket_name);
        $this->onConnect = array($this, 'onConnect');
        $this->onMessage = array($this, 'onMessage');
        $this->onClose = array($this, 'onClose');
    }

    /**
     * 处理收到的数据.
     *
     * @param TcpConnection $connection
     *
     * @return void
     */
    public function onConnect($connection)
    {
        echo 'conn:' .$connection->getRemoteIp() . ':' . $connection->getRemotePort() . PHP_EOL;
    }

    public function onClose($connection)
    {
        echo 'close conn:' .$connection->getRemoteIp() . ':' . $connection->getRemotePort() . PHP_EOL;
    }

    public function onMessage($connection, $data)
    {
        print_r($data);
    }

}
