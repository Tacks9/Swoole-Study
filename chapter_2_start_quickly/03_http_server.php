<?php

/*
 * @Descripttion: HTTP服务器
 * @Author: tacks321@qq.com
 * @Date: 2021-01-20 19:38:36
 * @LastEditTime: 2021-01-21 10:25:50
 */

// 0.0.0.0 表示监听所有 IP 地址，一台服务器可能同时有多个 IP，如 127.0.0.1 本地回环 IP、192.168.1.100 局域网 IP、210.127.20.2 外网 IP
// 当然也可以这里也可以单独指定监听一个 IP 
// HTTP服务器需要使用这个类 Swoole\Http\Server
$http = new Swoole\Http\Server('0.0.0.0', 9501);

// 【注册事件回调函数】  
// request事件  HTTP 服务器只需要关注请求响应
$http->on('request', function ($request, $response) {
    // 当有新的 HTTP 请求进入就会触发此事件

    var_dump($request->server); // 包含了请求的相关信息，如 GET/POST 请求的数据
    
    $response->header("Content-Type", "text/html; charset=utf-8");

    // $response->end() 方法表示输出一段 HTML 内容，并结束此请求
    $response->end("<h1>Hello Swoole </h1>".rand(1000, 9999));
});

// 开启HTTP服务器
$http->start();




/*

首先我们需要在一台PHP环境中执行这个程序，然后就会进入阻塞状态，相应的终端也就成为了服务端
[root@Centos7]#  php 03_http_server.php

然后我们可以通过另一个终端观察到9501这个端口正在被监听
[root@Centos7]# netstat -ntlp | grep 9501
tcp        0      0 0.0.0.0:9501            0.0.0.0:*               LISTEN      3457/php73     

我们可以打开谷歌浏览器来模拟get请求 

在地址栏输入 http://127.0.0.1:9501/ 回车请求
=============================================> 这个时候我们的服务端就会监听到 request 事件，然后响应执行回调函数，服务端打印 server请求信息，然后向客户端浏览器发出一段HTML然后结束
Hello Swoole
9769


$request->server 请求的相关信息
array(10) {
  ["request_method"]=>
  string(3) "GET"
  ["request_uri"]=>
  string(1) "/"
  ["path_info"]=>
  string(1) "/"
  ["request_time"]=>
  int(1611194723)
  ["request_time_float"]=>
  float(1611194723.5485)
  ["server_protocol"]=>
  string(8) "HTTP/1.1"
  ["server_port"]=>
  int(9501)
  ["remote_port"]=>
  int(61922)
  ["remote_addr"]=>
  string(8) "10.0.2.2"
  ["master_time"]=>
  int(1611194723)
}

当然，你也可以在服务器上进行压测这个地址 
例如 模拟并发请求100次，总共请求1000次
[root@Centos7 ]# ab -c 100 -n 1000 http://127.0.0.1:9501/


1、new 一个Swoole\HTTP\Server 的实例，监听9051端口，同样开启服务器需要启动start()函数。
2、HTTP服务器只需要关注请求\响应即可，设置 request 监听事件 。
3、HTTP服务器监听，当有新的HTTP请求进入，就会触发事件回调函数，主要是两个参数 $request \ $response
4、$request 主要包含了请求端的相关信息，可以通过打印 $request->server 查看请求相关内容
5、$response主要是响应对象，可以设置请求头，header()，以及输出内容 end() 并结束。
6、Linux中可以采用 ab 对 服务器进行压测
*/