<?php


namespace app\develop\controller;


use think\App;
use think\Controller;
use think\Db;

// 公共继承文件
class Base extends Controller
{
    protected $system = []; // 系统信息
    protected $install_sql; // 安装插件生成的sql
    protected $install_sql_file; // 安装数据库脚本
    protected $resource_url = '/0_frame_source/';

    public function __construct(App $app = null) {
        parent::__construct($app);
        // 禁止正式环境访问本模块
        if(strtolower(app()->env->get('SYSTEM')) == 'product') {
            die('非法请求');
        }
        // 异常错误报错级别, 禁止未定义变量错误的提示
        error_reporting(E_ERROR | E_PARSE);
        // 读取是否安装;
        $this->system['install'] = file_exists($app->getAppPath().'develop/install.lock');

        // 赋值资源链接
        $this->assign('resource_url', $app->env->get('resource_url', $this->resource_url));

        if($this->system['install']) {
            $module_list = json_decode(file_get_contents($app->getAppPath().'develop/mg_module'), true);
            $this->assign('module_list', $module_list);
        }
    }

    /**
     * 安装插件
     * @param $module
     * @param $mg_module
     * @return mixed
     * @date 2020/8/6 22:55
     */
    public function install($module, $mg_module) {
        $app_path    = app()->getAppPath();
        $root_path   = $app_path.'develop/resource/'.$module.'/';
        $target_path = $app_path;
        $db_pre      = app()->env->get(SYSTEM.'.db_pre', '');
        $search      = ['__MODULE__', '__DB_PRE__', '__TIMESTAMP__'];
        $replace     = [$mg_module, $db_pre, time()];
        $files       = folder_read($root_path);
        $this->install_files($files, $root_path, $target_path, $search, $replace, $mg_module);
        // 建表
        $sql = $this->install_sql;
        if(!empty($sql)) {
            $sql_list = explode(';', $sql);
            foreach ($sql_list as $sql) {
                $sql = trim($sql);
                if(!empty($sql)) Db::execute($sql.';');
            }
        }
        if(!empty($this->install_sql_file)) {
            require_once $this->install_sql_file;
        }
        return $this->install_sql;
    }

    /**
     * 安装包文件夹移动
     * @param array $files 文件列表
     * @param string $root_path 源目录
     * @param string $target_path 目标目录
     * @param array $search
     * @param array $replace
     * @param string $mg_module // 安装模块
     * @date 2020/8/6 21:53
     */
    private function install_files($files=[], $root_path='', $target_path='', $search=[], $replace=[], $mg_module='') {
        foreach ($files as $dir=>$file) {
            if(is_array($file)) {
                $path = $target_path.($dir == '__MODULE__' ? $mg_module.'/' : $dir.'/' );
                $this->install_files($file, $root_path.$dir.'/', $path, $search, $replace);
            }else {
                $this->install_file($root_path.$file, $target_path.$file, $search, $replace);
            }
        }
    }

    /**
     * 安装包文件移动
     * @param string $file 源文件地址
     * @param string $target_file 目标文件地址
     * @param array $search
     * @param array $replace
     * @date 2020/8/6 21:43
     */
    private function install_file($file='', $target_file='', $search=[], $replace=[]) {
        $content = str_replace($search, $replace, file_get_contents($file));
        // 递归创建目录
        folder_build(dirname($target_file));
        // 判断文件是否需要覆盖
        if(strpos($file, 'sql.sql') !== false) {
            $this->install_sql = $content;
            return ;
        }else if(strpos($file, 'readme') !== false) {
            return ;
        }else if(strpos($file, 'query.php') !== false) {
            $this->install_sql_file = $file;
            return ;
        }
        // 备份原文件
        if(is_file($target_file) && file_get_contents($target_file) != $content) {
            file_put_contents($target_file.'_bak'.date('YmdHis'), file_get_contents($target_file));
        }
        file_put_contents($target_file, $content);
    }
}