<?php


namespace app\api\controller;

// 抽奖接口
class Prize extends Base
{
    private $draw_num       = 0; // 用户初始抽奖次数
    private $share_draw_num = 0; // 用户可分享次数(分享获得1次抽奖机会, 不可分享改为0)
    private $user_id; // 用户ID
    /**
     * 初始化
     * Prize constructor.
     */
    public function __construct() {
        parent::__construct();

        // 获取抽奖次数
        $user_id = $this->check_user_id();
        $this->draw_num = 0;
    }


    /**
     * 获取用户分享次数
     * @return float|int|string
     * @date 2021/4/4 18:37
     */
    private function get_share_num() {
        $share_num = 0;
        if($this->share_draw_num > 0) {
            $share_num = db('prize_share')->where([
                'user_id'   => $this->user_id,
//                'log_date'  => date('Y-m-d')
            ])->count();
        }
        return $share_num;
    }

    /**
     * 获取用户抽奖次数
     * @return float|int|string
     * @date 2021/4/4 18:37
     */
    private function get_draw_num() {
        return db('prize_log')->where([
            'user_id'   => $this->user_id,
//            'log_date'  => date('Y-m-d')
        ])->count();
    }

    /**
     * 1、用户抽奖信息
     * @date 2021/4/4 18:29
     */
    public function index() {
        try {
            // 初始抽奖次数
            $draw_num = $this->draw_num;
            // 分享次数
            $share_num = $this->get_share_num();
            // 用户已抽奖次数
            $user_draw_num = $this->get_draw_num();
            // 奖品列表
            $prize_list = db('prize')
                ->where(['status'=>1])
                ->order('sort ASC, id DESC')
                ->field('id as prize_id, type, name, image')
                ->select();
            json_response(1, 'success', [
                'draw_num'          => $draw_num + $share_num, // 抽奖总次数
                'have_num'          => $draw_num + $share_num - $user_draw_num, // 剩余抽奖次数
                'share_num'         => $share_num, // 已分享次数
                'have_share_num'    => $this->share_draw_num - $share_num, // 剩余分享次数
                'prize_list'        => $prize_list, // 奖品列表
            ]);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 2、抽奖接口
     * @date 2021/4/4 18:29
     */
    public function draw() {
        try {
            $user_id = $this->user_id;
            redis_lock("{$this->redis_key}:draw:{$user_id}", $user_id, function ($user_id, $redis) {
                // 初始抽奖次数
                $draw_num = $this->draw_num;
                // 分享次数
                $share_num = $this->get_share_num();
                // 用户已抽奖次数
                $user_draw_num = $this->get_draw_num();
                if($user_draw_num >= $draw_num + $share_num) {
                    return error('抽奖次数不足');
                }

                // 获取奖品列表
                $prize_list = redis_get("{$this->redis_key}:draw:prize_list", function() {
                    return db('prize')
                        ->where(['status'=>1])
                        ->order('sort ASC, id DESC')
                        ->field('id as prize_id, type, name, image, value, stock, day_stock, win_times, ratio')
                        ->select();
                }, 10);
                if(empty($prize_list)) return error('活动未开放, 请稍后再试~');

                // 判断奖品库存以及其他逻辑
                $date = date('Y-m-d');
                foreach($prize_list as $key=>$item) {
                    // 谢谢参与不判断库存
                    if($item['type'] == 4) {
                        continue;
                    }

                    // 虚拟卡券判断是否导入卡券
                    if($item['type'] == 2 && empty($item['stock'])) {
                        unset($prize_list[$key]); continue;
                    }

                    // 判断redis库存
                    if($item['stock'] > 0) {
                        $redis_stock = $redis->lLen("{$this->redis_key}:PrizeStock:{$item['prize_id']}");
                        if(empty($redis_stock)) {
                            unset($prize_list[$key]); continue;
                        }
                    }

                    // 判断每日库存
                    if($item['day_stock'] > 0) {
                        $today_send = $redis->get("{$this->redis_key}:PrizeSendLog:{$item['prize_id']}_{$date}");
                        if($today_send >= $item['day_stock']) {
                            unset($prize_list[$key]); continue;
                        }
                        // 数据库再验证一次
                        $today_send2 = db('prize_log')->where(['prize_id'=>$item['prize_id'], 'log_date'=>$date])->count();
                        if($today_send2 >= $item['day_stock']) {
                            unset($prize_list[$key]); continue;
                        }
                    }

                    // 判断奖品用户中奖次数
                    if($item['win_times'] > 0) {
                        $user_send = db('prize_log')->where(['user_id'=>$user_id, 'prize_id'=>$item['prize_id']])->value('id');
                        if(!empty($user_send)) {
                            unset($prize_list[$key]); continue;
                        }
                    }
                }
                $prize = draw($prize_list, 'ratio');
                // 实物、虚拟卡券、其他卡券减库存
                if(in_array($prize['type'], [1, 2, 3]) && $prize['stock'] > 0) {
                    $res = $redis->rPop("{$this->redis_key}:PrizeStock:{$prize['prize_id']}");
                    // 减库存失败, 发放谢谢参与
                    if(empty($res)) {
                        return error('当前访问人数过多, 请稍后再试~');
                    }

                    // 虚拟卡券处理
                    if($prize['type'] == 2) {
                        db('prize_code')->where('code', $res)->update([
                            'user_id'       => $user_id,
                            'status'        => 1,
                            'send_time'     => time(),
                            'update_time'   => time()
                        ]);
                        $code = $res;
                    }

                    // 其他卡券处理
                    if($prize['type'] == 3) {
                        // 根据实际情况调取接口
                    }

                }

                // 奖品入库
                $prize_log_id = db('prize_log')->insertGetId([
                    'user_id'       => $user_id,
                    'prize_id'      => $prize['prize_id'],
                    'prize_type'    => $prize['type'],
                    'prize_name'    => $prize['name'],
                    'prize_image'   => $prize['image'],
                    'code'          => isset($code) ? $code : '',
                    'status'        => 0,
                    'log_time'      => time(),
                    'log_date'      => $date
                ]);
                // 中奖记录存储
                if($prize['day_stock'] > 0) {
                    $redis_day_stock_key = "{$this->redis_key}:PrizeSendLog:{$prize['prize_id']}_{$date}";
                    $redis->incr($redis_day_stock_key);
                    $redis->expire($redis_day_stock_key, 86400);
                }
                return success('抽奖成功', [
                    'prize_log_id'  => $prize_log_id,
                    'prize_info'    => [
                        'prize_id'      => $prize['prize_id'],
                        'prize_type'    => $prize['type'],
                        'prize_name'    => $prize['name'],
                        'prize_image'   => $prize['image'],
                        'code'          => isset($code) ? $code : '',
                        'value'         => $prize['value']
                    ]
                ]);

            }, 3);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 3、分享
     * @date 2021/4/4 20:37
     */
    public function share_log() {
        try {
            $user_id = $this->user_id;
            // 判断是否开启分享
            if(empty($this->share_draw_num)) {
                return error('分享未开启');
            }
            redis_lock("{$this->redis_key}:share_log:{$user_id}", $user_id, function($user_id) {
                // 判断分享次数
                $user_share_num = $this->get_share_num();
                if($user_share_num >= $this->share_draw_num) {
                    return error('您已分享过~');
                }
                $res = db('prize_log')->insert([
                    'user_id'   => $user_id,
                    'log_time'  => time(),
                    'log_date'  => date('Y-m-d')
                ]);
                if($res) {
                    return success('分享成功');
                }else {
                    return error('分享失败');
                }
            }, 3);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 4、中奖记录
     * @date 2021/4/4 19:02
     */
    public function prize_log() {
        try {
            $user_id = $this->check_user_id();
            $list = db('prize_log a')
                ->leftJoin('prize b', 'a.prize_id=b.id')
                ->where([
                    ['a.user_id', '=', $user_id],
                    ['a.prize_type', '<>', 4]
                ])
                ->field('a.id as prize_log_id, a.prize_id, a.prize_type, a.prize_name, a.prize_image, a.log_time, a.status, a.name, a.mobile, a.address')
                ->order('a.id DESC')
                ->limit(paginator())
                ->select();
            foreach($list as &$item) {
                $item['log_time'] = date('Y.m.d H:i', $item['log_time']);
            }
            json_response(1, 'success', [
                'list' => $list
            ]);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 5、留资
     * @date 2021/4/4 21:32
     */
    public function set_data() {
        try {
            $user_id        = $this->check_user_id();
            redis_lock("{$this->redis_key}:set_data:{$user_id}", $user_id, function ($user_id, $redis) {
                $prize_log_id   = param_check('prize_log_id');

                $name           = param_check('name');
                $mobile         = param_check('mobile');
                $address        = param_check('address');
                // 判断手机号格式
                if(!preg_match('/1[0-9]{10}/', $mobile)) return error('手机号格式错误');

                // 判断奖品是否存在
                $prize_info = db('prize_log')->where([
                    'id'        => $prize_log_id,
                    'user_id'   => $user_id
                ])->field('prize_type, status')->find();
                if(empty($prize_info)) {
                    return error('奖品不存在');
                }
                if($prize_info['status'] == 1) {
                    return error('奖品已领取');
                }

                $res = db('prize_log')->where('id', $prize_log_id)->update([
                    'name'          => $name,
                    'mobile'        => $mobile,
                    'address'       => $address,
                    'status'        => 1,
                    'receive_time'  => time()
                ]);
                if($res) {
                    return success('留资成功');
                }else {
                    return error('请求过快, 请稍后再试~');
                }
            },3);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }
}