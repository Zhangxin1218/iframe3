<?php


namespace app\__MODULE__\controller;

// 用户模块
class User extends Base
{

    /**
     * 用户列表
     * @date 2020/8/15 14:09
     */
    public function user_list() {
        $where = [];
        if(!empty($_GET['nickname'])) $where[] = ['nickname', 'like', "%{$_GET['nickname']}%"];
        if(!empty($_GET['mobile'])) $where[] = ['mobile', '=', $_GET['mobile']];
        $data = db('user')
            ->where($where)
            ->order('id DESC')
            ->field('id, avatar, nickname, create_time')
            ->paginate(15, false, ['query'=>$_GET]);
        $this->assign('list', $data->all());
        $this->assign('page', $data->render());
        $this->assign('where', $_GET);
        return $this->fetch();
    }
}