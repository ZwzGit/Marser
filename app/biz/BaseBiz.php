<?php

namespace cpsapi\app\biz;

use \Phalcon\Di;

class BaseBiz {

    /**
     * 系统配置数组
     * @var mixed
     */
    protected $_system_config;

    public function __construct(){
        $this -> _system_config = Di::getDefault() -> get('systemConfig');
    }
}