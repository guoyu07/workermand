# workermand
基于[workerman](http://www.workerman.net)的php服务器容器,当前实现以下特性。
- 支持thrift。与[workerman-thrift](https://github.com/walkor/workerman-thrift)实现不同。
- Transport支持TFramedTransport。TFramedTransport要求TNonblockingServer的实现，workerman本身符合这一要求。
- 一个监听端口支持一个命名空间下的多个service。thrift从0.9.1版本开始支持一个端口多个service。
>- 服务器端:TMultiplexedProcessor
>- 客户端:TMultiplexedProtocol

## getting started
### thrift-demo
thrift-demo 是一个sub module.

### 初始化环境和用例
```sh
$ cd /PATH/TO/workermand
$ composer install

$ cd /PATH/TO/workermand/thrift-demo
$ thrift -r --gen php:server tutorial.thrift
```

### 配置文件
/PATH/TO/workermand/workermand.ini

### 运行
#### 启动服务器
```sh
$ cd /PATH/TO/workermand
$ ./bin/workermand -c workermand.ini
```

#### 客户端
```sh
$ cd /PATH/TO/workermand
$ ./test/client.php
```

#### 服务器关闭，重启等
- 查看命令行参数说明.
```sh
./bin/workermand -h
```
- 关闭.
```sh
./bin/workermand -s stop
```

## deb打包
```sh
make deb
```
## Docker打包

## 超时
当前没有server端的超时机制，需要依赖client端实施超时.
```php
$socket = new TSocket('localhost', 9090);
$socket->setSendTimeout(3000);
$socket->setRecvTimeout(3000);
```
