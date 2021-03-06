<?php 
/**
 * 数据帧协议
 *
 * @author potterhe <potter.he@gmail.com>
 */
namespace Workermand\Thrift;

use \Workerman\Connection\ConnectionInterface;

/**
 * RPC 协议解析 相关
 */
class FrameProtocol implements \Workerman\Protocols\ProtocolInterface
{
    /**
     * 检查包的完整性
     * 如果能够得到包长，则返回包的在buffer中的长度，否则返回0继续等待数据
     *
     * @param string $buffer
     * @param ConnectionInterface $connection 链接.
     *
     * @return integer
     */
    public static function input($recv_buffer, ConnectionInterface $connection)
    {
        /* TFramedTransport 使用固定的前4个字节表示frame的长度 */
        if (strlen($recv_buffer) < 4) return 0;

        $val = unpack('N', $recv_buffer);
        return $val[1] + 4;
    }

    /**
     * 解包，当接收到的数据字节数等于input返回的值（大于0的值）自动调用
     * 并传递给onMessage回调函数的$data参数
     *
     * @param string $buffer
     *
     * @return string
     */
    public static function decode($recv_buffer, ConnectionInterface $connection)
    {
        /* thrift 传输层不知道数据协议,由thrift层负责解包,直接返回 */
        return $recv_buffer;
    }

    /**
     * 打包，当向客户端发送数据的时候会自动调用
     *
     * @param string $buffer
     *
     * @return string
     */
    public static function encode($data, ConnectionInterface $connection)
    {
        /* 数据已经由thrift上层打包 */
        return $data;
    }

}
