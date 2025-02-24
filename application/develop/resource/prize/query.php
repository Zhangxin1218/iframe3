<?php

// 插入权限菜单
$first_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'name'        => '抽奖管理',
    'router'      => '',
    'icon'        => 'layui-icon-gift',
    'status'      => 1,
    'sort'        => 99,
    'create_time' => time()
]);

$second_1_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'parent_id'   => $first_id,
    'name'        => '奖品设置',
    'router'      => "{$mg_module}/Prize/prize_list",
    'module'      => $mg_module,
    'controller'  => 'Prize',
    'action'      => 'prize_list',
    'status'      => 1,
    'sort'        => 1,
    'create_time' => time()
]);

$second_2_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'parent_id'   => $first_id,
    'name'        => '中奖记录',
    'router'      => "{$mg_module}/Prize/prize_log",
    'module'      => $mg_module,
    'controller'  => 'Prize',
    'action'      => 'prize_log',
    'status'      => 1,
    'sort'        => 2,
    'create_time' => time()
]);

$second_1_data = [
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '添加奖品',
        'router'      => "{$mg_module}/Prize/add_prize",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'add_prize',
        'style'       => '',
        'status'      => 1,
        'sort'        => 1,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '编辑奖品',
        'router'      => "{$mg_module}/Prize/edit_prize",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'edit_prize',
        'style'       => 'layui-btn-xs layui-btn-normal',
        'status'      => 1,
        'sort'        => 2,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '删除奖品',
        'router'      => "{$mg_module}/Prize/del_prize",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'del_prize',
        'style'       => 'layui-btn-xs layui-btn-danger',
        'status'      => 1,
        'sort'        => 3,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '上架下架',
        'router'      => "{$mg_module}/Prize/prize_switch",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'prize_switch',
        'style'       => '',
        'status'      => 1,
        'sort'        => 4,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '添加库存',
        'router'      => "{$mg_module}/Prize/add_stock",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'add_stock',
        'style'       => 'layui-btn-xs layui-btn-warm',
        'status'      => 1,
        'sort'        => 5,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '虚拟卡券管理',
        'router'      => "{$mg_module}/Prize/prize_code_list",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'prize_code_list',
        'style'       => 'layui-btn-xs',
        'status'      => 1,
        'sort'        => 6,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '虚拟卡券导入模板',
        'router'      => "{$mg_module}/Prize/prize_code_down_tmp",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'prize_code_down_tmp',
        'style'       => 'layui-btn-normal',
        'status'      => 1,
        'sort'        => 7,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '虚拟卡券导入',
        'router'      => "{$mg_module}/Prize/prize_code_import",
        'module'      => $mg_module,
        'controller'  => 'Prize',
        'action'      => 'prize_code_import',
        'style'       => 'layui-btn-warm',
        'status'      => 1,
        'sort'        => 8,
        'create_time' => time()
    ],
];
db('mg_menu')->insertAll($second_1_data);