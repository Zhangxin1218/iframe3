<?php

// 插入权限菜单
$first_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'name'        => '集卡管理',
    'router'      => '',
    'icon'        => 'layui-icon-gift',
    'status'      => 1,
    'sort'        => 99,
    'create_time' => time()
]);
$second_1_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'parent_id'   => $first_id,
    'name'        => '卡片设置',
    'router'      => "{$mg_module}/Card/card_list",
    'module'      => $mg_module,
    'controller'  => 'Card',
    'action'      => 'card_list',
    'status'      => 1,
    'sort'        => 1,
    'create_time' => time()
]);
$second_2_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'parent_id'   => $first_id,
    'name'        => '抽卡记录',
    'router'      => "{$mg_module}/Card/card_log",
    'module'      => $mg_module,
    'controller'  => 'Card',
    'action'      => 'card_log',
    'status'      => 1,
    'sort'        => 2,
    'create_time' => time()
]);

$second_1_data = [
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '添加卡片',
        'router'      => "{$mg_module}/Card/add_card",
        'module'      => $mg_module,
        'controller'  => 'Card',
        'action'      => 'add_card',
        'style'       => '',
        'status'      => 1,
        'sort'        => 1,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '编辑卡片',
        'router'      => "{$mg_module}/Card/edit_card",
        'module'      => $mg_module,
        'controller'  => 'Card',
        'action'      => 'edit_card',
        'style'       => 'layui-btn-xs layui-btn-normal',
        'status'      => 1,
        'sort'        => 2,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '删除卡片',
        'router'      => "{$mg_module}/Card/del_card",
        'module'      => $mg_module,
        'controller'  => 'Card',
        'action'      => 'del_card',
        'style'       => 'layui-btn-xs layui-btn-danger',
        'status'      => 1,
        'sort'        => 3,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '添加库存',
        'router'      => "{$mg_module}/Card/add_stock",
        'module'      => $mg_module,
        'controller'  => 'Card',
        'action'      => 'add_stock',
        'style'       => 'layui-btn-xs layui-btn-warm',
        'status'      => 1,
        'sort'        => 4,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '上架/下架',
        'router'      => "{$mg_module}/Card/card_switch",
        'module'      => $mg_module,
        'controller'  => 'Card',
        'action'      => 'card_switch',
        'style'       => '',
        'status'      => 1,
        'sort'        => 5,
        'create_time' => time()
    ]
];
db('mg_menu')->insertAll($second_1_data);