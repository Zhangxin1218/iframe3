<?php


namespace app\api\controller;

// H5授权
class Oauth extends Base
{
    private     $app_id; // 公众号appid
    private     $app_secret; // 公众号密钥
    private     $scope; // 授权类型
    private     $front_url; // 前端地址
    private     $state; // 自定义参数


    /**
     * 构造方法
     * Oauth constructor.
     */
    public function __construct() {
        parent::__construct();

        // 设置公众号信息
        $this->app_id = '';
        $this->app_secret = '';

        // 授权类型 [snsapi_userinfo]-非静默授权 [snsapi_base]-静默授权
        $this->scope = 'snsapi_userinfo';

        // 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
        $this->state = 'state';

        // 设置前端链接, 统一使用https
        $this->front_url = '';
    }

    /**
     * 跳转授权
     * @date 2021/3/8 10:34
     */
    public function index() {
        // 授权
        $this->authorization();
        exit();
    }


    /**
     * 分享sdk注册
     * @date 2021/3/8 10:35
     */
    public function share() {
        try {
            $params = [
                'noncestr'      => str_random(16),
                'jsapi_ticket'  => $this->get_ticket(),
                'timestamp'     => time(),
                'url'           => param_check('url')
            ];
            ksort($params);
            $str = '';
            foreach ($params as $k=>$v) {
                $str .= "$k=$v&";
            }
            $signature = sha1(substr($str, 0, strlen($str) - 1));

            json_response(1, 'success', [
                'appId'         => $this->app_id,
                'timestamp'     => $params['timestamp'],
                'nonceStr'      => $params['noncestr'],
                'signature'     => $signature,
            ]);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }

    }

    /**
     * 获取jsapi ticket
     * @date 2021/3/8 14:26
     */
    private function get_ticket() {
        $redis  = redis_instance();
        $key    = "JsApi:{$this->app_id}";
        $refresh_ticket = true;
        // 判断是否存在
        if($redis->exists($key)) {
            $res = json_decode($redis->get($key), true);
            if(empty($res['expire_time']) || $res['expire_time'] < time()) {
                $refresh_ticket = true;
            }else {
                $refresh_ticket = false;
            }
        }
        if($refresh_ticket) {
            $access_token = $this->get_access_token();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
            $res = json_decode(curl('GET', $url), true);
            if(!empty($res['ticket'])) {
                $res['expire_time'] = time() + 7000;
                // 存储redis
                $redis->set($key, json_encode($res), 7000);
            }else {
                json_response(0, '获取ticket失败', $res);
            }
        }
        return isset($res['ticket']) ? $res['ticket'] : '';
    }

    /**
     * 获取access_token
     * @date 2021/3/8 14:20
     */
    private function get_access_token() {
        $redis  = redis_instance();
        $key    = "AccessToken:{$this->app_id}";
        $refresh_token = true;
        // 判断是否存在access_token
        if($redis->exists($key)) {
            $res = json_decode($redis->get($key), true);
            if(empty($res['expire_time']) || $res['expire_time'] < time()) {
                $refresh_token = true;
            }else {
                $refresh_token = false;
            }
        }
        if($refresh_token) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->app_id}&secret={$this->app_secret}";
            $res = json_decode(curl('GET', $url), true);
            if(!empty($res['access_token'])) {
                $res['expire_time'] = time() + 7000;
                // 存储redis
                $redis->set($key, json_encode($res), 7000);
            }else {
                json_response(0, '获取access_token失败', $res);
            }
        }
        return isset($res['access_token']) ? $res['access_token'] : '';
    }


    /**
     * 授权
     * @date 2021/1/28 14:05
     */
    private function authorization() {
        if(!empty($_GET['code'])) {
            $data = json_decode(curl('GET', "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$_GET['code']}&grant_type=authorization_code"), true);
            $data = json_decode(curl('GET', "https://api.weixin.qq.com/sns/userinfo?access_token={$data['access_token']}&openid={$data['openid']}&lang=zh_CN"), true);
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
            // 用户信息存后端SESSION
            session("{$this->redis_key}_user", $data);


            // 用户信息存储前端缓存, 抽奖项目不可使用本方式
//            $param = json_encode(['token'=>$token, 'nickname'=>$data['nickname'], 'avatar'=>$data['headimgurl']]);
//            echo "<script>window.sessionStorage.setItem('param','{$param}');</script>";
//            echo "<script>window.localStorage.setItem('param','{$param}');</script>";
//            echo "<script>window.location='$this->front_url'</script>";
        }else {
            $callback_url = url_current('https');
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$callback_url}&response_type=code&scope={$this->scope}&state={$this->state}#wechat_redirect";
            header("Location:" . $url);
        }
    }
}