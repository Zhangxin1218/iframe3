<?php


namespace app\__MODULE__\controller;

// 卡片管理
class Card extends Base
{
    public function __construct() {
        parent::__construct();
        $this->form_param = [
            ['type'=>'radio', 'name'=>'type', 'title'=>'奖品类型', 'lay-filter'=>'type_change', 'option'=>[
                ['value'=>1, 'title'=>'普通卡片'],
                ['value'=>2, 'title'=>'稀有卡片']
            ], 'remark'=>'普通卡片不限制库存, 稀有卡片限制库存', 'edit'=>false],
            ['type'=>'text', 'name'=>'name', 'title'=>'卡片名称', 'lay-verify'=>'required'],
            ['type'=>'upload', 'name'=>'image', 'title'=>'卡片图片'],
            ['type'=>'text', 'name'=>'stock', 'title'=>'卡片库存', 'text_type'=>'number', 'remark'=>'设置奖品库存, 0为不限制', 'id'=>'stock'],
            ['type'=>'text', 'name'=>'day_stock', 'title'=>'每日发放数', 'text_type'=>'number', 'remark'=>'设置每日库存, 0为不限制', 'id'=>'day_stock'],
            ['type'=>'radio', 'name'=>'win_times', 'title'=>'中奖次数', 'option'=>[
                ['value'=>1, 'title'=>'只中1次'],
                ['value'=>0, 'title'=>'不限次数']
            ]],
            ['type'=>'text', 'name'=>'ratio', 'title'=>'中奖概率', 'text_type'=>'number', 'remark'=>'中奖概率=当前奖品概率/所有奖品中奖概率之和'],
            ['type'=>'radio', 'name'=>'status', 'title'=>'状态', 'option'=>[
                ['value'=>1, 'title'=>'上架'],
                ['value'=>0, 'title'=>'下架']
            ]],
        ];

        $this->table_param = [
            'cols'  => [
                ['field'=>'id', 'title'=>'ID', 'width'=>'5%', 'fixed'=>'left'],
                ['field'=>'name', 'title'=>'卡片名称', 'width'=>'10%'],
                ['field'=>'image', 'title'=>'卡片图片', 'width'=>'10%'],
                ['field'=>'stock_text', 'title'=>'已发/库存(总)', 'width'=>'12%'],
                ['field'=>'number', 'title'=>'redis库存', 'width'=>'10%'],
                ['field'=>'day_stock_text', 'title'=>'已发/库存(今日)', 'width'=>'14%'],
                ['field'=>'win_times_text', 'title'=>'用户可得次数', 'width'=>'12%'],
                ['field'=>'status_text', 'title'=>'状态', 'width'=>'9%'],
                ['field'=>'ratio', 'title'=>'中奖概率', 'width'=>'10%'],
            ],
            'toolbar' => [
                table_button('Card/add_card')
            ],
            'toolbar_row' => [
                table_button('Card/edit_card', '编辑'),
                table_button('Card/add_stock', '添加库存', 'frame', '', [], 'stock > 0'),
                table_button('Card/del_card', '删除', 'confirm'),
            ],
        ];
    }

    /**
     * 卡片列表
     * @date 2021/3/16 10:32
     */
    public function card_list() {
        if(IS_POST) {
            $db = db('card')
                ->order('id DESC');
            $list = $db->select();
            $all_ratio = array_sum(array_column($list, 'ratio'));
            $redis = redis_instance();
            foreach($list as &$item) {
                // 卡片库存
                $item['number'] = empty($item['stock']) ? '不限' : $redis->lLen("{$this->redis_key}:CardStock:{$item['id']}");
                // 卡片中奖概率
                $item['ratio'] = (round($item['ratio'] / $all_ratio, 2) * 100) . '%';
                // 卡片图片
                $item['image'] = table_img($item['image']);
                // 总库存
                $item['stock_text'] = db('card_log')->where(['card_id'=>$item['id']])->count().'/'.($item['stock'] == 0 ? '不限' : $item['stock']);
                // 中奖次数
                $item['win_times_text'] = $item['win_times'] == 0 ? '不限次数' : '只中1次';
                // 每日库存
                $item['day_stock_text'] = db('card_log')->where(['card_id'=>$item['id'], 'log_date'=>date('Y-m-d')])->count().'/'.($item['day_stock'] == 0 ? '不限' : $item['day_stock']);
                // 上架/下架
                $item['status_text'] = table_switch('Card/card_switch', ['id'=>$item['id']], $item['status'] == 1 ? true : false,'上架|下架');
            }
            admin_response(0, '成功', $list, $db->count());
        }else {
            return $this->render_table();
        }
    }

    /**
     * 添加卡片
     * @date 2021/3/16 15:12
     */
    public function add_card() {
        set_time_limit(0);
        if(IS_POST) {
            $data = $_POST;
            $data['create_time'] = time();
            if($data['type'] == 1) {
                $data['stock'] = $data['day_stock'] = 0;
            }
            $res = db('card')->insertGetId($data);
            if(!empty($data['stock'])) {
                $redis = redis_instance();
                for($i=1; $i<=$data['stock']; $i++) {
                    $redis->lPush("{$this->redis_key}:CardStock:{$res}", 1);
                }
            }
            $res ? json_response(1, '添加成功') : json_response(0, '添加失败');
        }
        return $this->render_form($this->form_param, [], true);
    }

    /**
     * 编辑卡片
     * @date 2021/3/16 18:22
     */
    public function edit_card() {
        $id = param_check('id');
        if(IS_POST) {
            $data = $_POST;
            $data['update_time'] = time();
            if($data['type'] == 1) {
                $data['stock'] = $data['day_stock'] = 0;
            }
            // 卡片类型不可编辑
            unset($data['type']);
            $res = db('card')->where('id', $id)->update($data);
            $res ? json_response(1, '编辑成功') : json_response(0, '编辑失败');
        }
        $data = db('card')->where('id', $id)->find();
        $param = $this->form_param;
        foreach ($param as $k=>$v) {
            if($v['name'] == 'stock') unset($param[$k]);
        }
        return $this->render_form($param, $data);
    }

    /**
     * 删除卡片
     * @date 2021/3/17 17:30
     */
    public function del_card() {
        $id = param_check('id');
        $res = db('card')->where('id', $id)->delete();
        if($res) {
            $redis = redis_instance();
            $redis->del("{$this->redis_key}:CardStock:{$id}");
            json_response(1, '删除成功');
        }else {
            json_response(0, '删除失败');
        }
    }

    /**
     * 上架/下架操作
     * @date 2021/3/17 11:20
     */
    public function card_switch() {
        $id      = param_check('id');
        $checked = param_check('checked', 0);
        $res = db('card')->where('id', $id)->update([
            'status'        => $checked ? 1 : 0,
            'update_time'   => time()
        ]);
        $res ? json_response(1, '修改成功') : json_response(0, '修改失败');
    }

    /**
     * 添加库存
     * @date 2021/4/11 17:36
     */
    public function add_stock() {
        $id = param_check('id');
        if(IS_POST) {
            $stock = param_check('stock');
            $res = db('card')->where('id', $id)->update([
                'stock'         => ['INC', $stock],
                'update_time'   => time()
            ]);
            if($res) {
                $redis = redis_instance();
                for($i=1; $i<=$stock; $i++) {
                    $redis->lPush("{$this->redis_key}:CardStock:{$id}", 1);
                }
                json_response(1, '添加库存成功');
            }else {
                json_response(0, '添加库存失败');
            }
        }
        return $this->render_form([
            ['type'=>'text', 'name'=>'stock', 'title'=>'添加库存', 'text_type'=>'number', 'remark'=>'请输入添加库存数量']
        ]);
    }

    /**
     * 抽卡记录
     * @date 2021/4/11 17:38
     */
    public function card_log() {
        if(IS_POST) {
            $db = db('card_log a')
                ->leftJoin('user b', 'a.user_id=b.id')
                ->field('a.*, b.nickname, b.avatar')
                ->order('a.id DESC');
            $list = $db->limit(paginator())->select();
            foreach ($list as &$item) {
                $item['avatar'] = table_img($item['avatar']);
                $item['card_image'] = table_img($item['card_image']);
                $item['log_time'] = date('Y-m-d H:i:s', $item['log_time']);
            }
            admin_response(0, '成功', $list, $db->count());
        }else {
            return $this->render_table([
                'page'  => true,
                'cols'  => [
                    ['field'=>'id', 'title'=>'ID', 'width'=>'10%', 'fixed'=>'left'],
                    ['field'=>'nickname', 'title'=>'用户昵称', 'width'=>'20%'],
                    ['field'=>'avatar', 'title'=>'用户头像', 'width'=>'15%'],
                    ['field'=>'card_name', 'title'=>'卡片名称', 'width'=>'20%'],
                    ['field'=>'card_image', 'title'=>'卡片图片', 'width'=>'15%'],
                    ['field'=>'log_time', 'title'=>'中奖时间', 'width'=>'20%'],
                ]
            ]);
        }
    }
}