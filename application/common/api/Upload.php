<?php


namespace app\common\api;

// 文件上传类

class Upload
{
    private $upload_type; // 上传类型 oss cos
    private $max_upload_size = 5; // 上传文件大小, 单位(M)

    /**
     * 上传文件
     * @param $file $_FILES接收的文件
     * @param string $file_type 文件类型 file-文件 base64-base64文件流
     * @param string $ext 文件后缀, base64文件类型需要传, 如png jpg
     * @param string $file_name 文件名
     * @return mixed
     * @date 2021/3/15 14:35
     */
    public function upload($file, $file_type='file', $ext='', $file_name='') {
        set_time_limit(0);
        if(!in_array($file_type, ['file', 'base64'])) json_response(0, '文件类型错误');
        // 处理文件信息
        if($file_type == 'file') {
            $file_size = $file['size'];
            $arr = explode('.', $file['name']);
            $ext = count($arr) > 1 ? '.'.end($arr) : '';
        }else if($file_type == 'base64') {
            // base64文件解码
            $file = str_replace(' ', '+', $file);
            $file = explode('base64,', $file);
            $file = end($file);
            $file = base64_decode($file);
            $file_size = strlen($file);
        }else {
            $file_size = 0;
        }
        // 判断文件大小
        if($file_size >= $this->max_upload_size*1024*1024) json_response(0, '文件超出限制大小, 请上传'.$this->max_upload_size.'M内的文件', $file);
        // 生成文件存储名
        if(!empty($file_name)) {
            $save_name = '/'.$file_name.$ext;
        }else {
            $save_name = '/'.date('Ym').'/'.date('d').'_'.time().uniqid().$ext;
        }
        // 上传到对应服务器, 返回资源链接
        $fun = $this->upload_type;
        return $this->$fun($file, $file_type, $save_name);
    }

    /**
     * 设置上传文件大小
     * @param $size
     * @return $this
     * @date 2021/1/28 14:53
     */
    public function size($size=5) {
        $this->max_upload_size = $size;
        return $this;
    }

    /**
     * 设置oss上传类型
     * @return $this
     * @date 2021/1/28 14:49
     */
    public function oss() {
        $this->upload_type = 'oss_upload';
        return $this;
    }

    /**
     * 设置cos上传类型
     * @return $this
     * @date 2021/1/28 14:49
     */
    public function cos() {
        $this->upload_type = 'cos_upload';
        return $this;
    }

    /**
     * 设置本地上传
     * @return $this
     * @date 2021/1/28 14:51
     */
    public function local() {
        $this->upload_type = 'local_upload';
        return $this;
    }

    /**
     * 设置aws上传
     * @date 2021/4/25 11:47
     */
    public function aws() {
        $this->upload_type = 'aws_upload';
        return $this;
    }

    /**
     * 本地上传
     * @param string $file 文件
     * @param string $file_type 文件类型 file base64
     * @param string $save_name 文件名称
     * @return string 文件地址
     * @date 2021/4/25 15:26
     */
    private function local_upload($file, $file_type, $save_name) {
        $config     = config('local');
        $save_name  = './'.$config['static_path'].$save_name;
        $file_path  = dirname($save_name);
        folder_build($file_path);
        if($file_type == 'base64') {
            $res = file_put_contents($save_name, $file);
        }else if($file_type == 'file') {
            $res = move_uploaded_file($file['tmp_name'], $save_name);
        }
        if(empty($res)) json_response(0, '上传失败');
        return $config['static_url'].substr($save_name, 1, strlen($save_name) - 1);
    }

    /**
     * OSS上传文件
     * @param string $file 文件
     * @param string $file_type 文件类型 file base64
     * @param string $save_name 文件名称
     * @return string 文件地址
     * @date 2021/4/25 15:26
     */
    private function oss_upload($file, $file_type, $save_name) {
        try {
            $config     = config('oss');
            $ossClient  = new \OSS\OssClient($config['access_key_id'], $config['access_key_secret'], $config['upload_url'], false);
            $save_name  = $config['static_path'].$save_name;
            if($file_type == 'base64') {
                $res = $ossClient->putObject($config['bucket_name'], $save_name, $file);
            }else if($file_type == 'file') {
                $res = $ossClient->uploadFile($config['bucket_name'], $save_name, $file['tmp_name']);
            }
            if(empty($res['info']['url'])) json_response(1, '上传失败, 请查看打印信息', $res);
            // 删除临时文件
            if($file_type == 'file') @unlink($file['tmp_name']);
            return empty($config['static_url']) ? $res['info']['url'] : $config['static_url'].'/'.$save_name;
        } catch (\Exception $e) {
            json_response(0, '上传失败', $e);
        }
    }

    /**
     * COS上传文件
     * @param string $file 文件
     * @param string $file_type 文件类型 file base64
     * @param string $save_name 文件名称
     * @return string 文件地址
     * @date 2021/4/25 15:26
     */
    private function cos_upload($file, $file_type, $save_name) {
        try {
            // 开始上传
            $config    = config('cos');
            $cosClient = new \Qcloud\Cos\Client([
                'region'        => $config['region'],
                'schema'        => 'http',
                'credentials'   => [
                    'secretId'  => $config['secret_id'],
                    'secretKey' => $config['secret_key']
                ]
            ]);
            $save_name = $config['static_path'].$save_name;
            if($file_type == 'base64') {
                $res = $cosClient->putObject([
                    'Bucket'    => $config['bucket_name'],
                    'Key'       => $save_name,
                    'Body'      => $file,
                ]);
            }else if($file_type == 'file') {
                $res = $cosClient->upload($config['bucket_name'], $save_name, $file['tmp_name']);
            }

            if(empty($res['Location'])) json_response(1, '上传失败', $res);
            // 删除临时文件
            if($file_type == 'file') @unlink($file['tmp_name']);
            return empty($config['static_url']) ? $res['Location'] : $config['static_url'].'/'.$save_name;
        } catch (\Exception $e) {
            json_response(1, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * AWS上传文件
     * @param string $file 文件
     * @param string $file_type 文件类型 file base64
     * @param string $save_name 文件名称
     * @return string 文件地址
     * @date 2021/4/25 15:26
     */
    private function aws_upload($file, $file_type, $save_name) {
        try {
            $config = config('aws');
            $credentials = new \Aws\Credentials\Credentials($config['access_key_id'], $config['access_key_secret']);
            $sharedConfig = [
                'region'        => $config['region'],
                'version'       => 'latest',
                'credentials'   => $credentials
            ];
            $save_name = $config['static_path'].$save_name;

            // Create an SDK class used to share configuration across clients.
            $sdk = new \Aws\Sdk($sharedConfig);

            // Use an Aws\Sdk class to create the S3Client object.
            $s3Client = $sdk->createS3();

            if($file_type == 'base64') {
                // Send a PutObject request and get the result object.
                $res = $s3Client->putObject([
                    'Bucket'    => $config['bucket_name'],
                    'Key'       => $save_name,
                    'Body'      => $file
                ]);
            }else if($file_type == 'file') {
                $res = $s3Client->putObject([
                    'Bucket'    => $config['bucket_name'],
                    'Key'       => $save_name,
                    'SourceFile'=> $file['tmp_name']
                ]);
            }
            if(empty($res['ObjectURL'])) json_response(1, '上传失败', $res);

            // Print the body of the result by indexing into the result object.
            if($file_type == 'file') @unlink($file['tmp_name']);
            return empty($config['static_url']) ? $res['ObjectURL'] : $config['static_url'].'/'.$save_name;;
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }
}