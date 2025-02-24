<?php


namespace app\__MODULE__\controller;

// 文件上传模块
class Upload
{
    private $upload; // 上传类

    /**
     * 构造方法
     * Upload constructor.
     */
    public function __construct() {
        // 实例化上传类
        $upload = new \app\common\api\Upload();
        // 设置上传类型和文件大小
        $this->upload = $upload->oss()->size(5);
    }

    /**
     * 上传base64文件
     * @date 2021/4/25 15:14
     */
    public function upload_base64() {
        $file = param_check('file');
        // 上传单文件
        $src = $this->upload->upload($file, 'base64', '.png');
        json_response(0, '上传成功', ['src'=>$src]);
    }

    /**
     * 上传文件
     * @date 2021/3/15 16:21
     */
    public function upload_file() {
        if(empty($_FILES['file'])) json_response(0, '请选择文件');
        $file = $_FILES['file'];
        // 上传操作
        if(is_array($file['name'])) {
            // 上传多文件
            $file_list = $src_list = [];
            foreach ($file as $key=>$item) {
                for($i=0; $i<count($item); $i++) {
                    $file_list[$i][$key] = $item[$i];
                }
            }
            foreach ($file_list as $file) {
                $src_list[] = $this->upload->upload($file);
            }
            json_response(0, '上传成功', ['src'=>$src_list]);
        }else {
            // 上传单文件
            $src = $this->upload->upload($file);
            json_response(0, '上传成功', ['src'=>$src]);
        }
    }

    /**
     * 百度编辑器文件上传
     * @date 2021/3/15 16:52
     */
    public function editor_upload() {
        $response = [
            'state'    => 'SUCCESS',
            'url'      => '',
            'title'    => '',
            'original' => '',
            'type'     => '',
            'size'     => ''
        ];
        $action = empty($_GET['action']) ? 'config' : $_GET['action'];
        if($action == 'config') {
            $url = 'http:'.app()->env->get('resource_url').'ueditor/php/config.json';
            exit(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($url)));
        }else if($action == 'uploadimage') {
            // {"state":"SUCCESS","url":"\/ueditor\/php\/upload\/image\/20200610\/1591781657896167.png","title":"1591781657896167.png","original":"default.png","type":".png","size":5327}
            $key = 'upfile';
            $response['url']    = $this->upload->upload($_FILES[$key]);
        }else if($action == 'uploadvideo') {
            $key = 'upfile';
            // 开始上传
            $response['url']    = $this->upload->upload($_FILES[$key]);
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}