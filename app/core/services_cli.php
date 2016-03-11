<?php

/**
 * DI注册服务配置文件
 */

use Phalcon\DI\FactoryDefault\CLI,
    Phalcon\Db\Adapter\Pdo\Mysql;

$di = new CLI();

/**
 * DI注入DB配置
 */
$di->setShared('db', function () use ($di) {
    $dbconfig = json_decode(get_cfg_var("marser.mysql"), true);
    if (!is_array($dbconfig) || count($dbconfig) == 0) {
        throw new \Exception("the database config is error");
    }

    if (RUNTIME != 'pro') {
        /*记录底层SQL日志*/
        $eventsManager = new \Phalcon\Events\Manager();
        $logger = \marser\app\core\PhalBaseLogger::getInstance();
        $eventsManager->attach('db', function ($event, $connection) use ($logger) {
            if ($event->getType() == 'beforeQuery') {
                $logger->write_log($connection->getSQLStatement(), 'debug');
            }
        });
        /*记录底层SQL日志*/
    }

    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
            "host" => $dbconfig['host'],
            "port" => $dbconfig['port'],
            "username" => $dbconfig['username'],
            "password" => $dbconfig['password'],
            "dbname" => $dbconfig['dbname'],
            "charset" => $dbconfig['charset'])
    );

    if (RUNTIME != 'pro') {
        /*记录底层SQL日志*/
        $connection->setEventsManager($eventsManager);
        /*记录底层SQL日志*/
    }

    return $connection;
});

/**
 * DI注入日志服务
 */
$di->setShared('logger', function () use ($di) {
    $logger = \marser\app\core\PhalBaseLogger::getInstance();
    return $logger;
});

/**
 * DI注入api配置
 */
$di->setShared('apiConfig', function () use ($di) {
    $config = \marser\app\core\Config::getInstance('api');
    $config -> set_run_time(RUNTIME);
    return $config;
});

/**
 * DI注入system配置
 */
$di->setShared('systemConfig', function () use ($di) {
    $config = \marser\app\core\Config::getInstance('system');
    $config -> set_run_time(RUNTIME);
    return $config;
});
