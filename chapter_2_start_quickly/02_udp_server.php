<?php
/*
 * @Descripttion: UDP 服务器
 * @Author: tacks321@qq.com
 * @Date: 2021-01-20 17:35:10
 * @LastEditTime: 2021-01-20 18:09:05
 */


$server = new Swoole\Server('127.0.0.1', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);



// 【注册事件回调函数】 监听数据接收事件
//  UDP 没有连接的概念。 客户端无需 Connect

// 监听数据发送 Packet 事件
$server->on('Packet', function($server, $data, $clientInfo) {
    // 打印客户端信息
    var_dump($clientInfo);

    // 直接可以向 Server 监听的 ip 127.0.0.1 端口9502 发送数据包data
    $server->sendto($clientInfo['address'], $clientInfo['port'], 'Server: '. $data);
});

// 启动服务器
$server->start();

/*

首先我们需要在一台PHP环境中执行这个程序，然后就会进入阻塞状态，相应的终端也就成为了服务端
[root@Centos7]#  php 02_udp_server.php

然后我们可以通过另一个终端观察到9502这个端口正在被监听
[root@Centos7 ]# netstat -nualp | grep 9502
udp        0      0 127.0.0.1:9502          0.0.0.0:*                           3402/php73 

我们可以采用netcat(nc)来连接服务端
[root@Centos7]# nc 127.0.0.1 9502
然后输入 ctrl+] 回车

接着我们输入数据如 hello 回车 
=============================================> 这个时候我们的服务端就会监听到Packet事件，然后执行回调函数，服务端打印客户端信息，然后向客户端发出消息 Server: hello


例如客户端信息 clientInfo
array(5) {
  ["server_socket"]=>
  int(3)
  ["dispatch_time"]=>
  float(1611136888.4777)
  ["server_port"]=>
  int(9502)
  ["address"]=>
  string(9) "127.0.0.1"
  ["port"]=>
  int(36854)
}


===========================================================================================================================
1、通过New一个Swoole\Server对象,指定监听端口 127.0.0.1:9502 ， 设置socket模型为UDP（SWOOLE_SOCK_UDP）
2、注册事件回调函数 （CallBack）。
3、UDP是无状态的，不需要连接，面向非连接的请求，所以没有Connect连接事件。
4、start() 是这里的启动 UDP Server的入口， 这个时候就是Swoole完全接手PHP的运行。
5、sendto($address, $port, $data) 是服务端调用方法向客户端发送数据,需要指定地址，端口，数据。
6、$clientInfo 是客户端的相关信息，是一个数组，有客户端的 IP 和端口等内容。
7、Linux可以采用 netstat -nualp | grep 9502 查看UDP监听的端口
*/