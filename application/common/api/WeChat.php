<?php


namespace app\common\api;


// 微信支付
use app\mass\controller\Oss;

class WeChat
{
    protected $app_id;      // 小程序appId
    protected $app_secret;  // 小程序appSecret
    protected $key;         // 商户平台密钥
    protected $mch_id;      // 商户号
    protected $sub_mch_id;  // 子商户号
    protected $sub_app_id;  // 子appId

    private $last_data; // 上次接口请求返回值临时存储


    /**
     * 获取上次接口返回值
     * @return mixed
     * @date 2020/7/20 15:40
     */
    public function get_last_data() {
        return $this->last_data;
    }

    /**
     * 构造方法
     * WeChat constructor.
     * @param string $app_id 小程序appId
     * @param string $app_secret 小程序appSecret
     * @param string $key 商户平台密钥 如果是特约商户, 要用主商户的key
     * @param string $mch_id 商户号
     * @param string $sub_mch_id 子商户号
     * @param string $sub_app_id 子appId
     */
    public function __construct($app_id='', $app_secret='', $key='', $mch_id='', $sub_mch_id='', $sub_app_id='') {
        $this->app_id      = !empty($app_id) ? $app_id : app()->env->get('applet_app_id');
        $this->app_secret  = !empty($app_secret) ? $app_secret :  app()->env->get('applet_app_secret');
        $this->key         = !empty($key) ? $key : app()->env->get('applet_key');
        $this->mch_id      = !empty($mch_id) ? $mch_id : app()->env->get('applet_mch_id');
        $this->sub_mch_id  = !empty($sub_mch_id) ? $sub_mch_id : app()->env->get('applet_sub_mch_id');
    }

    /**
     * 获取支付参数
     * @param string $openid 用户openid
     * @param string $order_sn 订单号
     * @param int $money 支付金额 单位: 分
     * @param string $notify_url 回调地址
     * @param string $body 订单商品信息 不可为空字符串，会下单失败
     * @return array|bool
     * @date 2020/6/12 18:29
     */
    public function pay_param($openid='', $order_sn='', $money=0, $notify_url='', $body='支付') {
        $data['data'] = [
            'appid'            => $this->app_id,
            'mch_id'           => $this->mch_id,
            'nonce_str'        => $this->str_random(18),
            'sign_type'        => 'MD5',
            'body'             => $body,
            'out_trade_no'     => $order_sn,
            'total_fee'        => $money,
            'spbill_create_ip' => request()->ip(),
            'notify_url'       => $notify_url,
            'trade_type'       => 'JSAPI'
        ];
        if(!empty($this->sub_mch_id)) {
            $data['data']['sub_mch_id'] = $this->sub_mch_id;
            $data['data']['sub_app_id'] = $this->sub_app_id;
            $data['data']['sub_openid'] = $openid;
        }else {
            $data['data']['openid'] = $openid;
        }
        ksort($data['data']);
        $str = '';
        foreach($data['data'] as $k => $v){
            $str .= "$k=$v&";
        }
        $str .= 'key='.$this->key;
        $data['data']['sign'] = strtoupper(md5($str));
        $data['data'] = $this->array_to_xml($data['data']);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = $this->curl('POST', $url, $data);
        $param = $this->xml_to_array($res);
        if(empty($param['prepay_id'])) {
            exit(json_encode([
                'code'  => 0,
                'msg'   =>'下单失败',
                'data'  => [
                    'response' => $param,
                    'params'   => $data['data']
                ]
            ], JSON_UNESCAPED_UNICODE));
        }
        $pay_param = array();
        if($param['result_code'] == 'SUCCESS') {
            $pay_param['appId']     = $this->app_id;
            $pay_param['timeStamp'] = (string)time();
            $pay_param['nonceStr']  = $param['nonce_str'];
            $pay_param['package']   = 'prepay_id=' . $param['prepay_id'];
            $pay_param['signType']  = 'MD5';
            ksort($pay_param);
            $pay_param['key'] = $this->key;
            $string = '';
            foreach($pay_param as $k=>$v) {
                $string .= "$k=$v&";
            }
            $string = substr($string, 0, strlen($string) - 1);
            $pay_param['string'] = $string;
            $pay_param['paySign'] = strtoupper(md5($string));
            return [
                'timeStamp'     => $pay_param['timeStamp'],
                'nonceStr'      => $pay_param['nonceStr'],
                'package'       => $pay_param['package'],
                'signType'      => $pay_param['signType'],
                'paySign'       => $pay_param['paySign']
                ];
        }else {
            return false;
        }
    }

    /**
     * 验证签名
     * @param $callback
     * @date 2020/6/12 18:36
     */
    public function check_sign($callback) {
        $xml = file_get_contents('php://input');
        $data = $this->xml_to_array($xml);
        if(!empty($data['return_code']) && $data['return_code'] == "SUCCESS") {
            $_sign = $data['sign'];
            unset($data['sign']);
            ksort($data);
            $sign = http_build_query($data);
            // md5处理 转大写
            $sign = strtoupper(md5($sign.'&key='.$this->key));
            if($sign !== $_sign) {
                exit('error');
            }
            try {
                $this->last_data = $data;
                $callback($data);
            } catch (\Exception $e) {
                exit('error');
            }
            echo $this->array_to_xml([
                'return_code' => 'SUCCESS',
                'return_msg'  => 'OK'
            ]);
        }
    }

    /**
     * 获取access_token
     * @return string
     * @date 2020/10/16 14:25
     */
    public function get_access_token() {
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
            $res = json_decode($this->curl('GET', $url), true);
            if(!empty($res['access_token'])) {
                $res['expire_time'] = time() + 7000;
                // 存储redis
                $redis->set($key, json_encode($res), 7000);
            }else {
                json_response(0, '获取access_token错误', $res);
            }
        }
        return isset($res['access_token']) ? $res['access_token'] : '';
    }

    /**
     * 生成小程序码
     * @param string $scene
     * @param string $page
     * @param bool $is_hyaline
     * @param int $width
     * @param bool $auto_color
     * @param array $line_color
     * @return string
     * @date 2020/10/28 15:56
     */
    public function create_qr_code($scene='', $page='', $is_hyaline=true, $width=1280, $auto_color=false, $line_color=['r'=>0, 'g'=>0,'b'=>0]) {
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$this->get_access_token();
        $data['data'] = json_encode([
            'scene'         => $scene,
            'page'          => $page,
            'width'         => $width,
            'is_hyaline'    => $is_hyaline,
            'auto_color'    => $auto_color,
            'line_color'    => $line_color
        ]);
        $res = $this->curl('POST', $url, $data);
        if(json_decode($res, true)['errcode']) {
            json_response(0, '生成太阳码失败, error_code: '.json_decode($res, true)['errcode'], $res);
        }else {
            // 上传太阳码到OSS
            $oss = new Upload();
            $path = $oss->upload('data:image/jpeg;base64,' . chunk_split(base64_encode($res)), 'base64', 'png');
            return $path;
        }
    }



    /**
     * 生成随机字符串
     * @param int $lenth
     * @return string
     */
    private function str_random($lenth=6) {
        $base = 'qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM';
        $str = '';
        for($i=1; $i<=$lenth; $i++) {
            $str .= $base[mt_rand(0, strlen($base) - 1)];
        }
        return $str;
    }

    /**
     * xml转数组
     * @param $xml
     * @return mixed
     * @date 2020/6/12 18:26
     */
    private function xml_to_array($xml) {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     * 数组转xml
     * @param $arr
     * @return string
     * @date 2020/6/12 18:26
     */
    private function array_to_xml($arr) {
        if(!is_array($arr) || count($arr) <= 0) {
            json_response(0, "数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)) {
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }


    /**
     * Curl操作
     * @param string $type 请求类型 'POST' 或 'GET' 大小写都可以
     * @param string $url 请求地址 url
     * @param array $data 数组 cookie 请求cookie data post请求数据
     * @param bool $headerFile 返回头信息 如果页面做了跳转 则可以从返回头信息获得跳转地址，应用场景不多
     * @return bool|mixed
     */
    private function curl($type, $url, $data=[], $headerFile=false) {
        $type = strtoupper($type);
        $type_list = ['POST', 'GET', 'PUT'];
        if(!in_array($type, $type_list)) $type = 'POST';
        $ch = curl_init();
        // 请求类型
        if($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }else if($type == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT"); //设置请求方式
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // 这个是解释gzip内容, 解决获取结果乱码 gzip,deflate
        // 是否存在请求字段信息
        if(!empty($data['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
        }
        // 是否存在cookie
        if(!empty($data['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $data['cookie']);
        }
        // 请求头
        if(!empty($data['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $data['header']);
        }

        // 证书
        if(!empty($data['ssl_cert'])) {
            curl_setopt($ch,CURLOPT_SSLCERT, $data['ssl_cert']);
        }
        if(!empty($data['ssl_key'])) {
            curl_setopt($ch,CURLOPT_SSLKEY, $data['ssl_key']);
        }

        // 返回ResponseHeader
        if($headerFile) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }
        // 设置请求超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // 发送请求
        $result = curl_exec($ch);
        if (curl_errno($ch)) return false;
        curl_close($ch);
        return $result;
    }
}