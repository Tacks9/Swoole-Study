<?php


/*
 * @Descripttion: EchoClient TCP客户端封装
 * @Author: tacks321@qq.com
 * @Date: 2021-01-22 11:25:43
 * @LastEditTime: 2021-01-22 15:03:45
 */


class EchoClient
{
    private $client;

    public function __construct()
    {
        // 实例化Client TCP客户端SWOOLE_SOCK_TCP 并设置长连接SWOOLE_KEEP
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);

        $this->connect();
    }


    public function connect() 
    {
        // 连接到我们TCP服务器监听的端口上，设置超时时间1
        $res = $this->client->connect('127.0.0.1', 9501, 1);
        if($res === false) {
            echo "Error: {$this->client->errMsg}[{$this->client->errCode}]", PHP_EOL;

            sleep(10); // 模拟暂停一段时间
            // 你可以先启动客户端，然后出现报错的情况，在启动服务端，这样可以看到重连的这段代码的效果。
            // 失败重连一次
            $this->client->close(true);
            $this->client->connect('127.0.0.1', 9501, 1);
        }
        
        echo "#######[Client Connect]#######", PHP_EOL;

        
        
        // php向终端写入提示信息
        fwrite(STDOUT, "Please input Msg:");

        // 循环
        while(1) {
              // php读取命令行输入的内容 
            $msg = trim(fgets(STDIN));

            // 客户端发送信息到TCP服务端
            if(empty($msg)) {
                $msg = 'Empty';
            }
            $this->client->send($msg);

            // 客户端接收TCP服务端的内容
            $receive_msg = $this->client->recv();
            echo "{$receive_msg}", PHP_EOL;

            if($msg == 'quit') {
                $this->client->send('Client: I am quit!');
                break;
            }
        }
      

        // 关闭客户端
        $this->client->close(true);

        echo "#######[Client Close]#######", PHP_EOL;

    }
}

// 实例化TCP客户端
$client = new EchoClient();
