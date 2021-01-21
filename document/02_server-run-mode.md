<!--
 * @Descripttion: Server的两种运行模式
 * @Author: tacks321@qq.com
 * @Date: 2021-01-21 16:40:27
 * @LastEditTime: 2021-01-21 17:35:42
-->

# Server的两种运行模式
> 如果我们去实例化一个HTTP的服务器，可以这样编写`$server = new Swoole\Server('127.0.0.1', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);` ，那么其中 `SWOOLE_PROCESS` 到底代表什么含义呢？ 第三个参数可以填两个常量值，一个是 `SWOOLE_PROCESS` ，另外一个就是 `SWOOLE_BASE`


## SWOOLE_PROCESS

- `SWOOLE_PROCESS`模式中的`Server`所有的客户端的`TCP`连接，都是和主进程建立的。
- 用了大量的进程间通信、进程管理机制、内存保护机制。
- 适合业务逻辑非常复杂的场景。

### 进程模式的优点

- `Worker`进程均衡。
    - 连接与数据请求发送是分离的，不会因为某些连接数据量大某些连接数据量小导致 `Worker` 进程不均衡

- 连接进程更可靠。
    - Worker 进程发送致命错误时，连接并不会被切断
    
- 支持单连接并发。
    - 仅保持少量 TCP 连接，请求可以并发地在多个 `Worker` 进程中处理

### 进程模式的缺点

- 存在2次`IPC`(InterProcess Communication 进程间通信) 的开销
- master 进程与 worker 进程需要使用 unixSocket 进行通信

    > 【概念解释】 Linux环境下，进程地址空间相互独立，每个进程各自有不同的用户地址空间。任何一个进程的全局变量在另一个进程中都看不到，所以进程和进程间不能互相访问，要交换数据必须通过内核，在内核中开辟一块缓存区，进程1把数据从用户空间拷贝到内存缓冲区，进程2再从内存缓冲区中把数据读走，内核提供的这种机制称为进程间通信（`IPC`）。


## SWOOLE_BASE

当TCP的连接请求进来时，所有的`Worker`进程都会去抢占这一个连接, 并最终会有一个`Worker`进程成功直接和客户端建立`TCP`连接，之后这个连接的所有数据接收/发送都会通过这个`Worker`进行通讯，而不需要经过主进程的`Reactor`线程转发。

- `SWOOLE_BASE` 这种模式就是传统的异步非阻塞 Server。

- `worker_num`  参数对`BASE`仍然有效，会启动多个Worker进程。

- 相当于每个`Worker`进程承担了SWOOLE_PROCESS模式中的 `Reactor`线程 和 `Worker`进程两部分职责。

- `BASE` 模式中没有`Master`进程角色，只有`Manager`进程的角色
    - 如果`worker_num`设置为1，而且没有用到Task和MaxRequest的时候，底层将会直接创建一个单独的`Worker`进程而不创建 `Manager`进程。


### BASE模式的优点

- 无IPC 开销。  BASE 模式没有 IPC 开销，性能更好
- 代码更简洁。  BASE 模式代码更简单，不容易出错

### BASE模式的缺点

- 连接可能被关闭。
    - 如 `TCP` 连接中，连接是放在`Worker`进程中维持的，所以如果某个`Worker`挂掉时，此`Worker`内的所有连接都将被关闭。

- 长连接可能无法用到`Worker`进程。
    - 少量的TCP长连接无法利用到`Worker`进程。
    
- `Worker`进程可能不均衡。
    - TCP 连接与 Worker 是绑定的，长连接应用中某些连接的数据量大，这些连接所在的 Worker 进程负载会非常高。但某些连接数据量小，所以在 Worker 进程的负载会非常低，不同的 Worker 进程无法实现均衡。

### BASE模式适用场景

- 如果客户端连接不需要交互，那么可以使用到 `BASE`模式。例如 `Memcache`服务器等