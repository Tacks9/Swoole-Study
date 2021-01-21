<?php

/*
 * @Descripttion: Async Task 异步任务服务器
 * @Author: tacks321@qq.com
 * @Date: 2021-01-21 14:24:15
 * @LastEditTime: 2021-01-21 15:43:56
 */

/*
    为什么要有异步任务？

    如果在一个程序中，比如想要处理发送邮件，当然可以顺序执行，但是这样大多数会阻塞当前进程，直到邮件发送成功后，才会顺序执行下面的程序

    这样可能会导致服务器的响应变慢

    那么 Swoole 提供了 异步任务处理的功能，可以投递一个异步任务到 TaskWorker进程池中执行，而不会影响当前请求速度。
*/


$server = new Swoole\Server('127.0.0.1', 9501);

// 设置异步任务的工程的 进程数量
$server->set([
    'task_worker_num' => 4
]);


// 【注册事件回调函数】 connect、 receive 、task 、finish 、close

// 监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    // 当有新的TCP客户端连接到 该端口后，服务器端便会打印出来 Client:Connect
    echo "######################################## Client: Connect ID[{$fd}]" , PHP_EOL;
});

// [receive] (回调函数在worker中进行)
$server->on('receive', function ($server, $fd, $from_id, $data) {
    // 派遣分配 异步任务
    $task_id = $server->task($data);

    echo "Dispatch AsyncTask: id=$task_id" . PHP_EOL;
   
});

// [task] 异步处理 回调函数在task进程中处理
$server->on('task', function ($server, $task_id, $from_id, $data) {
    // onTask 回调函数 Task 进程池内被异步执行

    echo "New AsyncTask[id=$task_id]" . PHP_EOL;

    // 返回任务执行的结果
    $server->finish("$data -> ok");
});


// [finish] 处理异步事件的结果 (回调函数在worker中进行)
$server->on('finish', function ($server, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data" . PHP_EOL;

});

// 监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    // 当客户端主动断开连接时， 服务端便会打印出来 Client:Close
    echo "######################################## Client: Close ID[{$fd}]" , PHP_EOL;
});


// 启动服务器
$server->start();


/*
首先我们需要在一台PHP环境中执行这个程序，然后就会进入阻塞状态，相应的终端也就成为了服务端
[root@Centos7]#  php 06_async_task_server.php

然后我们可以通过另一个终端观察到9501这个端口正在被监听
[root@Centos7]# netstat -ntlp | grep 9501
tcp        0      0 127.0.0.1:9501          0.0.0.0:*               LISTEN      3233/php73  
 

我们可以采用telnet来连接服务端
[root@Centos7]# telnet 127.0.0.1 9501
然后输入 ctrl+] 回车
=============================================> 这个时候我们的服务端就会监听到Connect事件，然后执行回调函数 Connect
Client: Connect ID[1]


接着我们输入数据如 hello 回车 
=============================================> 这个时候我们的服务端就会监听到Receive事件，然后执行回调函数，去派遣一个异步程序 并返回对应的task_id
Dispatch AsyncTask: id=0
New AsyncTask[id=0]



然后异步处理
=============================================> task 监听到事件，开始异步处理内容。 Task 回调函数 Task 进程池内被异步执行 


        =============================================> 同时程序继续向下执行 当 异步结束 执行 finish 回调返回结果
        AsyncTask[0] Finish: hello
         -> ok


===========================================> 当然这个时候程序并不会阻塞你再次发送内容，你可以继续发送数据



如果我们客户端主动退出连接 先进入命令行模式 ctrl+] 然后输入 quit
=============================================> 这个时候我们的服务端就会监听到Close事件，然后执行回调函数 Close
Client: Close ID[1]



1、可以基于TCP服务器的写法，加上 task 和 finish 两个监听事件来进行处理 异步
2、set([]) 可以设置服务器的相关配置， 如task_worker_num task进程数量
3、task($data) 可以派遣一个异步进程来进行处理，同时该方法会立即返回一个 task_id ，不会阻塞向下进行
4、task事件中，该进程是在task中进行的，用来处理异步任务
5、当异步处理完数据， $serv->finish() 返回结果
6、finish 来处理 异步事件结束的时候 


*/