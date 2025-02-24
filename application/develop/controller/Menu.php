<?php


namespace app\develop\controller;

// 菜单管理
use think\App;

class Menu extends Base
{
    private $where = []; // 查询条件
    /**
     * 构造方法
     * Menu constructor.
     * @param App|null $app
     */
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->where['mg_module'] = param_check('mg_module');
        $this->assign('mg_module', $this->where['mg_module']);
    }

    /**
     * 菜单列表
     * @date 2020/8/7 0:17
     */
    public function menu_list() {
        $where = $this->where;
        $where['status'] = 1;
        $list = db('mg_menu')
            ->field('id, parent_id, name, router, icon, style, status, sort')
            ->where($where)
            ->order('sort ASC, id DESC')
            ->select();
        $list = arr_tree($list);
        foreach($list as &$item) {
            $item['name'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8).(empty($item['icon']) ? '' : '<i class="layui-icon '.$item['icon'].'"></i> ').$item['name'];
            $item['sort'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . $item['sort'];
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加菜单
     * @date 2020/8/7 0:29
     */
    public function add_menu() {
        if(request()->isPost()) {
            // 创建页面参数
            $build_page = $_POST['build_page'];
            unset($_POST['build_page']);
            // 创建页面参数
            $data = $_POST;
            if(empty($data['name'])) {
                json_response(0, '请输入菜单名称');
            }
            if($data['parent_id'] == 0 && (empty($data['controller']) && empty($data['action']))) {
                $data['router'] = '';
            }else {
                if(empty($data['module'])) json_response(0, '非一级菜单请填写模块名');
                if(empty($data['controller'])) json_response(0, '非一级菜单请填写控制器名');
                if(empty($data['action'])) json_response(0, '非一级菜单请填写方法名');
                $data['router'] = "{$data['module']}/{$data['controller']}/{$data['action']}";
            }
            $data['mg_module'] = param_check('mg_module');
            $data['create_time'] = time();
            // 创建页面
            if(!empty($build_page)) $this->build_page($build_page, $data['router']);
            db('mg_menu')->insert($data) ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $parent_menu = db('mg_menu')
                ->where($this->where)
                ->order('sort ASC, id DESC')
                ->field('id, parent_id, name, module, controller')
                ->select();
            $parent_menu = arr_tree($parent_menu);
            foreach($parent_menu as &$item) {
                $item['name'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8).'|-'.$item['name'];
            }
            $this->assign('parent_menu', $parent_menu);
            return $this->fetch();
        }
    }

    /**
     * 编辑菜单
     * @date 2020/8/7 0:33
     */
    public function edit_menu() {
        $id = input('id');
        if(request()->isPost()) {
            // 创建页面参数
            $build_page = $_POST['build_page'];
            unset($_POST['build_page']);
            // 创建页面参数
            $data = $_POST;
            if(empty($data['name'])) {
                json_response(0, '请输入菜单名称');
            }
            if($data['parent_id'] == 0 && (empty($data['controller']) && empty($data['action']))) {
                $data['router'] = '';
            }else {
                if(empty($data['module'])) json_response(0, '非一级菜单请填写模块名');
                if(empty($data['controller'])) json_response(0, '非一级菜单请填写控制器名');
                if(empty($data['action'])) json_response(0, '非一级菜单请填写方法名');
                $data['router'] = "{$data['module']}/{$data['controller']}/{$data['action']}";
            }
            $data['update_time'] = time();
            // 创建页面
            if(!empty($build_page)) $this->build_page($build_page, $data['router']);
            $where = $this->where;
            $where['id'] = $id;
            db('mg_menu')->where($where)->update($data) ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $parent_menu = db('mg_menu')
                ->where($this->where)
                ->order('sort ASC, id DESC')
                ->field('id, parent_id, name, module, controller')
                ->select();
            $parent_menu = arr_tree($parent_menu);
            foreach($parent_menu as &$item) {
                $item['name'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8).'|-'.$item['name'];
            }
            $where = $this->where;
            $where['id'] = $id;
            $data = db('mg_menu')
                ->where($where)
                ->field('parent_id, name, module, controller, action, icon, style, sort, status')
                ->find();
            $this->assign('data', $data);
            $this->assign('parent_menu', $parent_menu);
            return $this->fetch('menu/add_menu');
        }
    }


    /**
     * 删除菜单
     * @date 2020/8/7 0:33
     */
    public function del_menu() {
        $id = input('id');
        // 判断是否存在子菜单
        $have_children = db('mg_menu')->where('parent_id', $id)->value('id');
        if(!empty($have_children)) json_response(0, '存在子菜单不可删除');
        $where = $this->where;
        $where['id'] = $id;
        db('mg_menu')->where($where)->delete() ? json_response(1, '删除成功') : json_response(0, '删除失败');
    }

    /**
     * 创建页面
     * @param string $page
     * @param string $router
     * @param string $mg_module
     * @return bool
     * @date 2020/8/7 0:31
     */
    public function build_page($page='', $router='', $mg_module='') {
        $app_path = app()->getAppPath();
        $tmp_page_path = [
            'table' => $app_path.'develop/view/tpl/table.html',
            'form'  => $app_path.'develop/view/tpl/form.html',
        ];
        $router = explode('/', $router);
        if(count($router) != 3) return false;
        $router[1] = str_format($router[1]);
        $router[2] = str_format($router[2]);
        $page_path = $app_path."{$router[0]}/view/{$router[1]}/{$router[2]}.html";
        // 创建目录
        folder_build(dirname($page_path));
        $content = file_get_contents($tmp_page_path[$page]);
        file_put_contents($page_path, $content);
    }
}