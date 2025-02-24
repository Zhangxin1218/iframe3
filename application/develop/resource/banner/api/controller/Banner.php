<?php


namespace app\api\controller;

// banner模块
class Banner extends Base
{
    /**
     * banner列表
     * @date 2020/8/13 20:58
     */
    public function get_banner() {
        try {
            $position = param_check('position', false, '缺少图片位置');
            $get_one  = param_check('get_one ', 0); // 是否只取一张, 用于页面某位置的单张banner
            $where    = [
                'status'   => 1,
                'position' => $position
            ];
            $hook = db('banner')
                ->where($where)
                ->order('sort ASC, id DESC')
                ->field('image, type, url');
            $info = $get_one == 1 ? $hook->find() : $hook->select();
            if(!empty($info)) {
                json_response(1, 'success', $info);
            }else {
                json_response(0, '图片不存在');
            }
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }
}