<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * 获取本周周一日期
 * @param $time_stamp
 * @return false|string
 * @date 2020/12/3 16:25
 */
function get_week($time_stamp=0) {
    $time_stamp = $time_stamp == 0 ? time() : $time_stamp;
    $week_num = date('w', $time_stamp) == 0 ? 7 : date('w', $time_stamp);
    if($week_num == 1) {
        $week = date('Y-m-d', $time_stamp);
    }else {
        $week = date('Y-m-d', $time_stamp - ($week_num - 1) * 86400);
    }
    return $week;
}

/**
 * 二维数组转换一维数组
 * @param array $arr
 * @param string $key
 * @param string $value
 * @return array
 * @date 2021/5/25 23:34
 */
function arr_to_key_value($arr=[], $key='value', $value='title') {
    $_arr = [];
    foreach ($arr as $v) {
        if(isset($v[$key]) && isset($v[$value])) $_arr[$v[$key]] = $v[$value];
    }
    return $_arr;
}


/**
 * 下载多个文件为zip
 * @param $file_list
 * @date 2021/3/15 16:38
 */
function download_files($file_list) {
    // 设置临时内存大小
    ini_set('memory_limit', '500M');
    set_time_limit(0);
    $file_name = time().'.zip';
    $zip = new \ZipArchive();
    // 创建一个空ZIP文件
    if ($zip->open($file_name, \ZipArchive::CREATE) === TRUE) {
        foreach ($file_list as $key => $value) {
            if(is_numeric($key)) {
                $name = $key.'.png';
            }else {
                $name = $key;
            }
            $zip->addFromString($name , file_get_contents($value));
        }
        $zip->close();
        $fp = fopen($file_name,"r");
        $file_size = filesize($file_name);//获取文件的字节
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=$file_name");
        $buffer=1024; //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器）
        $file_count=0; //读取的总字节数
        //向浏览器返回数据 如果下载完成就停止输出，如果未下载完成就一直在输出。根据文件的字节大小判断是否下载完成
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
        //下载完成后删除压缩包，临时文件夹
        if($file_count >= $file_size) {
            unlink($file_name);
        }
    }
    exit;
}

/**
 * 取文本中间内容
 * @param $str
 * @param $start
 * @param $end
 * @return bool|string
 * @date 2020/11/2 21:13
 */
function str_get_middle($str, $start, $end) {
    $start_position = strpos($str, $start);
    $end_position   = strpos($str, $end);
    return substr($str, $start_position+strlen($start), $end_position-strlen($start)-$start_position);
}

/**
 * 递归读取目录内容
 * @param string $root_path
 * @return array
 * @date 2020/8/6 21:24
 */
function folder_read($root_path=''){
    $files     = [];
    if(!is_dir($root_path)) return [];
    $dh = opendir($root_path);
    while ($file = readdir($dh)) {
        if($file == '.' || $file == '..') {
            continue;
        }
        if(is_dir($root_path.$file)) {
            $files[$file] = folder_read($root_path.$file.'/');
        }else {
            $files[] = $file;
        }
    }
    closedir($dh);
    return $files;
}

/**
 * 生成随机字符串
 * @param int $lenth
 * @return string
 */
function str_random($lenth=6) {
    $base = 'qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM';
    $str = '';
    for($i=1; $i<=$lenth; $i++) {
        $str .= $base[mt_rand(0, strlen($base) - 1)];
    }
    return $str;
}

/**
 * 大写字母转下划线加小写字母(忽略首字母)
 * @param string $name
 * @return string
 */
function str_format($name='') {
    $temp_array = array();
    for($i=0; $i<strlen($name); $i++) {
        $ascii_code = ord($name[$i]);
        if($ascii_code >= 65 && $ascii_code <= 90){
            if($i == 0) {
                $temp_array[] = chr ($ascii_code + 32);
            }else {
                $temp_array[] = '_'.chr ($ascii_code + 32);
            }
        } else{
            $temp_array[] = $name[$i];
        }
    }
    return implode('',$temp_array);
}

/**
 * 数组无限级分类
 * @param $arr
 * @param bool $sub_list // 是否放在子数组内
 * @param $key_val // 第一级的上级ID参数值
 * @param array $config
 * @param int $level
 * @return array
 */
function arr_tree($arr, $sub_list=false, $key_val=0, $config=[], $level=1) {
    $parent_key = isset($config['parent_key']) ? $config['parent_key'] : 'parent_id'; // 上级ID参数名称
    $key        = isset($config['key']) ? $config['key'] : 'id'; // 主键ID参数名称
    $res = array();
    foreach ($arr as $k=>$item) {
        if($item[$parent_key] == $key_val) {
            unset($arr[$k]);
            $item['level'] = $level;
            if($sub_list) {
                $item['sub_list'] = arr_tree($arr, $sub_list, $item[$key], $config, $level + 1);
                $res[] = $item;
            }else {
                $res[] = $item;
                $res = array_merge($res , arr_tree($arr, $sub_list, $item[$key], $config,$level + 1));
            }
        }
    }
    return $res;
}

/**
 * xml转数组
 * @param $xml
 * @return mixed
 * @date 2020/6/12 18:26
 */
function xml_to_array($xml) {
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
function array_to_xml($arr) {
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
 * 取出html内的img地址
 * @param string $content
 * @return array
 */
function html_parse_img($content='') {
    preg_match_all('/<img.*?src="(.*?)".*?>/', $content, $matches);
    return isset($matches[1]) ? $matches[1] : [];
}

/**
 * 解析百度编辑器内容
 * @param string $text
 * @return array
 * @date 2020/8/17 13:57
 */
function parse_ueditor($text='') {
    // 正则取出富文本内容
    preg_match_all("/<p.*?>(.*?)<\/p>/", $text, $p_list);
    $content = [];
    foreach ($p_list[1] as $p_key=>$p_item) {
        preg_match('/<img.*?src="(.*?)".*?\/>/', $p_item, $img);
        preg_match('/<embed.*?src="(.*?)".*?\/>/', $p_item, $video);
        if(empty($img[1]) && empty($video[1])) {
            if(strpos($p_list[0][$p_key], 'center')) {
                $p_item = str_replace('&nbsp;', '', $p_item);
                $indent = 2;
            }else if(strpos($p_item, '&nbsp;') === 0){
                $p_item = str_replace('&nbsp;', '', $p_item);
                $indent = 1;
            }else {
                $indent = 0;
            }
            $content[] = [
                'type'   => 'text',
                'value'  => trim(strip_tags($p_item)),
                'indent' => $indent
            ];
        }else if(!empty($img[1])) {
            $content[] = [
                'type'  => 'img',
                'value' => $img[1]
            ];
        }else if(!empty($video[1])) {
            $content[] = [
                'type'  => 'video',
                'value' => $video[1]
            ];
        }
    }
    return $content;
}

/**
 * 递归创建目录
 * @param string $dir
 * @return bool
 */
function folder_build($dir='') {
    if(!is_dir($dir)) {
        while(!is_dir(dirname($dir))) {
            if(!folder_build(dirname($dir))) {
                json_response(0, $dir.'目录写入失败');
            }
        }
        if(!is_writable($dir)) {
            return mkdir($dir, 0777, true);
        }else {
            json_response(0,$dir.'目录不可写');
        }
    }
}

/**
 * JSON格式返回数据
 * @param int $code
 * @param string $msg
 * @param array $data
 */
function json_response($code=0, $msg='', $data=[]) {
    echo json_encode([
        'code'  => $code,
        'msg'   => $msg,
        'data'  => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * 接口调用成功返回
 * @param string $msg
 * @param array $data
 * @param int $code
 * @return string
 * @date 2021/1/29 15:22
 */
function success($msg='', $data=[], $code=1) {
    return  json_encode([
        'code'  => $code,
        'msg'   => $msg,
        'data'  => $data,
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 接口调用失败返回
 * @param string $msg
 * @param array $data
 * @param int $code
 * @return string
 * @date 2021/1/29 15:22
 */
function error($msg='', $data=[], $code=0) {
    return  json_encode([
        'code'  => $code,
        'msg'   => $msg,
        'data'  => $data,
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 参数检查
 * @param string $name
 * @param bool $default
 * @param string $tips
 * @return array|bool
 */
function param_check($name, $default=false, $tips='') {
    $val = input($name);
    if(!empty($val)) {
        return $val;
    }else {
        if($default !== false) return $default;
        json_response(0, empty($tips) ? "{$name}不能为空" : $tips);
    }
}

/**
 * Curl操作
 * @param string $type 请求类型 'POST' 或 'GET' 大小写都可以
 * @param string $url 请求地址 url
 * @param array $data 数组 cookie 请求cookie data post请求数据
 * @param bool $headerFile 返回头信息 如果页面做了跳转 则可以从返回头信息获得跳转地址，应用场景不多
 * @return bool|mixed
 */
function curl($type, $url, $data=[], $headerFile=false) {
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //绕过ssl验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // 这个是解释gzip内容, 解决获取结果乱码 gzip,deflate
    // 是否存在请求字段信息
    if(!empty($data['data']) && $type == 'POST') {
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


/**
 * 大文件导出
 * @param $db
 * @param string $file_name
 * @param array $fields
 * @param $callback
 * @date 2020/11/7 15:59
 */
function big_array_to_csv($db, $file_name, $fields=[], $callback='') {
    set_time_limit(0);
    ini_set('memory_limit', '5G');
    error_reporting(0);
    // 表头
    header('Content-Type: application/vnd.ms-excel');   // header设置
    header("Content-Disposition: attachment;filename=".($file_name ? $file_name : '导出').".csv");
    header('Cache-Control: max-age=0');


    $fp = fopen('php://output','w');
    $header = empty($fields) ? array_keys($db->find()) : array_values($fields);
    $head = [];
    foreach($header as $i=>$value) {
        $value = is_array($value) ? $value[0] : $value;
        $head[$i] = iconv("UTF-8","GBK", $value);
    }
    fputcsv($fp,$head);

    $total = $db->count();
    $page_size = 5000;//每次只从数据库取10000条以防变量缓存太大

    for($o=0; $o<ceil($total/$page_size); $o++) {
        $data = $db->limit($o*$page_size, $page_size)->select();
        $data = is_callable($callback) ? $callback($data) : $data;
        foreach ($data as $i=>$item) {
            $list = [];
            foreach($fields as $k=>$v) {
                $value = isset($item[$k]) ? $item[$k] : '';
                if(is_array($v)) {
                    if(is_array($v[1])) {
                        // 数组类型处理
                        $value = $v[1][$value];
                    }else if(is_callable($v[1])) {
                        // 匿名函数处理
                        $value = $v[1]($item);
                    }else if(is_string($v[1])) {
                        // 字符串类型处理
                        if($v[1]  == 'datetime') {
                            // 格式化时间戳
                            $value = $value > 0 ? date('Y-m-d H:i:s', $value) : '';
                        }else if(!empty($v[1])) {
                            // 设置字段默认值
                            $value = $v[1];
                        }
                    }
                }
                // 处理数字变为科学计数法的问题
                if(is_numeric($value) && strlen($value) > 10) $value .= "\t";
                if(mb_substr($value, 0, 1) == 0) $value .= "\t";
                $list[$k] = iconv("UTF-8","GBK", (string)$value);
            }
            fputcsv($fp, $list);
        }
        // 刷新一下输出buffer，防止由于数据过多造成问题
        ob_flush();
        flush();
    }
    exit();
}


/**
 * 数组转csv
 * @param array $data 数组
 * @param string $file_name 文件名字
 * @param array $fields 字段介绍
 */
function array_to_csv($data=[], $file_name='', $fields=[]) {
    // 表头
    header('Content-Type: application/vnd.ms-excel');   // header设置
    header("Content-Disposition: attachment;filename=".($file_name ? $file_name : '导出').".csv");
    header('Cache-Control: max-age=0');
    $fp = fopen('php://output','a');
    $header = empty($fields) ? array_keys($data[0]) : array_values($fields);
    $head = [];
    foreach($header as $i=>$value) {
        $value = is_array($value) ? $value[0] : $value;
        $head[$i] = iconv("UTF-8","GBK", $value);
    }
    fputcsv($fp,$head);
    foreach ($data as $i=>$item) {
        $list = [];
        foreach($fields as $k=>$v) {
            $value = isset($item[$k]) ? $item[$k] : '';
            if(is_array($v)) {
                if(is_array($v[1])) {
                    // 数组类型处理
                    $value = $v[1][$value];
                }else if(is_callable($v[1])) {
                    // 匿名函数处理
                    $value = $v[1]($item);
                }else if(is_string($v[1])) {
                    // 字符串类型处理
                    if($v[1]  == 'datetime') {
                        // 格式化时间戳
                        $value = $value > 0 ? date('Y-m-d H:i:s', $value) : '';
                    }else if(!empty($v[1])) {
                        // 设置字段默认值
                        $value = $v[1];
                    }
                }
            }
            // 处理数字变为科学计数法的问题
            if(is_numeric($value) && strlen($value) > 10) $value .= "\t";
            if(mb_substr($value, 0, 1) == 0) $value .= "\t";
            $list[$k] = iconv("UTF-8","GBK", (string)$value);
        }
        fputcsv($fp, $list);
    }
    exit();
}

/**
 * 读取csv文件转换成数组
 * @param string $csv
 * @return array
 * @date 2020/8/21 16:11
 */
function csv_to_array($csv=''){
    setlocale(LC_ALL, 'zh_CN');
    set_time_limit(0);
    $data = [];
    $fs = fopen($csv,'r');
    $i = 0;
    while ($row = fgetcsv($fs)) {
        $i += 1;
        if($i == 1) continue;
        foreach($row as &$value) {
            $value = iconv('GBK', 'UTF-8', $value);
        }
        $data[] = $row;
    }
    fclose($fs);
    @unlink($csv);
    return $data;
}


/**
 * csv大文件导入
 * @param $csv
 * @return array
 * @date 2020/10/18 15:57
 */
function big_csv_to_array($csv) {
    function big_csv_read($csv) {
        $handle = fopen($csv, 'rb');
        while (feof($handle) === false) {
            yield fgetcsv($handle);
        }
        fclose($handle);
    }
    $result = big_csv_read($csv);
    $data = [];
    $i = 0;
    foreach($result as $row) {
        $i++;
        if($i==1) continue;
        foreach ($row as &$value) {
            $value = iconv('GBK', 'UTF-8', $value);
        }
        $data[] = $row;
    }
    return $data;
}

/**
 * 获得当前完整URL
 * @param string $request_scheme 请求头
 * @return string
 */
function url_current($request_scheme='https') {
    $redirect_uri = $request_scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    return $redirect_uri;
}

/**
 * 获取redis对象实例
 * @return Redis
 * @date 2020/6/4 14:04
 */
function redis_instance() {
    global $redis;
    if($redis) return $redis;
    $config = config('redis');
    $redis  = new \Redis();
    $redis->connect($config['redis_host'], $config['redis_port']);
    $redis->auth($config['redis_pass']);
    $redis->select($config['redis_index']);
    return $redis;
}

/**
 * redis锁
 * @param $lock
 * @param $param
 * @param $func
 * @param int $ttl
 * @date 2020/8/17 15:31
 */
function redis_lock($lock, $param, $func, $ttl=15) {
    $redis = redis_instance();
    $res   = $redis->setnx($lock, '1');
    if(!$res) json_response(0, '请求过快，请稍后再试哦~');
    $redis->expire($lock, $ttl);
    $result = $func($param, $redis);
    $redis->del($lock);
    exit($result);
}

/**
 * redis数据缓存
 * @param string $key redis键名
 * @param mixed $func 闭包方法
 * @param int $ttl 缓存时间
 * @return mixed
 * @date 2021/3/19 14:13
 */
function redis_get($key, $func, $ttl=15) {
    $redis = redis_instance();
    if($redis->exists($key)) {
        return json_decode($redis->get($key), true);
    }else {
        $data = $func();
        $redis->set($key, json_encode($data, JSON_UNESCAPED_UNICODE), $ttl);
        return $data;
    }
}

/**
 * 抽奖方法
 * @param array $prize_list 奖品列表
 * @param string $ratio_key 代表概率的键名
 * @return mixed
 * @date 2021/3/19 14:22
 */
function draw($prize_list=[], $ratio_key='') {
    if(empty($prize_list)) json_response(0, '奖品列表不可为空');
    $result = '';
    // 概率数组的总概率精度
    $radioSum = array_sum(array_column($prize_list, $ratio_key));
    //概率数组循环
    foreach ($prize_list as $key => $prize) {
        $randNum = mt_rand(1, $radioSum);
        if ($randNum <= $prize[$ratio_key]) {
            $result = $key;
            break;
        } else {
            $radioSum -= $prize[$ratio_key];
        }
    }
    return $prize_list[$result];
}

/**
 * 计算分页
 * @param int $page 页码
 * @param int $limit 每页展示条数
 * @return string
 * @date 2020/6/15 16:29
 */
function paginator($page=0, $limit=0) {
    if(empty($page))  $page  = param_check('page', 1);
    if(empty($limit)) $limit = param_check('limit', 15);
    return ($page - 1) * $limit . ',' . $limit;
}

/**
 * aes加密数据
 * @param string $data 加密前的字符串
 * @param string $key key
 * @param string $iv iv
 * @param string $method 加密方法
 * @return string
 * @date 2020/6/22 15:38
 *
 */
function aes_encrypt($data='', $key='', $iv='', $method='aes-128-cbc') {
    return openssl_encrypt($data, $method, $key, 0, $iv);
}

/**
 * aes解密
 * @param string $data 加密前的字符串
 * @param string $key key
 * @param string $iv iv
 * @param string $method 加密方法
 * @return string
 * @date 2020/6/22 15:40
 */
function aes_decrypt($data='', $key='', $iv='', $method='aes-128-cbc') {
    $data = openssl_decrypt($data, $method, $key, OPENSSL_ZERO_PADDING , $iv);
    return trim($data);
}

if (!function_exists('check_cors_request')) {
    /**
     * 跨域检测
     * Author zengxin(573908667@qq.com)
     */
    function check_cors_request()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN']) {
            $info        = parse_url($_SERVER['HTTP_ORIGIN']);
            $domainArr   = explode(',', 'localhost,127.0.0.1,*');
            $domainArr[] = request()->host(true);
            if (in_array("*", $domainArr) || in_array($_SERVER['HTTP_ORIGIN'], $domainArr) || (isset($info['host']) && in_array($info['host'], $domainArr))) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            } else {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');

            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                }
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                exit;
            }
        }
    }
}

if (!function_exists('response_result')) {
    /**
     * 响应结果
     * Author zengxin(573908667@qq.com)
     * @param $msg
     * @param null $data
     * @param int $code
     * @param null $type
     * @param array $header
     */
    function response_result($msg, $code = 0, $data = null, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => \think\facade\Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : (\think\facade\Request::instance()->param(config('var_jsonp_handler')) ? 'jsonp' : 'json');

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = \think\facade\Response::create($result, $type, $code)->header($header);
        throw new \think\exception\HttpResponseException($response);
    }
}

/**
 * 成功返回
 * Author zengxin(573908667@qq.com)
 * @param array $data
 * @param string $msg
 */
function suc_return($data = [], $msg = 'success')
{
    response_result($msg, 1, $data);
}

/**
 * 错误返回
 * Author zengxin(573908667@qq.com)
 * @param string $msg
 */
function err_return($msg = 'error')
{
    response_result($msg, 0);
}

/**
 * 参数校验
 * Author zengxin(573908667@qq.com)
 * @param \think\Validate $validate 验证器
 * @param string $scene 验证场景
 * @param array $params 需要验证参数
 */
function checkParam(\think\Validate $validate, $scene = '', $params = [])
{
    $result = $validate->scene($scene)->check($params ?: \think\facade\Request::param());
    if (!$result) {
        err_return($validate->getError());
    }
}