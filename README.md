# Tp-common



[toc]

## 基础

### 说明

该组件包基于多应用模式开发,使用前需安装多应用模式扩展。

### 组件安装

~~~
$ composer require hmoe9/tp-commmon:dev-master
~~~

### 目录结构

~~~
tp-common
├─ data	
│  └─ database.sql 数据库初始化语句
│      
└─ src
    ├─ config.php 配置文件
    │  
    └─package
        ├─ Base.php
        ├─ Model.php
        ├─ Service.php
        │  
        ├─command
        │  ├─ TokenBucket.php
        │  │  
        │  └─migrate 数据库迁移命令
        │      ├─ MigrateTable.php
        │      │  
        │      └─stubs
        │          ├─ action_log.stub
        │          ├─ error_log.stub
        │          ├─ failed_jobs.stub
        │          └─ success_jobs.stub
        │              
        ├─contract
        │  └─basic
        │      ├─ ExceptionContract.php
        │      ├─ LogContract.php
        │      └─ ResponseContract.php
        │          
        ├─exception
        │     ├─ ExceptionHandle.php 自定义异常处理类
        │     └─ HttpExceptions.php http 异常处理类
        │      
        ├─job
        │  └─ Base.php think-queue 队列父类
        │      
        ├─lang 语言包
        │  └─zh-cn
        │      ├─ error_code.php 错误码
        │      └─ error_message.php 错误信息
        │          
        ├─middleware
        │     ├─ SignMiddleware.php 签名校验中间件
        │     └─ VerifyMiddleware.php 验证类中间件
        │      
        ├─model
        │  ├─job
        │  │  ├─ FailedJobsModel.php
        │  │  └─ SuccessJobsModel.php
        │  │      
        │  └─log
        │     ├─ ActionLogModel.php
        │     └─ ErrorLogModel.php
        │          
        ├─service
        │  ├─ BloomFilter.php 布隆过滤器服务
        │  ├─ TokenBucket.php 令牌桶服务
        │  │  
        │  └─basic
        │      ├─ Exception.php 异常处理服务
        │      ├─ Hash.php 哈希函数服务
        │      ├─ Log.php 日志记录服务
        │      ├─ Redis.php Redis服务
        │      ├─ Response.php 请求响应服务
        │      └─ Variable.php 自定义变量服务
        │          
        └─validate
              └─ SignValidate.php 签名验证类
~~~

### tp-common 配置文件

```php
use tp\common\package\model\{
    job\FailedJobsModel,
    job\SuccessJobsModel,
    log\ActionLogModel,
    log\ErrorLogModel
};

return [
    'error_http_code' => 500, // 抛出异常时 http 状态码

    // 组件包使用的基础表
    // 模型名 => 表名(不含表前缀)
    'migrate_table' => array(
        FailedJobsModel::class => 'failed_jobs',
        SuccessJobsModel::class => 'success_jobs',
        ActionLogModel::class => 'action_log',
        ErrorLogModel::class => 'error_log',
    ),

    'app_dev' => true, // 调试模式
    'app_dev_version' => 1.0, // 调试模式匹配参数
];
```

#### app_dev 调试模式说明

* 在关闭 tp 自带的 APP_DEBUG 调试模式时,可通过传参的形式动态的启用或关闭调试模式。
* 在请求数据中添加 dev_version 参数。值为 tp-common 中所配置。

## 数据库

### 迁移工具使用

配置 .env 中数据库信息

~~~
[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = datebase
USERNAME = username
PASSWORD = root
HOSTPORT = password
CHARSET = utf8
DEBUG = true
PREFIX = t_
~~~

#### 创建迁移文件

~~~
$ php think tp-common:migrate-table
~~~

#### 执行数据库迁移

~~~
$ php think migrate:run
~~~

### 数据表说明

* action_log 请求成功记录请求信息
* error_log 请求异常时记录异常信息
* failed_jobs 队列操作失败时记录失败信息
* success_jobs 队列操作成功时记录成功信息

## 中间件

### 验证类中间件

在应用目录中添加验证配置

~~~
项目
├─ app
│  └─ 应用名
│		├─ config
│	   	│	└─ verify_method.php 验证配置
│		│
│		├─ controller
│		│	└─ index
│		│		└─ User.php 
│		│
│		└─ validate
│			└─ index
│				└─ UserValidate.php 验证类明明规则(控制器名 + Validate) 
├─ config
├─ extend
├─ public
├─ ...
~~~

verify_method.php 文件内容

~~~php
// 控制器名,方法名不区分大小写
return array(
    // 多级控制器 => 方法
    'index.user' => array(
        'login' => 'get', // get 请求
        'register' => 'post', // post 请求
    ),
);
~~~

User.php 控制器

~~~php
namespace app\index\controller\index;

use tp\common\package\middleware\VerifyMiddleware;

class User
{
    protected $middleware = array(
        VerifyMiddleware::class,
    );

    public function login()
    {
        // ...
    }

    public function register()
    {
        // ...
    }
}
~~~

UserValidate.php 验证类

~~~php
namespace app\index\validate\index;

class UserValidate extends \think\Validate
{
    /**
     * 定义验证规则
     * @var array
     */
    protected $rule = array(
        'test1' => array('require', ),
        'test2' => array('require', ),
    );

    /**
     * 定义错误信息
     * @var array
     */
    protected $message = array(
        'test1' => array('require' => 'TEST_ERROR_1', ),
        'test2' => array('require' => 'TEST_ERROR_2', ),
    );

    /**
     * 定义验证场景,场景名必须小写
     * @var array
     */
    protected $scene = array(
        'login' => array('test2', ),

        'register' => array('test1', 'test2', ),
    );
}
~~~

## 语言包

组件自带语言包位于 src/package/zh-cn，可在应用目录中添加自定义语言包。

~~~
项目
├─ app
│  └─ 应用名
│      └─ lang
│			└─ zh-cn
│				├─ error_code.php 错误码
│      			└─ error_message.php 错误信息	
│    
├─ config
├─ extend
├─ public
├─ ...
~~~

error_code.php 文件内容

~~~php
return array(
    'TEST_ERROR_1' => '9000',
    'TEST_ERROR_2' => '9001',
);
~~~

error_message.php 文件内容

~~~php
return array(
    '9000' => '测试错误信息1',
    '9001' => '测试错误信息2',
);
~~~

## 队列

~~~php
use tp\common\package\job\Base;
use think\queue\Job;
use Exception;
use Throwable;

class TestJob extends Base
{
    public function fire(Job $job, $data)
    {
        $this->job = $job;
        try
        {
            // 业务处理 ... 

            // 操作成功日志记录
            $this->success();
        }
        catch (Exception $e)
        {
            $job->failed($e);
        }

        $job->delete();
    }

    public function failed($data, Throwable $e)
    {
        $this->error($e);
    }
~~~

## 服务 

### 常用

~~~php
public $bind = array(
    // 系统核心服务注册
    'exception' => Exception::class,
    'response' => Response::class,
    'system_log' => Log::class,
    'redis' => Redis::class,
    'var' => Variable::class,
    'hash' => Hash::class,

    // 自定义服务
    'bloom_filter' => BloomFilter::class,
    'token_bucket' => TokenBucket::class,
);

// 服务的调用
app('服务名')->方法名();

// 如果有 use tp\common\package\Base;
$this->app->服务名->方法名();
~~~

#### response

~~~php
public function index()
{
    $data = array(
    	'key' => 'value',
    );
	app('response')->setData($data); // 响应的数据
	return app('response')->ajaxReturn();
}
~~~

#### redis

配置 .env 中 redis 信息

~~~
[REDIS]
HOST = 127.0.0.1
PORT = 6379
PASSWORD =
TIMEOUT = 0
SELECT = 0
~~~

~~~php
public function index()
{
	$key = 'key';
	$value = 'value';
	app('redis')->set($key, $value);
	echo app('redis')->get($key);
}
~~~

#### var

~~~php
public function index()
{
	app('var')->key = 123;
	echo app('var')->key;
}
~~~

#### bloom_filter

~~~PHP
public function index()
{
	app('bloom_filter')->setKey('prefix'); // 设置key
	app('bloom_filter')->add(strval('value')); // 添加值到过滤器中
	$bool = app('bloom_filter')->has(strval('value')); // 只会返回 true 或 false,判断值是否存在过滤器中
}
~~~

## 异常处理

### 替换异常处理类

可将 app/provider.php 中的 think\exception\Handle 替换成自定义的异常处理。

```php
// use app\ExceptionHandle; // tp 自带异常处理
use app\Request;
use tp\common\package\exception\ExceptionHandle; // 组件包定义的异常处理

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
];
```

###  异常类使用和含义

在启用调试模式时,均不会记录错误信息。关闭调试后根据抛出的异常类来进行相对于操作。

* 使用 InvalidArgumentException 和 ValidateException 抛出异常时,不会记录错误信息。

  若未修改 tp-common.php 中 error_http_code,响应的 http 状态码为 500。

  ~~~php
  throw new InvalidArgumentException('TEST_ERROR_1');
  
  // ValidateException 为 tp 验证类校验失败时调用。不需要手动调用。
  // 在开启调试模式时自动行批量校验,显示所有未匹配的数据信息。
  throw new ValidateException('TEST_ERROR_1');
  ~~~

* 使用 LogicException 抛出异常时根据异常 code 区分是否记录错误信息。

  该异常 http 状态码为 200,无法修改。

  无法使用语言包中定义的错误码。响应的 code 固定为 10000。错误信息可自定义。

  ~~~php
    // 异常类 code 值默认为0。
    // boolval($e->getcode()) 为 true 时记录日志。
    throw new LogicException('TEST_ERROR_1', '1'); // 记录日志
    throw new LogicException('自定义错误信息2'); // 不记录日志
  ~~~

* 使用 HttpExceptions 抛出异常时可记录自定义的信息,可指定 http 状态码

  注意: 这里使用的是组件包内重写的 HttpExceptions 类。

  ~~~php
  use tp\common\package\exception\HttpExceptions;
  
  $param = array(
  	'key' => 'custom',
  );
  throw new HttpExceptions('TEST_ERROR_1', $param, 200);
  ~~~

*   

## TODO

