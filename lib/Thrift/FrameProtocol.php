<?php 

namespace Workermand\Thrift;

use \Workerman\Connection\ConnectionInterface;

/**
 * RPC 协议解析 相关
 * @author walkor <worker-man@qq.com>
 * */
class FrameProtocol implements \Workerman\Protocols\ProtocolInterface
{
    /**
     * 检查包的完整性
     * 如果能够得到包长，则返回包的在buffer中的长度，否则返回0继续等待数据
     *
     * @param string $buffer
     */
    public static function input($recv_buffer, ConnectionInterface $connection)
    {
        /* TFramedTransport 使用固定的前4个字节表示frame的长度 */
        if (strlen($recv_buffer) < 4) return 0;

        $val = unpack('N', $recv_buffer);
        return $val[1] + 4;
    }

    /**
     * 打包，当向客户端发送数据的时候会自动调用
     * @param string $buffer
     * @return string
     */
    public static function encode($data, ConnectionInterface $connection)
    {
        return $data;
    }

    /**
     * 解包，当接收到的数据字节数等于input返回的值（大于0的值）自动调用
     * 并传递给onMessage回调函数的$data参数
     * @param string $buffer
     * @return string
     */
    public static function decode($recv_buffer, ConnectionInterface $connection)
    {
        return $recv_buffer;
    }

}
