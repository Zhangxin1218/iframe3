<?php


namespace app\__MODULE__\controller;

// 奖品管理
use think\cache\driver\Redis;

class Prize extends Base
{
    /**
     * 构造方法
     * Prize constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->form_param = [
            ['type'=>'radio', 'name'=>'type', 'title'=>'奖品类型', 'lay-filter'=>'type_change', 'option'=>[
                ['value'=>1, 'title'=>'实物'],
                ['value'=>2, 'title'=>'虚拟卡券'],
                ['value'=>3, 'title'=>'其他卡券'],
                ['value'=>4, 'title'=>'谢谢参与']
            ], 'edit'=>false],
            ['type'=>'text', 'name'=>'value', 'title'=>'卡券信息', 'hidden'=>true, 'id'=>'value'],
            ['type'=>'text', 'name'=>'name', 'title'=>'奖品名称', 'lay-verify'=>'required'],
            ['type'=>'upload', 'name'=>'image', 'title'=>'奖品图片'],
            ['type'=>'text', 'name'=>'stock', 'title'=>'奖品库存', 'text_type'=>'number', 'id'=>'stock', 'remark'=>'0为不限制'],
            ['type'=>'text', 'name'=>'day_stock', 'title'=>'每日发放数', 'text_type'=>'number', 'remark'=>'0为不限制', 'id'=>'day_stock'],
            ['type'=>'radio', 'name'=>'win_times', 'title'=>'中奖次数', 'option'=>[
                ['value'=>1, 'title'=>'只中1次'],
                ['value'=>0, 'title'=>'不限次数']
            ], 'id'=>'win_times'],
            ['type'=>'text', 'name'=>'ratio', 'title'=>'中奖概率', 'text_type'=>'number', 'remark'=>'中奖概率=当前奖品概率/所有奖品中奖概率之和', 'id'=>'ratio'],
            ['type'=>'radio', 'name'=>'status', 'title'=>'状态', 'option'=>[
                ['value'=>1, 'title'=>'上架'],
                ['value'=>0, 'title'=>'下架']
            ]],
            ['type'=>'text', 'name'=>'sort', 'title'=>'排序', 'text_type'=>'number', 'remark'=>'序号越小, 排序越靠前']
        ];
    }

    /**
     * 奖品列表
     * @date 2021/3/29 16:40
     */
    public function prize_list() {
        if(IS_POST) {
            $data = db('prize')
                ->order('sort ASC, id DESC')
                ->select();
            $redis = redis_instance();
            $type_map = [1=>'实物', 2=>'虚拟卡券', 3=>'其他卡券', 4=>'谢谢参与'];
            foreach($data as &$item) {
                // 奖品类型
                $item['type_text'] = $type_map[$item['type']];
                // 奖品图片
                $item['image'] = table_img($item['image']);
                // 奖品状态
                $item['status_switch'] = table_switch('Prize/prize_switch', ['id'=>$item['id']], $item['status'] == 1 ? true : false);
                // 奖品redis库存
                $item['redis_stock'] = $redis->lLen("{$this->redis_key}:PrizeStock:{$item['id']}");
                // 中奖次数
                $item['win_times_text'] = $item['win_times'] == 1 ? '只中1次' : '不限次数';
                // 奖品总库存
                $item['stock_text'] = $item['stock'] > 0 ? $item['stock'] : '不限';
                // 虚拟卡券库存为0代表没有导入券码，而不是不限
                if($item['type'] == 2) $item['stock_text'] = $item['stock'];
                // 每日库存
                $item['day_stock_text'] = $item['day_stock'] > 0 ? $item['day_stock'] : '不限';
                // 今日发放数
                $item['today_send_num'] = db('prize_log')->where(['prize_id'=>$item['id'], 'log_date'=>date('Y-m-d')])->count();
                // 累计发放数
                $item['send_num'] = db('prize_log')->where('prize_id', $item['id'])->count();

            }
            admin_response(0, 'success', $data);
        }
        return $this->render_table([
            'cols'  => [
                ['field'=>'id', 'title'=>'ID', 'width'=>'60', 'fixed'=>'left'],
                ['field'=>'type_text', 'title'=>'奖品类型', 'width'=>'150'],
                ['field'=>'name', 'title'=>'奖品名称', 'width'=>'150'],
                ['field'=>'image', 'title'=>'奖品图片', 'width'=>'100'],
                ['field'=>'status_switch', 'title'=>'状态', 'width'=>'100'],
                ['field'=>'stock_text', 'title'=>'累计库存', 'width'=>'100'],
                ['field'=>'redis_stock', 'title'=>'剩余库存', 'width'=>'100'],
                ['field'=>'day_stock_text', 'title'=>'每日限制', 'width'=>'100'],
                ['field'=>'send_num', 'title'=>'累计发出', 'width'=>'100'],
                ['field'=>'today_send_num', 'title'=>'今日发出', 'width'=>'100'],
                ['field'=>'win_times_text', 'title'=>'中奖限制', 'width'=>'100'],
                ['field'=>'sort', 'title'=>'排序', 'width'=>'100'],
            ],
            'toolbar'   => [
                table_button('Prize/add_prize')
            ],
            'toolbar_row' => [
                table_button('Prize/edit_prize', '编辑'),
                table_button('Prize/add_stock', '添加库存', 'frame', '', [], 'type != 2 && d.stock > 0'),
                table_button('Prize/prize2_list', '虚拟卡券', 'frame', '', [], 'type == 2'),
                table_button('Prize/del_prize', '删除', 'confirm')
            ]
        ]);
    }

    /**
     * 添加奖品
     * @date 2021/3/23 10:35
     */
    public function add_prize() {
        if(IS_POST) {
            $data = $_POST;
            $data['create_time'] = time();
            $res = db('prize')->insertGetId($data);
            // redis队列存入
            if(!empty($data['stock'])) {
                $redis = redis_instance();
                $redis->multi(\Redis::PIPELINE);
                for($i=1; $i<=$data['stock']; $i++) {
                    $redis->lPush("{$this->redis_key}:PrizeStock:{$res}", 1);
                }
                $redis->exec();
            }
            if($data['type2'] == 2) {
                $data['stock'] = $data['day_stock'] = $data['ratio'] = 0;
                $data['win_times'] = 1;
            }
            $res ? json_response(1, '添加成功') : json_response(0, '添加失败');
        }
        return $this->render_form([], [], true);
    }

    /**
     * 编辑奖品
     * @date 2021/3/23 11:21
     */
    public function edit_prize() {
        $prize_id = param_check('id');
        if(IS_POST) {
            $data = $_POST;
            // 奖品类型/库存不可编辑
            unset($data['type'], $data['stock']);
            $data['update_time'] = time();
            $res = db('prize')->where('id', $prize_id)->update($data);
            $res ? json_response(1, '编辑成功') : json_response(0, '编辑失败');
        }
        $data = db('prize')->where('id', $prize_id)->find();
        // 奖品库存不可修改
        foreach ($this->form_param as $k=>$v) {
            if($v['name'] == 'stock') {
                unset($this->form_param[$k]);break;
            }
        }
        return $this->render_form([], $data, 'add_prize');
    }

    /**
     * 删除奖品
     * @date 2021/3/23 11:22
     */
    public function del_prize() {
        $prize_id = param_check('id');
        $res = db('prize')->where('id', $prize_id)->delete();
        if($res) {
            // 同步删除redis库存
            $redis = redis_instance();
            $redis->del("{$this->redis_key}:PrizeStock:{$prize_id}");

            // 删除虚拟卡券券码
//            db('prize_code')->where('prize_id', $prize_id)->delete();

            json_response(1, '删除成功');
        }else {
            json_response(0, '删除失败');
        }
    }

    /**
     * 添加库存
     * @date 2021/4/11 17:36
     */
    public function add_stock() {
        $id = param_check('id');
        if(IS_POST) {
            $stock = param_check('stock');

            // 虚拟卡券或者不限数量奖品不可以添加库存
            $prize_info = db('prize')->where('id', $id)->find();
            if($prize_info['type'] != 2 && $prize_info['stock'] > 0) {
                // 修改数据库
                $res = db('prize')->where('id', $id)->update([
                    'stock'         => ['INC', $stock],
                    'update_time'   => time()
                ]);
                // 存入redis
                if($res) {
                    $redis = redis_instance();
                    $redis->multi(\Redis::PIPELINE);
                    for($i=1; $i<=$stock; $i++) {
                        $redis->lPush("{$this->redis_key}:PrizeStock:{$id}", 1);
                    }
                    $redis->exec();
                    json_response(1, '添加库存成功');
                }else {
                    json_response(0, '添加库存失败');
                }
            }else {
                json_response(0, '非法操作');
            }
        }
        return $this->render_form([
            ['type'=>'text', 'name'=>'stock', 'title'=>'添加库存', 'text_type'=>'number', 'remark'=>'请输入添加库存数量']
        ]);
    }

    /**
     * 奖品上架下架
     * @date 2021/4/2 10:58
     */
    public function prize_switch() {
        $id      = param_check('id');
        $checked = param_check('checked', 0);
        $res = db('prize')->where('id', $id)->update([
            'status'        => $checked ? 1 : 0,
            'update_time'   => time()
        ]);
        $res ? json_response(1, '修改成功') : json_response(0, '修改失败');
    }

    /**
     * 虚拟卡券列表
     * @date 2021/4/2 11:15
     */
    public function prize_code_list() {
        $prize_id = param_check('id');
        if(IS_POST) {
            $db = db('prize_code')->where('prize_id', $prize_id)->order('id DESC');
            $list = $db->limit(paginator())->select();
            $prize_name = db('prize')->where('id', $prize_id)->value('name');
            foreach($list as &$item) {
                $item['prize_name']  = $prize_name;
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['status_text'] = $item['status'] == 1 ? '已发出' : '未发出';
                $item['send_time']   = $item['status'] == 1 ? date('Y-m-d H:i:s', $item['send_time']) : '-';

            }
            admin_response(0, 'success', $list, $db->count());
        }

        return $this->render_table([
            'page'  => true,
            'frame' => true,
            'query' => '?prize_id='.$prize_id,
            'cols'  => [
                ['field'=>'id', 'title'=>'ID', 'width'=>'8%', 'fixed'=>'left'],
                ['field'=>'prize_name', 'title'=>'奖品名称', 'width'=>'17%'],
                ['field'=>'code', 'title'=>'券码', 'width'=>'30%'],
                ['field'=>'status_text', 'title'=>'状态', 'width'=>'15%'],
                ['field'=>'send_time', 'title'=>'发出时间', 'width'=>'15%'],
                ['field'=>'create_time', 'title'=>'创建时间', 'width'=>'15%'],
            ],
            'toolbar'   => [
                table_button('Prize/prize_code_down_tmp', '下载导入模板', 'url'),
                table_button('Prize/prize_code_import', '导入虚拟卡券', 'upload', '.csv'),
            ]
        ]);
    }

    /**
     * 虚拟卡券导入模板下载
     * @date 2021/4/2 11:32
     */
    public function prize_code_down_tmp() {
        header('Location: https://pilihuo.oss-cn-hangzhou.aliyuncs.com/resource/csv_template/%E8%99%9A%E6%8B%9F%E5%8D%A1%E5%88%B8%E5%AF%BC%E5%85%A5%E6%A8%A1%E6%9D%BF.csv');
    }

    /**
     * 导入虚拟卡券
     * @date 2021/4/4 18:12
     */
    public function prize_code_import() {
        set_time_limit(0);
        $prize_id = param_check('prize_id');
        if(empty($_FILES['file'])) json_response(0, '请选择文件');
        $redis = redis_instance();
        $data = big_csv_to_array($_FILES['file']['tmp_name']);
        $num = 0;
        foreach ($data as $item) {
            if(!empty($item[0])) {
                $code = trim($item[0]);
                $redis->lPush("{$this->redis_key}:PrizeStock:{$prize_id}", $code);
                db('prize_code')->insert([
                    'prize_id'    => $prize_id,
                    'code'        => $code,
                    'create_time' => time()
                ]);
                $num += 1;
            }
        }
        db('prize')->where('id', $prize_id)->update([
            'stock'         => ['INC', $num],
            'update_time'   => time()
        ]);
        json_response(1,"成功导入{$num}条数据");
    }

    /**
     * 中奖记录
     * @date 2021/4/4 21:49
     */
    public function prize_log() {
        $map = [];
        if(!empty($_REQUEST['prize_id'])) $map[] = ['a.prize_id', '=', $_REQUEST['prize_id']];
        if(strlen($_REQUEST['status'])) $map[] = ['a.status', '=', $_REQUEST['status']];
        if(!empty($_REQUEST['name'])) $map[] = ['a.name', '=', $_REQUEST['name']];
        if(!empty($_REQUEST['mobile'])) $map[] = ['a.mobile', '=', $_REQUEST['mobile']];
        if(IS_POST) {
            $db = db('prize_log a')
                ->where($map)
                ->leftJoin('prize b', 'a.prize_id=b.id')
                ->leftJoin('user c', 'a.user_id=c.id')
                ->field('a.id, a.status, a.name, a.mobile, a.address, a.log_time, a.prize_type, a.prize_name, a.prize_image, c.nickname, c.avatar');
            $list = $db->order('a.id DESC')->limit(paginator())->select();
            foreach ($list as &$item) {
                if($item['prize_type'] == 1) $item['type_text'] = '实物';
                if($item['prize_type'] == 2) $item['type_text'] = '虚拟卡券';
                if($item['prize_type'] == 3) $item['type_text'] = '其他卡券';
                if($item['prize_type'] == 4) $item['type_text'] = '谢谢参与';
                $item['avatar']     = table_img($item['avatar']);
                $item['log_time']   = date('Y-m-d H:i:s', $item['log_time']);
                $item['status_text'] = $item['status'] == 1 ? '已领取' : '未领取';
            }
            admin_response(0, 'success', $list, $db->count());
        }
        if(IS_EXCEL) {
            $db = db('prize_log a')
                ->where($map)
                ->leftJoin('prize b', 'a.prize_id=b.id')
                ->leftJoin('user c', 'a.user_id=c.id')
                ->field('a.id, a.status, a.name, a.mobile, a.address, a.log_time, a.prize_type, a.prize_name, a.prize_image, c.nickname, c.avatar')
                ->order('a.id ASC');
            big_array_to_csv($db, '中奖记录导出', [
                'prize_type'    => ['奖品类型', function($row) {
                    if($row['prize_type'] == 1) return '实物';
                    if($row['prize_type'] == 2) return '虚拟卡券';
                    if($row['prize_type'] == 3) return '其他卡券';
                    if($row['prize_type'] == 4) return'谢谢参与';
                    return '';
                }],
                'prize_name'    => '奖品名称',
                'status'        => ['状态', function($row) {
                    return $row['status'] == 1 ? '已领取' : '未领取';
                }],
                'name'          => '姓名',
                'mobile'        => '电话',
                'address'       => '收货地址',
                'log_time'      => ['中奖时间', 'datetime']
            ]);


        }
        return $this->render_table([
            'page'   => true,
            'export' => true,
            'cols'   => [
                ['field'=>'id', 'title'=>'ID', 'width'=>'6%', 'fixed'=>'left'],
                ['field'=>'avatar', 'title'=>'头像', 'width'=>'6%'],
                ['field'=>'nickname', 'title'=>'昵称', 'width'=>'10%'],
                ['field'=>'type_text', 'title'=>'奖品类型', 'width'=>'10%'],
                ['field'=>'prize_name', 'title'=>'奖品名称', 'width'=>'10%'],
                ['field'=>'status_text', 'title'=>'状态', 'width'=>'10%'],
                ['field'=>'name', 'title'=>'姓名', 'width'=>'10%'],
                ['field'=>'mobile', 'title'=>'电话', 'width'=>'10%'],
                ['field'=>'address', 'title'=>'收货地址', 'width'=>'10%'],
                ['field'=>'code', 'title'=>'卡券编号', 'width'=>'10%'],
                ['field'=>'value', 'title'=>'附加信息', 'width'=>'10%'],
                ['field'=>'log_time', 'title'=>'中奖时间', 'width'=>'15%'],
            ],
            'where' => [
                'prize_id' => ['type'=>'select', 'title'=>'奖品名称', 'option'=>db('prize')->order('sort ASC, id DESC')->field('id as value, name as title')->select()],
                'status' => ['type'=>'select', 'title'=>'状态', 'option'=>[
                    ['value'=>1, 'title'=>'已领取'],
                    ['value'=>0, 'title'=>'未领取']
                ]],
                'name' => ['type'=>'input', 'title'=>'留资姓名'],
                'mobile' => ['type'=>'input', 'title'=>'留资电话']
            ]
        ]);
    }
}