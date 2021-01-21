<?php

/*
 * @Descripttion: HTTP服务器 小demo
 * @Author: tacks321@qq.com
 * @Date: 2021-01-21 10:25:09
 * @LastEditTime: 2021-01-21 11:17:00
 */

/**
* 测试类
* @date  2021-01-21 11:15:16
*/
class test {
    
    /**
     * 对应方法的回调函数
     *
     * @date  2021-01-21 11:15:40
     * @param  $request  请求
     * @param  $response 响应
     */
    public function index($request, $response) {
        // HTTP服务器端显示这些信息
        echo "Uri: ", $request->server['request_uri'] , PHP_EOL;
        echo "Controller: test", PHP_EOL;
        echo "Action: index", PHP_EOL;
        
        // 浏览器回调执行
        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->end('<h1>Hello Swoole! HTTP Server!</h1>  <hr> <h2>test:index</h2> :) ');
    }
}





// 初始化一个HTTPServer的实例
$http = new Swoole\Http\Server('0.0.0.0', 9501);


// 设置事件回调
$http->on('request', function ($request, $response) {

    var_dump($request->server);

    // 此函数返回由字符串组成的数组，每个元素都是 string 的一个子串，它们被字符串 delimiter 作为边界点分割出来

    // 由于 chrome浏览器访问 http://127.0.0.1:9501/test/index/ 会默认多请求一下 http://127.0.0.1:9501/favicon.ico
    // 这里进行过滤
    if($request->server['request_uri'] == '/favicon.ico') {
        return false;
    }
    
    // 分析 request的uri，可以根据路由，进行分配，从而执行不同的回调方法
    list($controller, $action) = explode('/', trim($request->server['request_uri'],'/') );

    // 设置一个参数 判断是否有对应的控制器方法
    $is_has = false;

    // 判断类是否存在
    if(class_exists($controller)) {
        $obj = new $controller;
        // 判断类的方法是否存在
        if(method_exists($obj, $action)) {
            $is_has = true;
        }
    }

    if($is_has) {
        // 如果uri中有对应的控制器方法，则进行回调执行
        (new $controller)->$action($request, $response);
    }else{
        // 否则浏览器就按照默认的输出
        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->end('<h1>Hello Swoole! HTTP Server!</h1>  :) ');
    }


  
});



// 启动HTTP服务器
$http->start();


/*
1、应用程序可以根据 $request->server['request_uri'] 实现路由，从而实现我们常见的  控制器/方法  这样的路由
2、主要需要 class_exists() 和  method_exists() 函数来确定是否类存在
3、list() 函数可以  把数组中的值赋给一组变量

*/