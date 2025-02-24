<?php

namespace app\common\extend;

use think\App;
use think\Controller;

// 后台公共继承文件
class MgBase extends Controller {

    protected $session_key;    // 判定登录状态的key
    protected $mg_module;      // 后台模块
    protected $mg_user;        // 后台登陆用户
    protected $access = [];    // 当前用户菜单权限
    protected $current_router; // 当前访问路由 模块/控制器/方法
    protected $current_router2; // 当前访问路由 控制器/方法

    protected $allow_router = [
        'Index/index',
        'Index/welcome',
        'MgMember/login',
        'MgMember/logout',
        'MgMember/edit_pwd'
    ]; // 允许访问的路由

    /**
     * 构造方法
     * MgBase constructor.
     */
    public function __construct() {
        parent::__construct();

        // 异常错误报错级别, 禁止未定义变量错误的提示
        error_reporting(E_ERROR | E_PARSE);

        // 设置session_key
        $this->session_key = $this->session_key($this->mg_module);

        // 当前访问路由
        $this->current_router  = request()->module().'/'.request()->controller().'/'.request()->action();
        $this->current_router2 = request()->controller().'/'.request()->action();
        // 检查用户登录状态
        if($this->current_router2 != 'MgMember/login') $this->check_login();

        // 检查用户访问权限
        if($this->mg_user && !in_array($this->current_router2, $this->allow_router)) $this->check_access();
        if($this->mg_user && $this->current_router2 == 'Index/index') $this->check_access();

        // 控制台url
        $this->assign('home_page', url('Index/welcome'));
        // 资源目录
        $this->assign('resource_url', app()->env->get('resource_url'));

        global $mg_router;
        $mg_router = $this->mg_module;
    }

    /**
     * 检查用户登录状态
     * @date 2020/8/6 20:21
     */
    public function check_login() {
        $mg_user = session($this->session_key);
        if(empty($mg_user)) {
            // 重定向去登录页面
            echo '<script>var url = "'.url('MgMember/login').'";if(window.parent) {window.parent.location.href = url;}else {location.href = url;}</script>';
            exit();
        }else {
            $this->mg_user = $mg_user;
            $this->assign('mg_user', $this->mg_user);
            global $mg_user_id;
            $mg_user_id = $this->mg_user['id'];
        }
    }

    /**
     * 检查用户访问权限
     * @date 2020/8/6 20:23
     */
    public function check_access() {
        $access_info = cache("{$this->mg_module}_menu_{$this->mg_user['id']}");
        if(empty($access_info) || $access_info['expire_time'] < time()) {
            $access = db('mg_group')->where('id', $this->mg_user['group_id'])->value('access');
            $where = ['mg_module'=>$this->mg_module, 'status' => 1];
            if($access != '*') $where['id'] = explode(',', $access);
            $access_list = db('mg_menu')
                ->where($where)
                ->field('id, parent_id, icon, name, router, style')
                ->order('sort ASC, id DESC')
                ->select();
            foreach($access_list as $item) {
                if(!empty($item['router'])) $this->access[$item['router']] = ['name'=>$item['name'], 'icon'=>$item['icon'], 'style'=>$item['style']];
            }
            cache("{$this->mg_module}_menu_{$this->mg_user['id']}", [
                'expire_time' => time() + 5,
                'access_list' => $access_list,
                'access_menu' => $this->access
            ]);
        }else {
            $access_list  = $access_info['access_list'];
            $this->access = $access_info['access_menu'];
        }
        // 首页渲染左侧菜单
        if($this->current_router2 == 'Index/index') {
            // 无限级分类排序菜单
            $access_list = arr_tree($access_list, true);
            // 渲染左侧菜单
            $this->assign('access_list', $access_list);
        }else {
            // 判断访问权限
            if(!in_array($this->current_router, $this->allow_router)) {
                if(!in_array($this->current_router, array_keys($this->access))) {
                    if(request()->isAjax()) {
                        json_response(0, '没有访问权限');
                    }else {
                        $this->error('没有访问权限');
                        exit();
                    }
                }else {
                    // 普通页面
                    $this->assign('page_title', $this->access[$this->current_router]['name']);
                }
            }
        }
    }

    /**
     * 根据项目名设置session的键，避免多个项目的session冲突
     * @param $mg_module
     * @return string
     * @date 2020/7/7 16:13
     */
    private function session_key($mg_module='') {
        $path = dirname(dirname(dirname(app()->getAppPath())));
        if(strpos('/', $path)) {
            $temp = explode('/', $path);
        }else {
            $temp = explode('\\', $path);
        }
        return end($temp).$mg_module.'_user';
    }
}