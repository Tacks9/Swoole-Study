<!--
 * @Descripttion: Master 进程、Reactor 线程、Worker 进程、Task 进程、Manager 进程
 * @Author: tacks321@qq.com
 * @Date: 2021-01-21 17:46:59
 * @LastEditTime: 2021-01-21 18:36:58
-->


# Swoole 进程/线程

`Reactor` 类比 `Nginx` , `Worker` 类比 `PHP-FPM`。 

`Reactor`线程异步并行处理网络请求，然后再转发给 `Worker`进程中去处理，两者之间采用 `unixSocket`进行通信。

如果想执行异步任务，`Task`进程可以实现异步任务的处理，并且在任务执行结束的时候反馈给 `Worker`。



- Swoole 的 `Reactor`、`Worker`、`Task` 之间可以紧密的结合起来，提供更高级的使用方式。

    > 一个通俗的比喻。 Server是工厂，Reactor是销售，接收客户订单。Worker是工人，通过销售拿到订单后，就要去工作进行生产。Task是行政人员，异步的帮助Worker处理杂事，让Worker专心工作。

## `Master` 进程 （多线程进程）

### Reactor 线程

- `Reactor` 线程是在 `Master` 进程中创建的
- 负责维护客户端 `TCP` 连接、处理网络 `IO`、处理协议、收发数据
- 不执行任何 `PHP` 代码
- 将 `TCP` 客户端发来的数据缓冲、拼接、拆分成完整的一个请求数据包

### 心跳检测线程

### UDP收包线程




## `Manager` 进程 （多进程）

- 负责创建/回收 `Worker`、`Task` 进程

### Worker 进程

- 接受由 `Reactor` 线程投递的请求数据包，并执行 `PHP` 回调函数处理数据
- 生成响应数据并发给 `Reactor` 线程，由 `Reactor` 线程发送给 `TCP` 客户端
- 可以是**异步非阻塞模式**，也可以是**同步阻塞模式**
- `Worker` 以**多进程**的方式运行


### Task 进程

- 接受由 `Worker` 进程通过 `Swoole\Server`->`task/taskwait/taskCo/taskWaitMulti` 方法投递的任务
- 处理任务，并将结果数据返回（使用 `Swoole\Server`->`finish()`）给 `Worker` 进程
- 完全是**同步阻塞模式**
- `Task` 以**多进程**的方式运行