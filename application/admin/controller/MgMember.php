<?php


namespace app\admin\controller;


// 管理员管理
class MgMember extends Base
{
    /**
     * 管理员登录
     * @date 2020/8/6 22:02
     */
    public function login() {
        if(request()->isPost()) {
            $username = param_check('username');
            $password = param_check('password');
            // 验证管理员是否存在
            $field = 'a.id, a.group_id, a.username, a.password, a.salt, a.status, b.status as group_status';
            $user = db('mg_member')->alias('a')->join('mg_group b', 'a.group_id=b.id', 'left')
                ->where(['a.mg_module'=>$this->mg_module, 'a.username'=>$username])->field($field)->find();
            if(empty($user)) json_response(0, '用户不存在');
            // 判断密码是否正确
            if(md5($user['salt'].'_'.$password) != $user['password']) {
                json_response(0, '密码错误');
            }
            // 判断管理员状态和管理组状态
            if($user['status'] != 1) json_response(0, '您已被禁止登录');
            if($user['group_status'] != 1) json_response(0, '您所在的管理组已被禁用');
            // 登陆成功
            db('mg_member')
                ->where('id', $user['id'])
                ->update([
                    'last_login_time' => time(),
                    'last_login_ip'   => request()->ip()
                ]);
            session($this->session_key, $user);
            json_response(1, '登录成功');
        }else {
            return $this->fetch('common@index/login');
        }
    }

    /**
     * 退出登录
     * @date 2020/8/6 22:39
     */
    public function logout() {
        session($this->session_key, null);
        $this->redirect('MgMember/login');
    }

    /**
     * 管理员列表
     * @date 2020/8/6 23:57
     */
    public function member_list() {
        $data = db('mg_member a')
            ->where('a.mg_module', $this->mg_module)
            ->join('mg_group b', 'a.group_id=b.id', 'left')
            ->field('a.id, b.group_name, a.nickname, a.username, a.status, a.last_login_time, a.last_login_ip')
            ->order('a.id DESC')
            ->paginate(15);
        $list = $data->all();
        foreach($list as &$item) {
            if($item['last_login_time'] > 0) {
                $item['last_login_time'] = date('Y-m-d H:i:s', $item['last_login_time']);
            }else {
                $item['last_login_time'] = $item['last_login_ip'] = '-';
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $data->render());
        return $this->fetch('common@/mg_member/member_list');
    }

    /**
     * 添加管理员
     * @date 2020/8/6 23:57
     */
    public function add_member() {
        if(request()->isPost()) {
            $data = $_POST;
            if(empty($data['group_id'])) json_response(0, '请选择管理组');
            if(empty($data['username']) || strlen($data['username']) < 5) {
                json_response(0, '用户名至少5个长度');
            }
            // 判断用户名是否存在
            if(db('mg_member')->where('username', $_POST['username'])->value('id')) {
                json_response(0, '用户名已存在');
            }
            $data['mg_module']   = $this->mg_module;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['salt']        = str_random();
            $data['password']    = md5($data['salt'].'_'.$data['password']);
            db('mg_member')->insert($data) ? json_response(1,'保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('mg_group')
                ->where('mg_module', $this->mg_module)
                ->field('id, parent_id, group_name')
                ->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $this->assign('group_list', $group_list);
            return $this->fetch('common@mg_member/add_member');
        }
    }

    /**
     * 编辑管理员
     * @date 2020/8/6 23:57
     */
    public function edit_member() {
        $id = input('id');
        if(request()->isPost()) {
            $data = $_POST;
            if(empty($data['group_id'])) json_response(0, '请选择管理组');
            $data['update_time'] = time();
            db('mg_member')
                ->where(['id'=>$id, 'mg_module'=>$this->mg_module])
                ->update($data) ? json_response(1,'保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('mg_group')
                ->where('mg_module', $this->mg_module)
                ->field('id, parent_id, group_name')
                ->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $data = db('mg_member')->where('id', $id)->field('group_id, nickname, username, status')->find();
            $this->assign('data', $data);
            $this->assign('group_list', $group_list);
            return $this->fetch('common@/mg_member/add_member');
        }
    }

    /**
     * 重置密码
     * @date 2020/8/6 23:57
     */
    public function reset_pwd() {
        $id = input('id');
        if(request()->isPost()) {
            if(strlen($_POST['password']) > 32 || strlen($_POST['password']) < 6) json_response(0, '密码长度6-32位');
            if($_POST['password'] != $_POST['re_password']) json_response(0, '两次密码输入不一致');
            $data = [];
            $data['salt'] = str_random();
            $data['password'] = md5($data['salt'].'_'.$_POST['password']);
            $data['update_time'] = time();
            db('mg_member')->where(['id'=>$id, 'mg_module'=>$this->mg_module])->update($data) ? json_response(1, '重置密码成功') : json_response(0, '重置密码失败');
        }else {
            return $this->fetch('common@/mg_member/reset_pwd');
        }
    }

    /**
     * 修改密码
     * @date 2020/7/6 18:30
     */
    public function edit_pwd() {
        if(request()->isPost()) {
            $old_pwd     = param_check('old_password');
            $password    = param_check('password');
            $re_password = param_check('re_password');
            $user = db('mg_member')
                ->where(['id'=>$this->mg_user['id']])
                ->field('password, salt')
                ->find();
            if(md5($user['salt'].'_'.$old_pwd) != $user['password']) {
                json_response(0, '原密码错误');
            }
            if($password != $re_password) {
                json_response(0, '两次新密码输入不一致');
            }
            $salt = str_random(6);
            $res = db('mg_member')
                ->where('id', $this->mg_user['id'])
                ->update([
                    'salt'        => $salt,
                    'password'    => md5($salt.'_'.$password),
                    'update_time' => time()
                ]);
            if($res) {
                session($this->session_key, null);
                json_response(1, '修改成功');
            }else {
                json_response(0, '修改失败');
            }
        }else {
            return $this->fetch('common@mg_member/edit_pwd');
        }
    }


    /**
     * 删除管理员
     * @date 2020/8/6 23:57
     */
    public function del_member() {
        $id = input('id');
        if(empty($id)) json_response(0, '缺少ID参数');
        if($id == 1) json_response(0, '系统管理员不可删除');
        db('mg_member')->where(['id'=>$id])->delete() ? json_response(1, '删除成功') : json_response(0, '删除失败');
    }
}