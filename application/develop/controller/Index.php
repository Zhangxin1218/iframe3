<?php


namespace app\develop\controller;

// 开发者首页
class Index extends Base
{
    /**
     * 初始化方法
     * @date 2020/8/6 14:45
     */
    public function index() {
        if($this->system['install']) {
            return $this->fetch();
        }else {
            // 未安装过，填写配置信息 111
            return redirect('Install/step1');
        }
    }

    /**
     * 添加后台模块
     * @date 2020/8/19 16:33
     */
    public function add_module() {
        if(request()->isPost()) {
            $mg_module = $_POST['mg_module'];
            if(is_dir( app()->getAppPath().$_POST['mg_module'])) {
                json_response(0, '后台模块已存在');
            }
            // 安装模块
            $this->install('mg', $mg_module);
            $arr = json_decode(file_get_contents(app()->getAppPath().'develop/mg_module'), true);
            $arr[] = $_POST['mg_module'];
            file_put_contents(app()->getAppPath().'develop/mg_module', json_encode($arr));
            json_response(1, '安装成功');
        }else {
            return $this->fetch();
        }
    }

}