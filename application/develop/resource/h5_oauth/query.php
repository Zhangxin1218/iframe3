<?php

db('mg_menu')
    ->whereOr("mg_module='{$mg_module}' and name = '用户管理'")
    ->whereOr("mg_module='{$mg_module}' and router = '{$mg_module}/User/user_list'")
    ->delete();

$parent_id = db('mg_menu')->insertGetId([
    'mg_module'     => $mg_module,
    'name'          => '用户管理',
    'module'        => $mg_module,
    'router'        => "",
    'controller'    => '',
    'action'        => '',
    'icon'          => 'layui-icon-username',
    'status'        => 1,
    'create_time'   => time()
]);
db('mg_menu')->insertGetId([
    'parent_id'     => $parent_id,
    'mg_module'     => $mg_module,
    'name'          => '用户列表',
    'module'        => $mg_module,
    'router'        => "{$mg_module}/User/user_list",
    'controller'    => 'User',
    'action'        => 'user_list',
    'icon'          => '',
    'status'        => 1,
    'create_time'   => time()
]);