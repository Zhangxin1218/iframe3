<?php


namespace app\admin\controller;


// 管理组
class MgGroup extends Base
{
    /**
     * 管理组列表
     * @date 2020/8/6 23:22
     */
    public function group_list() {
        $list = db('mg_group')
            ->where('mg_module', $this->mg_module)
            ->field('id, parent_id, group_name, access, status, update_time')
            ->select();
        $list = arr_tree($list);
        foreach ($list as &$item) {
            $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            $item['group_people'] = db('mg_member')
                ->where('group_id', $item['id'])
                ->count();
        }
        $this->assign('list', $list);
        return $this->fetch('common@mg_group/group_list');
    }

    /**
     * 添加管理组
     * @date 2020/8/6 23:25
     */
    public function add_group() {
        if(request()->post()) {
            $data = $_POST;
            $data['mg_module'] = $this->mg_module;
            $data['access'] = implode(',', $data['access']);
            $data['create_time'] = time();
            $data['update_time'] = time();
            db('mg_group')->insert($data) ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('mg_group')
                ->where('mg_module', $this->mg_module)
                ->field('id, parent_id, group_name')
                ->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $access_list = db('mg_menu')
                ->where('mg_module', $this->mg_module)
                ->field('id, parent_id, name')
                ->order('sort ASC, id DESC')
                ->select();
            $access_list = arr_tree($access_list, true);
            $this->assign('group_list', $group_list);
            $this->assign('access_list', $access_list);
            return $this->fetch('common@mg_group/add_group');
        }
    }

    /**
     * 编辑管理组
     * @date 2020/8/6 23:26
     */
    public function edit_group() {
        $id = input('id');
        if(request()->isPost()) {
            $data = $_POST;
            $data['access'] = implode(',', $data['access']);
            $data['update_time'] = time();
            $res = db('mg_group')
                ->where(['id'=>$id, 'mg_module'=>$this->mg_module])
                ->update($data);
            $res ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('mg_group')
                ->where(['mg_module'=>$this->mg_module])
                ->field('id, parent_id, group_name')
                ->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $access_list = db('mg_menu')
                ->where('mg_module', $this->mg_module)
                ->field('id, parent_id, name')
                ->order('sort ASC, id DESC')
                ->select();
            $access_list = arr_tree($access_list, true);
            $data = db('mg_group')
                ->where(['id'=>$id, 'mg_module'=>$this->mg_module])
                ->field('parent_id, group_name, access, status')
                ->find();
            $data['access'] = explode(',', $data['access']);
            $this->assign('group_list', $group_list);
            $this->assign('access_list', $access_list);
            $this->assign('data', $data);
            return $this->fetch('common@mg_group/add_group');
        }
    }

    /**
     * 删除管理组
     * @date 2020/8/6 23:28
     */
    public function del_group() {
        $id = input('id');
        if($id == 1) json_response(0, '系统管理组不可删除');
        $have_children = db('mg_member')
            ->where('group_id', $id)
            ->value('id');
        if($have_children) json_response(0, '管理组存在管理员不可删除');
        $res = db('mg_group')
            ->where(['id'=>$id, 'mg_module'=>$this->mg_module])
            ->delete();
        $res ? json_response(1, '删除成功') : json_response(0,'删除失败');
    }
}