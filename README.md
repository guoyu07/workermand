# workermand
基于[workerman](http://www.workerman.net)的php服务器容器,当前实现以下特性。
- 支持thrift。与[workerman-thrift](https://github.com/walkor/workerman-thrift)实现不同。
- Transport支持TFramedTransport。TFramedTransport要求TNonblockingServer的实现，workerman本身符合这一要求。
- 一个监听端口支持一个命名空间下的多个service。thrift从0.9.1版本开始支持一个端口多个service。服务器端：TMultiplexedProcessor;客户端TMultiplexedProtocol。

## getting started
### 初始化环境和用例
```sh
$ cd /PATH/TO/workermand
$ composer install

$ cd /PATH/TO/workermand/doc/thrift-service
$ thrift -r --gen php:server tutorial.thrift
```

### 配置文件
/PATH/TO/workermand/etc/workermand.json

### 运行
#### 启动服务器
```sh
$ cd /PATH/TO/workermand
$ ./bin/workermand
```

#### 客户端
```sh
$ cd /PATH/TO/workermand
$ ./doc/thrift-service/client.php
```
