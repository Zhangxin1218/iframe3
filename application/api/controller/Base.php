<?php


namespace app\api\controller;

// 公共继承模块
class Base
{
    protected $openid; // 用户openid
    protected $redis_key; // redis目录名

    /**
     * 构造方法
     * Base constructor.
     */
    public function __construct() {
        // 计算项目名称
        $temp = explode("/", str_replace('\\', '/', substr(app()->getRootPath(), 0, strlen(app()->getRootPath()) - 1)));
        $this->redis_key = end($temp);
    }

    /**
     * 通过token换取openid和user_id并缓存 H5使用此方法
     * @return bool|string
     * @date 2021/3/23 13:47
     */
    public function get_user_id() {
        $openid = param_check('openid');
        // 读取redis缓存
        $redis_key  = "{$this->redis_key}:UserId_Cache:{$openid}";
        $redis      = redis_instance();
        $is_cache   = $redis->exists($redis_key);
        if($is_cache) return $redis->get($redis_key);

        // 验证openid真实性
        $user_info = db('user')->where('openid', $openid)->field('id as user_id, openid')->find();
        if(empty($user_info)) json_response(0, '用户不存在');

        // 设置redis缓存
        $redis->set($redis_key, $user_info['user_id'], 6);

        // 赋值openid
        $this->openid = $user_info['openid'];

        // 返回用户ID
        return $user_info['user_id'];
    }

    /**
     * 检查用户ID
     * @return bool|mixed|string 小程序使用此方法
     * @date 2021/3/23 13:33
     */
    public function check_user_id() {
        $user_info = session("{$this->redis_key}_user");
        if(empty($user_info) || empty($user_info['openid'])) json_response(0, '登录失效, 请重新登录！');

        // 赋值openid
        $this->openid = $user_info['openid'];

        // 读取redis缓存
        $redis_key  = "{$this->redis_key}:UserId_Openid_Cache:{$this->openid}";
        $redis      = redis_instance();
        $is_cache   = $redis->exists($redis_key);
        if($is_cache) return $redis->get($redis_key);

        // 验证openid真实性
        $user_id = db('user')->where('openid', $this->openid)->value('id as user_id');
        if(empty($user_id)) json_response(0, '用户不存在');

        // 设置redis缓存
        $redis->set($redis_key, $user_id, 6);

        // 返回用户ID
        return $user_id;
    }
}