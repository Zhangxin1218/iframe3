<?php


namespace app\api\controller;

// 用户授权模块
class Oauth extends Base
{
    private $app_id;
    private $app_secret;
    /**
     * 构造方法
     * Oauth constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->app_id     = app()->env->get('applet_app_id'); // 小程序 app_id
        $this->app_secret = app()->env->get('applet_app_secret'); // 小程序 app_secret
    }

    /**
     * 用户静默登录
     * @date 2020/8/15 13:58
     */
    public function oauth_login() {
        try {
            $js_code    = urldecode(param_check('js_code')); // 小程序wx.login获取的code, 必填参数
            $app_id     = $this->app_id; // 小程序 app_id
            $app_secret = $this->app_secret; // 小程序 app_secret
            // 请求微信授权接口, 获取openid
            $url  = "https://api.weixin.qq.com/sns/jscode2session?appid={$app_id}&secret={$app_secret}&js_code={$js_code}&grant_type=authorization_code";
            $data = json_decode(curl('GET', $url), true);
            // 判断接口是否请求成功
            if(empty($data['openid'])) {
                json_response(0, $data['errmsg'], $data);
            }
            // 判断openid是否存在
            $user = db('user')
                ->where('openid', $data['openid'])
                ->field('id as user_id, openid, unionid, avatar, nickname, mobile')
                ->find();
            if(empty($user)) {
                $user_id = db('user')->insertGetId([
                    'openid'        => $data['openid'],
                    'token'         => md5($data['openid']),
                    'unionid'       => isset($data['unionid']) ? $data['unionid'] : '',
                    'create_time'   => time()
                ]);
                $user = [
                    'user_id'   => $user_id,
                    'openid'    => $data['openid'],
                    'token'     => md5($data['openid']),
                    'unionid'   => isset($data['unionid']) ? $data['unionid'] : '',
                    'mobile'    => '',
                    'avatar'    => '',
                    'nickname'  => '',
                ];
            }
            json_response(1, 'success', [
                'session_key' => $data['session_key'],
                'user_info'   => $user
            ]);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 获取session_key接口
     * @date 2020/8/15 14:00
     */
    public function get_session_key() {
        try {
            $js_code    = param_check('js_code'); // 小程序wx.login获取的code, 必填参数
            $app_id     = $this->app_id; // 小程序 app_id
            $app_secret = $this->app_secret; // 小程序 app_secret
            // 请求微信授权接口, 获取openid
            $url  = "https://api.weixin.qq.com/sns/jscode2session?appid={$app_id}&secret={$app_secret}&js_code={$js_code}&grant_type=authorization_code";
            $data = json_decode(curl('GET', $url), true);
            // 判断接口是否请求成功
            if(empty($data['openid'])) {
                json_response(0, $data['errmsg'], $data);
            }
            json_response(1, 'success', $data);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }


    /**
     * 存储头像昵称 适用于不需要unionid的需求
     * @date 2020/7/27 14:47
     */
    public function perfect_info() {
        try {
            $user_id  = $this->get_user_id();
            $avatar   = param_check('avatar');
            $nickname = param_check('nickname');
            $res = db('user')
                ->where('id', $user_id)
                ->update([
                    'avatar'      => $avatar,
                    'nickname'    => $nickname,
                    'update_time' => time()
                ]);
            $res ? json_response(1,'修改成功', [
                'nickname'  => $nickname,
                'avatar'    => $avatar
            ]) : json_response(0,' 修改失败');
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 解密unionid并存储头像昵称
     * @date 2020/8/15 14:03
     */
    public function de_unionid() {
        try {
            $user_id      = $this->get_user_id();
            $app_id       = $this->app_id; // 小程序 app_id
            $session_key  = urldecode(param_check('session_key')); // get_session_key接口获得的session_key
            $iv           = urldecode(param_check('iv')); // 点击授权按钮获取的iv
            $e_data       = urldecode(param_check('e_data')); // 点击授权按钮获取encryptedData
            // AES解密
            $result = $this->decrypt_data($e_data, $iv, $session_key, $data, $app_id);
            // 判断结果
            if($result == 0) {
                $data = json_decode($data, true);
                db('user')->where('id', $user_id)->update([
                    'nickname'    => $data['nickName'],
                    'avatar'      => $data['avatarUrl'],
                    'unionid'     => isset($data['unionId']) ? $data['unionId'] : '',
                    'update_time' => time()
                ]);
                json_response(1, '解密成功', [
                    'unionid' => isset($data['unionId']) ? $data['unionId'] : ''
                ]);
            }else {
                json_response(0, '解密失败, 错误代码：'.$result);
            }
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 手机号解密
     * @date 2020/8/15 14:02
     */
    public function de_mobile() {
        try {
            $user_id      = $this->get_user_id();
            $app_id       = $this->app_id; // 小程序 app_id
            $session_key  = urldecode(param_check('session_key')); // get_session_key接口获得的session_key
            $iv           = urldecode(param_check('iv')); // 点击授权按钮获取的iv
            $e_data       = urldecode(param_check('e_data')); // 点击授权按钮获取encryptedData
            // AES解密
            $result = $this->decrypt_data($e_data, $iv, $session_key, $data, $app_id);
            // 判断结果
            if($result == 0) {
                $data = json_decode($data, true);
                if(empty($data['purePhoneNumber'])) {
                    json_response(0, '手机号不存在');
                }
                db('user')->where('id', $user_id)->update([
                    'mobile'      => $data['purePhoneNumber'],
                    'update_time' => time()
                ]);
                json_response(1, '解密成功', [
                    'mobile' => $data['purePhoneNumber']
                ]);
            }else {
                json_response(0, '解密失败, 错误代码：'.$result);
            }
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $sessionKey string getCode返回的session_key参数
     * @param $data array 解密后的原文
     * @param $app_id string AppId
     * @return int 成功0，失败返回对应的错误码
     * @date 2020/8/15 14:04
     */
    private function decrypt_data($encryptedData, $iv, $sessionKey, &$data, $app_id) {
        $ErrorCode = [
            'IllegalAesKey'     => -41001, // encodingAesKey 非法
            'IllegalIv'         => -41002, // iv错误
            'IllegalBuffer'     => -41003, // aes 解密失败
            'DecodeBase64Error' => -41004, // 解密后得到的buffer非法
            'OK'                => 0,
        ];
        if (strlen($sessionKey) != 24) {
            return $ErrorCode['IllegalAesKey'];
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return $ErrorCode['IllegalIv'];
        }
        $aesIV      = base64_decode($iv);
        $aesCipher  = base64_decode($encryptedData);
        $result     = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj    = json_decode( $result );
        if($dataObj == NULL) {
            return $ErrorCode['IllegalBuffer'];
        }
        if($dataObj->watermark->appid != $app_id) {
            return $ErrorCode['IllegalBuffer'];
        }
        $data = $result;
        return $ErrorCode['OK'];
    }
}