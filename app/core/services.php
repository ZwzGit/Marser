<?php

/**
 * DI注册服务配置文件
 * @package app/core
 * @version $Id
 */

use Phalcon\DI\FactoryDefault,
    Phalcon\Mvc\View,
    Phalcon\Mvc\Url as UrlResolver,
    Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter,
    Phalcon\Db\Profiler as DbProfiler,
    Phalcon\Mvc\View\Engine\Volt as VoltEngine;

$di = new FactoryDefault();

/**
 * 设置路由
 */
$di->set('router', function(){
    $Router = new \Phalcon\Mvc\Router();
    $routers = new \Phalcon\Config\Adapter\Php(ROOT_PATH . "/app/config/routers.php");
    foreach ($routers->toArray() as $key => $value){
        $Router->add($key,$value);
    }
    return $Router;
});

/**
 * 设置错误页
 */
$di->set('dispatcher', function() use ($config) {
    $eventsManager = new \Phalcon\Events\Manager();
    $eventsManager -> attach("dispatch:beforeException", function($event, $dispatcher, $exception) {
        if ($event -> getType() == 'beforeException') {
            switch ($exception->getCode()) {
                case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                    $dispatcher->forward(array(
                        'controller' => 'Index',
                        'action' => 'notfound'
                    ));
                    return false;
                case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward(array(
                        'controller' => 'Index',
                        'action' => 'notfound'
                    ));
                    return false;
            }
        }
    });
    $dispatcher = new \Phalcon\Mvc\Dispatcher();
    $dispatcher -> setEventsManager($eventsManager);
    $dispatcher -> setDefaultNamespace($config->app->controllers_namespace);
    return $dispatcher;
}, true);

/**
 * DI注入cookies服务
 */
$di->set('cookies', function() {
    $cookies = new \Phalcon\Http\Response\Cookies();
    $cookies -> useEncryption(false);
    return $cookies;
});

/**
 * DI注入模板服务
 */
$di -> set('view', function() use($config) {
    $view = new \Phalcon\Mvc\View();
    $view -> setViewsDir($config->app->views);
    $view -> registerEngines(array(
        '.phtml' => function($view, $di) use($config) {
            $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
            $volt -> setOptions(array(
                'compileAlways' => $config->app->is_compiled,
                'compiledPath'  =>  $config->app->compiled_path
            ));
            return $volt;
        },
    ));
    return $view;
});

/**
 * DI注入url服务
 */
$di -> set('url', function(){
    $url = new \Phalcon\Mvc\Url();
    return $url;
});

/**
 * DI注入DB配置
 */
$di->set('db', function () use($di) {
    $dbconfig = json_decode(get_cfg_var("marser.mysql"), true);
    if (!is_array($dbconfig) || count($dbconfig)==0) {
        throw new \Exception("the database config is error");
    }

    if (RUNTIME != 'pro') {
        $eventsManager = new \Phalcon\Events\Manager();
        // 分析底层sql性能，并记录日志
        $profiler = new DbProfiler();
        $eventsManager -> attach('db', function ($event, $connection) use ($profiler) {
            if($event -> getType() == 'beforeQuery'){
                //在sql发送到数据库前启动分析
                $profiler -> startProfile($connection -> getSQLStatement());
            }
            if($event -> getType() == 'afterQuery'){
                //在sql执行完毕后停止分析
                $profiler -> stopProfile();
                //获取分析结果
                $profile = $profiler -> getLastProfile();
                $sql = $profile->getSQLStatement();
                $startTime = $profile->getInitialTime();
                $endTime = $profile->getFinalTime();
                $executeTime = $profile->getTotalElapsedSeconds();
                //日志记录
                $logger = \marser\app\core\PhalBaseLogger::getInstance();
                $logger -> debug_log("{$sql}|startTime:{$startTime}|endTime:{$endTime}|executeTime:{$executeTime}");
            }
        });
    }

    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        "host" => $dbconfig['host'], "port" => $dbconfig['port'],
        "username" => $dbconfig['username'],
        "password" => $dbconfig['password'],
        "dbname" => $dbconfig['dbname'],
        "charset" => $dbconfig['charset'])
    );

    if(RUNTIME != 'pro') {
        /*记录底层SQL日志*/
        $connection->setEventsManager($eventsManager);
        /*记录底层SQL日志*/
    }

    return $connection;
});

/**
 * DI注入日志服务
 */
$di -> setShared('logger', function() use($di){
    $logger = \marser\app\core\PhalBaseLogger::getInstance();
    return $logger;
});

/**
 * DI注入api配置
 */
$di -> setShared('apiConfig', function() use($di){
    $config = \marser\app\core\Config::getInstance('api');
    $config -> set_run_time(RUNTIME);
    return $config;
});

/**
 * DI注入system配置
 */
$di -> setShared('systemConfig', function() use($di){
    $config = \marser\app\core\Config::getInstance('system');
    $config -> set_run_time(RUNTIME);
    return $config;
});
