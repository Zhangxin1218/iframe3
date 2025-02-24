<?php


namespace app\__MODULE__\controller;

// 首页
class Index extends Base
{
    /**
     * 后台首页
     * @date 2020/8/6 22:31
     */
    public function index() {
        return $this->fetch('common@index/index');
    }

    /**
     * 欢迎页
     * @date 2020/6/3 14:10
     */
    public function welcome() {
        echo '欢迎使用';
    }
}