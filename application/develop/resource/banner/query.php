<?php
db('mg_menu')
    ->whereOr("mg_module='{$mg_module}' and router = '{$mg_module}/Banner/banner_list'")
    ->whereOr("mg_module='{$mg_module}' and router = '{$mg_module}/Banner/add_banner'")
    ->whereOr("mg_module='{$mg_module}' and router = '{$mg_module}/Banner/edit_banner'")
    ->whereOr("mg_module='{$mg_module}' and router = '{$mg_module}/Banner/del_banner'")
    ->delete();
$parent_id = db('mg_menu')->insertGetId([
    'mg_module'     => $mg_module,
    'name'          => '图片管理',
    'module'        => $mg_module,
    'router'        => "$mg_module/Banner/banner_list",
    'controller'    => 'Banner',
    'action'        => 'banner_list',
    'icon'          => 'layui-icon-picture',
    'status'        => 1,
    'create_time'   => time()
]);
db('mg_menu')->insert([
    'mg_module'     => $mg_module,
    'parent_id'     => $parent_id,
    'name'          => '添加图片',
    'module'        => $mg_module,
    'router'        => "$mg_module/Banner/add_banner",
    'controller'    => 'Banner',
    'action'        => 'add_banner',
    'style'         => '',
    'status'        => 1,
    'sort'          => 1,
    'create_time'   => time()
]);
db('mg_menu')->insert([
    'mg_module'     => $mg_module,
    'parent_id'     => $parent_id,
    'name'          => '编辑图片',
    'module'        => $mg_module,
    'router'        => "$mg_module/Banner/edit_banner",
    'controller'    => 'Banner',
    'action'        => 'edit_banner',
    'style'         => 'layui-btn-xs layui-btn-normal',
    'status'        => 1,
    'sort'          => 2,
    'create_time'   => time()
]);
db('mg_menu')->insert([
    'mg_module'     => $mg_module,
    'parent_id'     => $parent_id,
    'name'          => '删除图片',
    'module'        => $mg_module,
    'router'        => "$mg_module/Banner/del_banner",
    'controller'    => 'Banner',
    'action'        => 'del_banner',
    'style'         => 'layui-btn-xs layui-btn-danger',
    'status'        => 1,
    'sort'          => 3,
    'create_time'   => time()
]);