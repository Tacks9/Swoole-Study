<?php

/*
 * @Descripttion: WebSocket服务器
 * @Author: tacks321@qq.com
 * @Date: 2021-01-21 11:27:44
 * @LastEditTime: 2021-01-21 14:14:39
 */

// 创建WebSocket Server 对象 监听0.0.0.0:9502对象
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9501);

// 【注册事件回调函数】 

// [open] 监听 WebSocket 连接打开事件
$ws->on('open', function ($ws, $request) {
    // 当客户端浏览器 连接到 服务器的时候，
    // 服务器打印当前的fd操作符 和 server请求信息
    var_dump($request->fd, $request->server);

    // 向客户端浏览器发出内容 Hello Welcome!
    $ws->push($request->fd, "Hello, Welcome! \n");
});


// [message] 监听 WebSocket 消息事件
$ws->on('message', function ($ws, $frame) {
    // 客户端向服务器端发送信息时，服务器端触发 message 事件回调
    echo "Message：{$frame->data}\n";
    // 服务器端可以调用 push() 向某个客户端（使用 $fd 标识符）发送消息
    $ws->push($frame->fd, "Server: {$frame->data}");
});


// [close] 监听 WebSocket 连接关闭事件
$ws->on('close', function ($ws, $fd) {
    // 当浏览器刷新或者关闭连接的时候，就会打印
    echo "client-{$fd} is closed\n";
});


// 启动服务器
$ws->start();



/*
该服务端代码 需要 Swoole-Study\chapter_2_start_quickly\05_websocket_server.html来作为客户端

服务端运行 php 05_websocket_server.php
本地运行   file:///D:/Swoole-Study/chapter_2_start_quickly/05_websocket_server.html

另外，如果PHP代码是在虚拟机中，那么在本地运行html的时候，他们监听的端口号要确保已经正常映射，否则可能客户端连接失败。


1、通过New一个Swoole\WebSocket\Server对象,指定监听端口0.0.0.0:9501 
2、start() 这里才是真正启动Server的入口，这个时候也就是Swoole完全接收PHP的运行。
3、$fd 就是客户端连接的唯一标识符。
4、WebSocket 相关监听事件 open 、message 、 close、 error
5、WebSocket 向某个客户端（使用 $fd 标识符）发送消息 push($fd, $data)
*/