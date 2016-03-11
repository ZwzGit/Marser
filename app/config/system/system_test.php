<?php

/**
 * 系统配置--测试环境
 */

return array(
    'app' => array(
        //项目名称
        'app_name' => 'marser',

        //控制器路径
        'controllers' => ROOT_PATH . '/app/controllers/',

        //控制器路径
        'controllers_namespace' => 'marser\app\controllers',

        //类库路径
        'libs' => ROOT_PATH . '/app/libs/',

        //视图路径
        'views' => ROOT_PATH . '/app/views/',

        //是否实时编译模板
        'is_compiled' => true,

        //模板路径
        'compiled_path' => ROOT_PATH . '/app/cache/compiled/',

        //日志根目录
        'log_path' => ROOT_PATH . '/app/cache/logs/',
    ),

    //数据库表配置
    'database' => array(
        //表前缀
        'prefix' => 'marser_',
    ),
);