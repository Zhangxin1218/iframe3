<?php


namespace app\api\controller;

// 抽卡模块
class Card extends Base
{
    private $draw_num       = 0; // 用户初始抽奖次数
    private $share_draw_num = 0; // 用户可分享次数(分享获得1次抽奖机会, 不可分享改为0)

    /**
     * 获取用户分享次数
     * @param $user_id
     * @return float|int|string
     * @date 2021/4/4 18:37
     */
    private function get_share_num($user_id) {
        $share_num = 0;
        if($this->share_draw_num > 0) {
            $share_num = db('card_share')->where([
                'user_id'   => $user_id,
                'log_date'  => date('Y-m-d')
            ])->count();
        }
        return $share_num;
    }

    /**
     * 获取用户抽奖次数
     * @param $user_id
     * @return float|int|string
     * @date 2021/4/4 18:37
     */
    private function get_draw_num($user_id) {
        return db('card_log')->where([
            'user_id'   => $user_id,
            'log_date'  => date('Y-m-d')
        ])->count();
    }

    /**
     * 1、用户抽卡信息
     * @date 2021/4/4 18:29
     */
    public function index() {
        try {
            $user_id = $this->check_user_id();
            // 初始抽奖次数
            $draw_num = $this->draw_num;
            // 分享次数
            $share_num = $this->get_share_num($user_id);
            // 用户已抽奖次数
            $user_draw_num = $this->get_draw_num($user_id);
            json_response(1, 'success', [
                'draw_num'          => $draw_num + $share_num, // 抽奖总次数
                'have_num'          => $draw_num + $share_num - $user_draw_num, // 剩余抽奖次数
                'share_num'         => $share_num, // 已分享次数
                'have_share_num'    => $this->share_draw_num - $share_num // 剩余分享次数
            ]);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 2、抽卡接口
     * @date 2021/3/19 11:49
     */
    public function draw() {
        try {
            $user_id = $this->check_user_id();
            redis_lock("{$this->redis_key}:draw:{$user_id}", $user_id, function($user_id, $redis) {
                // 初始抽奖次数
                $draw_num = $this->draw_num;
                // 分享次数
                $share_num = $this->get_share_num($user_id);
                // 用户已抽奖次数
                $user_draw_num = $this->get_draw_num($user_id);
                if($user_draw_num >= $draw_num + $share_num) {
                    return error('抽奖次数不足');
                }

                // 获取卡片列表
                $card_list = redis_get("{$this->redis_key}:draw_cardList", function() {
                    return db('card')->where('status', 1)->order('ratio ASC')->field('id as card_id, type, name, image, stock, day_stock, win_times, ratio')->select();
                }, 5);
                if(empty($card_list)) return error('活动未开放, 请稍后再试~');


                // 判断卡片库存以及其他逻辑
                $date = date('Y-m-d');
                foreach($card_list as $key=>$item) {
                    // 普通卡不判断库存
                    if($item['type'] == 1) {
                        continue;
                    }

                    // 判断卡片总库存
                    if($item['stock'] > 0) {
                        // 判断卡片库存
                        $stock = $redis->lLen("{$this->redis_key}:CardStock:{$item['card_id']}");
                        // 总库存不足, 从抽奖列表移除
                        if(empty($stock)) {
                            unset($card_list[$key]); continue;
                        }
                    }

                    // 判断卡片每日库存
                    if($item['day_stock'] > 0) {
                        $today_send = $redis->get("{$this->redis_key}:CardSendLog:{$item['card_id']}_{$date}");
                        if($today_send >= $item['day_stock']) {
                            unset($card_list[$key]); continue;
                        }
                        // 数据库再验证一次
                        $today_send2 = db('card_log')->where(['card_id'=>$item['card_id'], 'log_date'=>$date])->count();
                        if($today_send2 >= $item['day_stock']) {
                            unset($card_list[$key]); continue;
                        }
                    }

                    // 判断奖品用户抽中次数
                    if($item['win_times'] > 0) {
                        $user_send = db('card_log')->where(['user_id'=>$user_id, 'card_id'=>$item['card_id']])->count();
                        if(!empty($user_send)) {
                            unset($card_list[$key]); continue;
                        }
                    }
                }
                $card = draw($card_list, 'ratio');
                // redis队列减库存
                if($card['stock'] > 0) {
                    $res = $redis->rPop("{$this->redis_key}:CardStock:{$card['card_id']}");
                    // 减库存失败, 发放无价值卡片
                    if(empty($res)) {
                        return error('当前访问人数过多, 请稍后再试~');
                    }
                }
                // 奖品入库
                db('card_log')->insertGetId([
                    'user_id'       => $user_id,
                    'card_id'       => $card['card_id'],
                    'card_type'     => $card['type'],
                    'card_name'     => $card['name'],
                    'card_image'    => $card['image'],
                    'status'        => 0,
                    'log_time'      => time(),
                    'log_date'      => $date
                ]);
                // 中奖记录存储
                if($card['day_stock'] > 0) {
                    $redis_day_stock_key = "{$this->redis_key}:CardSendLog:{$card['card_id']}_{$date}";
                    $redis->incr($redis_day_stock_key);
                    $redis->expire($redis_day_stock_key, 86400);
                }
                return success('抽卡成功', [
                    'card_id'       => $card['card_id'],
                    'card_type'     => $card['type'],
                    'card_name'     => $card['name'],
                    'card_image'    => $card['image']
                ]);
            });
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
            $user_id = $this->check_user_id();
            // 判断是否开启分享
            if(empty($this->share_draw_num)) {
                return error('分享未开启');
            }
            redis_lock("{$this->redis_key}:share_log:{$user_id}", $user_id, function($user_id) {
                // 判断分享次数
                $user_share_num = db('card_share')->where([
                    'user_id'   => $user_id,
                    'log_date'  => date('Y-m-d')
                ])->count();
                if($user_share_num >= $this->share_draw_num) {
                    return error('今日已分享');
                }
                $res = db('card_share')->insert([
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
     * 我的卡片
     * @date 2021/4/9 17:05
     */
    public function card_log() {
        try {
            $user_id = $this->check_user_id();
            $card_list = db('card')
                ->where('type', 2)
                ->field('id as card_id, name, image2 as image')
                ->select();
            foreach ($card_list as &$item) {
                $item['number'] = db('card_log')->where([
                    'user_id' => $user_id,
                    'card_id' => $item['card_id'],
                    'status'  => 0
                ])->count();
            }
            json_response(1, 'success', $card_list);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }
}