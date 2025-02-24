<?php


namespace app\api\controller;

// H5三方授权
class Oauth extends Base
{
    private     $biz_appid; // 授权公众号appid
    private     $component_type; // 授权第三方平台
    private     $scope; // 授权类型
    private     $front_url; // 前端地址
    private     $state; // 自定义参数


    /**
     * 构造方法
     * Oauth constructor.
     */
    public function __construct() {
        parent::__construct();

        // 授权公众号appid 如果客户用自己的服务号授权则使用客户的appid
        // 云聚客平台-wxdda73d79aefd0cba 云聚客h5定制平台-wx7c3ed56f7f792d84 嘻游记h5定制平台-wx31dd8e9bcce66497 云美互动-wxa5e08871399cd731 深圳网晨-wx38da89ddf8b76665
        // 发红包必须使用d84
        $this->biz_appid = '';


        // 授权第三方平台 1-网晨集团 2-嘻游记 3-云聚客 4-云美互动
        $this->component_type = 2;

        // 授权类型 1-非静默授权 2-静默授权
        $this->scope = 1;

        // 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
        $this->state = 'state';

        // 设置前端链接, 统一使用https
        $this->front_url = '';

        // 授权
        $this->authorization();

        exit();
    }

    /**
     * 授权
     * @date 2021/1/28 14:05
     */
    private function authorization() {
        if(!empty($_POST['json'])) {
            $data = json_decode($_POST['json'], true);
            $user_exists = db('user')->where('openid', $data['openid'])->value('id');
            if(empty($user_exists)) {
                db('user')->insert([
                    'openid'        => $data['openid'],
                    'token'         => md5($data['openid']),
                    'nickname'      => $data['nickname'],
                    'avatar'        => $data['headimgurl'],
                    'unionid'       => !empty($data['unionid']) ? $data['unionid'] : '',
                    'create_time'   => time()
                ]);
            }
            // 基础access_token存入redis
            $redis = redis_instance();
            $redis->set("{$this->redis_key}:AccessToken", $data['token']);

            // 用户信息存后端SESSION
            session("{$this->redis_key}_user", $data);


            // 用户信息存储前端缓存, 抽奖项目不可使用本方式
//            $param = json_encode(['openid'=>$data['openid'], 'nickname'=>$data['nickname'], 'avatar'=>$data['headimgurl']]);
//            echo "<script>window.sessionStorage.setItem('param','{$param}');</script>";
//            echo "<script>window.localStorage.setItem('param','{$param}');</script>";
//            echo "<script>window.location='$this->front_url'</script>";
            exit();
        }else {
            $callback_url = url_current('https');
            $url = "https://auth.vrupup.com/sanguo/auth/api.php?appid={$this->biz_appid}&callback_url={$callback_url}&scope={$this->scope}&type={$this->component_type}&state={$this->state}";
            header("Location:" . $url, true, 301);
        }
    }
}