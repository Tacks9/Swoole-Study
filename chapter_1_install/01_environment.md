<!--
 * @Descripttion: 环境准备
 * @Author: tacks321@qq.com
 * @Date: 2021-01-20 11:41:18
 * @LastEditTime: 2021-01-20 14:13:50
-->


### 安装 Swoole

> `Swoole` 扩展是根据PHP的标准扩展构建的。执行`phpize`从而调用`autoconf`生成 `./configure` 配置脚本，然后执行`./configure`来进行编译配置，接着利用`make`来进行编译，最后`make install`进行安装扩展。

- 采用`phpize`动态添加扩展
```shell
// 下载安装包 并进行解压
$ wget https://github.com/swoole/swoole-src/archive/v4.6.1.tar.gz -O swoole-4.6.1.tar.gz
$ tar -zxvf swoole-4.6.1.tar.gz
$ cd swoole-src-4.6.1/
// 生成配置脚本 (这里需要找到自己的PHP目录bin下)
$ /php/php73/bin/phpize
//  配置检测
$ ./configure --with-php-config=/php/php73/bin/php-config
// 编译
$ make -j4
// 安装
$ make install 
// 测试
$ make test
// 启用扩展
// 在php.ini 中加入一行 extension=swoole.so
$ vim /php/php73/php.ini
[swoole]
extension=swoole.so
// 重启PHP
$ /etc/init.d/php-fpm73 restart


// 验证swoole扩展是否安装成功
```

- 验证`swoole`扩展是否安装成功
```shell
$ php73 -m | grep swoole
swoole

$ php73 --ri swoole
swoole
Swoole => enabled
Author => Swoole Team <team@swoole.com>
Version => 4.6.1
Built => Jan 20 2021 13:57:29
coroutine => enabled with boost asm context
epoll => enabled
eventfd => enabled
signalfd => enabled
cpu_affinity => enabled
spinlock => enabled
rwlock => enabled
pcre => enabled
zlib => 1.2.7
mutex_timedlock => enabled
pthread_barrier => enabled
futex => enabled
async_redis => enabled

Directive => Local Value => Master Value
swoole.enable_coroutine => On => On
swoole.enable_library => On => On
swoole.enable_preemptive_scheduler => Off => Off
swoole.display_errors => On => On
swoole.use_shortname => On => On
swoole.unixsock_buffer_size => 8388608 => 8388608

```