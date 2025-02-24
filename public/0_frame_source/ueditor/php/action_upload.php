<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */
if( htmlspecialchars($_GET['action']) == 'uploadimage' ){
    $getFileInfo = $up->getFileInfo();
    // 本地路径
//    $file_path = substr(__DIR__, 0, -33).$getFileInfo['url'];
    $file_path = "/alidata/www/game.flyh5.cn".$getFileInfo['url'];

//    print_r($file_path);exit;
    // 图片名
    $file_name =  $getFileInfo['title'];

    // oss目标地址
//    $remotePath = 'game/admin_wuhui/2019/lzpc/'.$file_name;
    $remotePath = 'game/admin_yyt/2019/dfqc/'.$file_name;
    // 同步到oss
    $PATH = dirname(dirname(dirname(dirname(__DIR__))));
    include_once $PATH.'/vendor/oss-aly/samples/Common.php';

    $obj = new \Commons;
    $bucket = $obj::getBucketName();
    $ossClient = $obj::getOssClient();
    $result=$ossClient->uploadFile($bucket, $remotePath, $file_path);

    if( $result ){
        $ossUrl = 'http://img.flyh5.cn/'.$remotePath;

//        $ossUrl = $remotePath;
        // 替换显示路径
        $getFileInfo['url'] = $ossUrl;

    }else{
        $getFileInfo['url'] = 'http://' . $_SERVER['SERVER_NAME']  .$getFileInfo['url'];
    }
    print_r(json_encode($getFileInfo));exit;


//    return json_encode($getFileInfo,JSON_UNESCAPED_SLASHES);
}

if( htmlspecialchars($_GET['action']) == 'uploadvideo' ){
    $getFileInfo = $up->getFileInfo();
    // 本地路径
//    $file_path = $getFileInfo['file_path'];
    $file_path = "/alidata/www/game.flyh5.cn".$getFileInfo['url'];
    // 图片名
    $file_name =  $getFileInfo['title'];

    // oss目标地址
//    $remotePath = 'game/admin_wuhui/2019/lzpc/'.$file_name;
    $remotePath = 'game/admin_yyt/2019/dfqc/'.$file_name;
    // 同步到oss

    include_once  substr(__DIR__, 0, -33).'/extend/oss-aly/samples/Common.php';

    $obj = new \Commons;
    $bucket = $obj::getBucketName();
    $ossClient = $obj::getOssClient();
    $result=$ossClient->uploadFile($bucket, $remotePath, $file_path);

    if( $result ){
        $ossUrl = 'http://img.flyh5.cn/'.$remotePath;
        // 替换显示路径
        $getFileInfo['url'] = $ossUrl;
    }else{
        $getFileInfo['url'] = 'http://' . $_SERVER['SERVER_NAME']  .$getFileInfo['url'];
    }
    print_r(json_encode($getFileInfo));exit;

    return json_encode($getFileInfo,JSON_UNESCAPED_SLASHES);
}

/* 返回数据 */
return json_encode($up->getFileInfo());
