<?php

/*
 * @Descripttion: TCP服务器
 * @Author: tacks321@qq.com
 * @Date: 2021-01-22 11:12:23
 * @LastEditTime: 2021-01-22 17:46:11
 */


/*
  EchoServer TCP服务器
    
采用类进行封装整个过程

我们将封装一个 EchoServer作为TCP服务器，以及EchoClient作为TCP客户端

你仅需要分别在两个终端内执行PHP文件，首先启动服务端，然后执行客户端进行连接，即可进行通信。一个简单的echo 服务器实现啦




回调函数的写法支持 【类静态方法】 例如

class A
{
    // 必须采用类的静态方法
    static function test($req, $resp)
    {
        echo "hello world";
    }
}
$server->on('Request', 'A::Test');
$server->on('Request', array('A', 'Test'));



 */
class EchoServer 
{
    public $server; // TCP 服务器
    
    public function __construct()
    {
        // 实例化
        $this->server = new Swoole\Server('127.0.0.1', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        // set配置信息
        $this->server->set([
            'worker_num' => 4,     // 设置启动的 Worker 进程数
            'daemonize'  => false  // 关闭守护进程化，执行当前程序，终端会进入阻塞，如果想要终止可以 ctrl+c
        ]);

        // 【注册事件回调函数】 分别处理这四个事件 Start、Connect、Receive、Close

        // 采用 [类静态方法] 设置事件监听回调函数
        $this->server->on('Start',   'EchoServer::onStart');
        $this->server->on('Connect', 'EchoServer::onConnect');
        $this->server->on('Receive', 'EchoServer::onReceive');
        $this->server->on('Close',   'EchoServer::onClose');


        // 启动服务器关键步骤 切勿忽略 ！
        $this->server->start();

    }

    // 开启
    public static function onStart($server) {
        echo "####[Server OnStart]############", PHP_EOL;
    }

    // 连接
    public static function onConnect($server, $fd) {
        echo "###[On Connect][fd:{$fd}]#######", PHP_EOL;// 服务器监听到客户端连接上来
        $server->send($fd, "Server say: Hello Client [fd:{$fd}]!");  // 向客户端打招呼
    }

    // 接收
    public static function onReceive($server, $fd, $from_id, $data) {
        echo "Message From Client[{$fd}]:[{$data}]", PHP_EOL;
        $server->send($fd, 'Server say: Server Received!');
    }

    // 关闭
    public static function onClose($server, $fd, $from_id) {
        echo "Client {$fd} close connection", PHP_EOL;
    }
    
}

// 实例化TCP服务器
$server = new EchoServer();