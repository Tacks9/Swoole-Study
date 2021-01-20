<?php

/*
 * @Descripttion: TCP 创建一个服务端
 * @Author: tacks321@qq.com
 * @Date: 2021-01-20 15:35:04
 * @LastEditTime: 2021-01-20 17:34:29
 */


// 创建Server对象 监听127.0.0.1:9501端口 
$server = new Swoole\Server('127.0.0.1', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

// 【注册事件回调函数】 分别处理这三个事件 Connect、Receive、Close

// 监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    // 当有新的TCP客户端连接到 该端口后，服务器端便会打印出来 Client:Connect
    echo 'Client: Connect', PHP_EOL;
});

// 监听数据接收事件
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    // 当有某个的TCP客户端发送数据时，服务器端便会打印出来 Server: 对应的数据内容
    $server->send($fd, "Server: ". $data);
});

// 监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    // 当客户端主动断开连接时， 服务端便会打印出来 Client:Close
    echo 'Client: Close', PHP_EOL;
});

// 启动服务器，将由Swoole来接管PHP
$server->start();

// var_dump($server);


/*
首先我们需要在一台PHP环境中执行这个程序，然后就会进入阻塞状态，相应的终端也就成为了服务端
[root@Centos7]#  php 01_tcp_server.php

然后我们可以通过另一个终端观察到9501这个端口正在被监听
[root@Centos7]# netstat -ntlp | grep 9501
tcp        0      0 127.0.0.1:9501          0.0.0.0:*               LISTEN      3233/php73  
[root@Centos7 vagrant]# netstat -an | grep 9501
tcp        0      0 127.0.0.1:9501          0.0.0.0:*               LISTEN  

我们可以采用telnet来连接服务端
[root@Centos7]# telnet 127.0.0.1 9501
然后输入 ctrl+] 回车
=============================================> 这个时候我们的服务端就会监听到Connect事件，然后执行回调函数，发出 Client: Connect

接着我们输入数据如 hello 回车 
=============================================> 这个时候我们的服务端就会监听到Receive事件，然后执行回调函数，向客户端发出消息 Server: hello

如果我们客户端主动退出连接 先进入命令行模式 ctrl+] 然后输入 quit
=============================================> 这个时候我们的服务端就会监听到Close事件，然后执行回调函数，发出 Client: Close



1、通过New一个Swoole\Server对象,指定监听端口 127.0.0.1:9501 ，默认是TCP Socket类型，指定运行模式SWOOLE_PROCESS，指定socket模型SWOOLE_SOCK_TCP
2、注册事件回调函数（CallBack）。
回调函数，就好比是张开夹子的捕鼠器，当我们设置捕鼠器的时候，并没有夹到老鼠，这个就是注册事件回调。
我们设置三个捕鼠器（事件回调函数）用来分别监听 connect、receive、close三种事件。
当真正发生这三种事件的时候，他就会按照对应的回调函数进行执行相应的处理。
3、start() 这里才是真正启动Server的入口，这个时候也就是Swoole完全接收PHP的运行。
4、Swoole Server通过监听相应的端口以及触发的事件来执行我们自定义的事件回调函数。
5、服务器可以同时被成千上万个客户端连接，$fd 就是客户端连接的唯一标识符。
6、send($fd,$data) 服务端可以通过调用此方法，向fd连接符对应的客户端发送data数据。
7、Linux可以采用 netstat -ntlp 或者 netstat -an 来查看端口

*/