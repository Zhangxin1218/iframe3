<?php


namespace app\__MODULE__\controller;

// banner管理模块
class Banner extends Base
{
    // banner位置定义
    private $position_list = [
        1 => '首页轮播图'
    ];

    // 跳转类型定义
    private $type_list = [
        0 => '不跳转',
        1 => '小程序页面',
        2 => '外链'
    ];

    /**
     * banner列表
     * @date 2020/8/13 17:54
     */
    public function banner_list() {
        $where = [];
        if(!empty($_GET['name'])) $where[] = ['name', 'like', "%{$_GET['name']}%"];

        $data = db('banner')
            ->where($where)
            ->order('sort ASC')
            ->field('id, position, type, name, url, sort, status')
            ->paginate(10, false, ['query'=>$_GET]);
        $list = $data->all();
        foreach ($list as &$item) {
            $item['position_text'] = $this->position_list[$item['position']];
            $item['type_text']     = $this->type_list[$item['type']];
        }
        $this->assign('list', $list);
        $this->assign('page', $data->render());
        $this->assign('where', $_GET);
        return $this->fetch();
    }

    /**
     * 添加banner
     * @date 2020/8/13 17:54
     */
    public function add_banner() {
        if(request()->isPost()) {
            $data = $_POST;
            $data['create_time'] = time();
            db('banner')->insert($data) ? json_response(1, '添加成功') : json_response(0, '添加失败');
        }else {
            $this->assign('position_list', $this->position_list);
            $this->assign('type_list', $this->type_list);
            return $this->fetch();
        }
    }

    /**
     * 编辑banner
     * @date 2020/8/13 17:55
     */
    public function edit_banner() {
        $id = param_check('id');
        if(request()->isPost()) {
            $data = $_POST;
            $data['update_time'] = time();
            db('banner')->where('id', $id)->update($data) ? json_response(1, '编辑成功') : json_response(0, '编辑失败');
        }else {
            $data = db('banner')->where('id', $id)->field('position, name, image, type, url, sort, status')->find();
            $this->assign('position_list', $this->position_list);
            $this->assign('type_list', $this->type_list);
            $this->assign('data', $data);
            return $this->fetch('add_banner');
        }
    }

    /**
     * 删除banner
     * @date 2020/8/13 17:55
     */
    public function del_banner() {
        $id = param_check('id');
        $res = db('banner')->where('id', $id)->delete();
        $res ? json_response(1, '删除成功') : json_response(0,'删除失败');
    }
}