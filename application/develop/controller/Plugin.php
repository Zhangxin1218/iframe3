<?php


namespace app\develop\controller;

// 插件
use think\App;

class Plugin extends Base
{
    private $mg_module; // 模块

    /**
     * 构造方法
     * Plugin constructor.
     * @param App|null $app
     */
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->mg_module = param_check('mg_module');
        $this->assign('mg_module', $this->mg_module);
    }

    /**
     * 插件列表
     * @date 2020/8/13 18:32
     */
    public function plugin_list() {
        $list = [];
        $dir_list = folder_read(app()->getAppPath().'develop/resource/');
        foreach ($dir_list as $key => $item) {
            if($key == 'mg') continue;
            $readme = file_get_contents(app()->getAppPath().'develop/resource/'.$key.'/readme');
            $readme = explode("\n", $readme);
            $list[] = [
                'module'      => $key,
                'name'        => !empty($readme[0]) ? trim($readme[0]) : $key,
                'intro'       => !empty($readme[1]) ? trim($readme[1]) : '-',
                'update_time' => !empty($readme[2]) ? trim($readme[2]) : '-',
            ];
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * @return mixed|void
     * @date 2020/8/13 20:03
     */
    public function install_plugin() {
        $module = param_check('module');
        $this->install($module, $this->mg_module);
        json_response(1, '安装成功');
    }
}