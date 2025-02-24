<?php
// 插入管理组
$group_id = db('mg_group')->insertGetId([
    'mg_module'   => $mg_module,
    'group_name'  => '超级管理员',
    'access'      => '*',
    'status'      => 1,
    'create_time' => time(),
    'update_time' => time()
]);

// 只有admin模块插入管理员, 其他模块管理员是总后台创建的
if($mg_module == 'admin') {
    // 插入管理员
    $salt = str_random(6);
    db('mg_member')->insert([
        'mg_module'   => $mg_module,
        'group_id'    => $group_id,
        'nickname'    => '超级管理员',
        'username'    => $mg_module,
        'password'    => md5($salt.'_123456'),
        'salt'        => $salt,
        'status'      => 1,
        'create_time' => time()
    ]);
}

// 插入权限菜单
$first_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'name'        => '权限管理',
    'router'      => '',
    'icon'        => 'layui-icon-snowflake',
    'status'      => 1,
    'sort'        => 99,
    'create_time' => time()
]);
$second_1_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'parent_id'   => $first_id,
    'name'        => '管理组',
    'router'      => "{$mg_module}/MgGroup/group_list",
    'module'      => $mg_module,
    'controller'  => 'MgGroup',
    'action'      => 'group_list',
    'status'      => 1,
    'sort'        => 1,
    'create_time' => time()
]);
$second_2_id = db('mg_menu')->insertGetId([
    'mg_module'   => $mg_module,
    'parent_id'   => $first_id,
    'name'        => '管理员',
    'router'      => "{$mg_module}/MgMember/member_list",
    'module'      => $mg_module,
    'controller'  => 'MgMember',
    'action'      => 'member_list',
    'status'      => 1,
    'sort'        => 2,
    'create_time' => time()
]);
$second_1_data = [
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '添加管理组',
        'router'      => "{$mg_module}/MgGroup/add_group",
        'module'      => $mg_module,
        'controller'  => 'MgGroup',
        'action'      => 'add_group',
        'style'       => '',
        'status'      => 1,
        'sort'        => 1,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '编辑管理组',
        'router'      => "{$mg_module}/MgGroup/edit_group",
        'module'      => $mg_module,
        'controller'  => 'MgGroup',
        'action'      => 'edit_group',
        'style'       => 'layui-btn-xs layui-btn-normal',
        'status'      => 1,
        'sort'        => 2,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_1_id,
        'name'        => '删除管理组',
        'router'      => "{$mg_module}/MgGroup/del_group",
        'module'      => $mg_module,
        'controller'  => 'MgGroup',
        'action'      => 'del_group',
        'style'       => 'layui-btn-xs layui-btn-danger',
        'status'      => 1,
        'sort'        => 3,
        'create_time' => time()
    ]
];
db('mg_menu')->insertAll($second_1_data);
$second_2_data = [
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_2_id,
        'name'        => '添加管理员',
        'router'      => "{$mg_module}/MgMember/add_member",
        'module'      => $mg_module,
        'controller'  => 'MgMember',
        'action'      => 'add_member',
        'style'       => '',
        'status'      => 1,
        'sort'        => 1,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_2_id,
        'name'        => '编辑管理员',
        'router'      => "{$mg_module}/MgMember/edit_member",
        'module'      => $mg_module,
        'controller'  => 'MgMember',
        'action'      => 'edit_member',
        'style'       => 'layui-btn-xs layui-btn-normal',
        'status'      => 1,
        'sort'        => 2,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_2_id,
        'name'        => '重置密码',
        'router'      => "{$mg_module}/MgMember/reset_pwd",
        'module'      => $mg_module,
        'controller'  => 'MgMember',
        'action'      => 'reset_pwd',
        'style'       => 'layui-btn-xs layui-btn-warm',
        'status'      => 1,
        'sort'        => 3,
        'create_time' => time()
    ],
    [
        'mg_module'   => $mg_module,
        'parent_id'   => $second_2_id,
        'name'        => '删除管理员',
        'router'      => "{$mg_module}/MgMember/del_member",
        'module'      => $mg_module,
        'controller'  => 'MgMember',
        'action'      => 'del_member',
        'style'       => 'layui-btn-xs layui-btn-danger',
        'status'      => 1,
        'sort'        => 4,
        'create_time' => time()
    ]
];
db('mg_menu')->insertAll($second_2_data);