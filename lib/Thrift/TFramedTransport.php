<?php
/**
 * 基于数据帧的transport实现.
 *
 * @author potterhe <potter.he@gmail.com>
 */
namespace Workermand\Thrift;

use Thrift\Transport\TTransport;
use Thrift\Exception\TException;
use Thrift\Factory\TStringFuncFactory;
use Workerman\Connection\ConnectionInterface;

/**
 * Php stream transport. Reads to and writes from the php standard streams
 * php://input and php://output
 *
 * @package thrift.transport
 */
class TFramedTransport extends TTransport
{

    /**
     * \Workerman\Connection\ConnectionInterface
     */
    protected $conn;

    protected $rBuf;
    protected $rp;

    public function __construct($input, ConnectionInterface $conn)
    {
        $this->conn = $conn;
        $this->rBuf = $input;
        $this->rp = 0;
    }

    public function open() {
        if ($this->isOpen()) {
            throw new TTransportException('Socket already connected', TTransportException::ALREADY_OPEN);
        }
    }

    public function close() {
        $this->conn->close();
    }

    public function isOpen() {
        return is_resource($this->conn->getSokcet());
    }

    public function read($len)
    {
        if ((strlen($this->rBuf) - $this->rp) < $len) {
            throw new TTransportException('TFramedTransport: Could not read '.$len.' bytes');
        }

        $data = substr($this->rBuf, $this->rp, $len);
        $this->rp += $len;
        return $data;
    }

    public function write($buf)
    {
        $this->conn->send($buf);
    }

    public function flush()
    {
        /**/
    }

}
