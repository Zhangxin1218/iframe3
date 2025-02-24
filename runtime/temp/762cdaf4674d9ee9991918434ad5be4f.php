<?php /*a:3:{s:71:"D:\phpstudy_pro\benren\iframe3\application\common\view\index\index.html";i:1617781666;s:73:"D:\phpstudy_pro\benren\iframe3\application\common\view\public\header.html";i:1617787436;s:73:"D:\phpstudy_pro\benren\iframe3\application\common\view\public\footer.html";i:1617781666;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="<?php echo htmlentities($resource_url); ?>layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="<?php echo htmlentities($resource_url); ?>layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="<?php echo htmlentities($resource_url); ?>layuiadmin/style/login.css" media="all">
    <link rel="stylesheet" href="<?php echo htmlentities($resource_url); ?>admin/css/login.css" media="all">
    <style>
        td img {
            width: 40px;
            height: 40px;
            display: block;
        }
        .layui-search {
            display: block;
            padding: 10px 10px 0 10px;
            border: 1px solid #e6e6e6;
            background-color: #f2f2f2;
            margin-top: 10px;
        }
        .layui-search .layui-col-md3, .layui-search .layui-col-md9 {
            margin-bottom: 10px;
        }
        .layui-search label {
            float: left;
            display: block;
            width: 80px;
            font-weight: 400;
            line-height: 20px;
            text-align: right;
            padding: 9px 9px 9px 0;
            text-align-last: justify;
            font-size: 13px;
        }
        .layui-search .layui-input-inline {
            width: calc(100% - 100px);
        }
        .layui-form-mid {
            float: none !important;
        }
        .layui-form-select .layui-anim {
            z-index: 999999;
        }
        .img_preview {
            cursor: pointer;
        }
        .table_img_preview {
            cursor: pointer;
            width: 35px !important;
            height: 35px !important;
        }
    </style>
    <script>
        var UPLOAD_URL   = '<?php echo url("admin/Upload/upload_file"); ?>';
        var RESOURCE_URL = '<?php echo htmlentities($resource_url); ?>';
    </script>
</head>
<body>
<div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <!-- 头部区域 -->
            <ul class="layui-nav layui-layout-left">
                <li class="layui-nav-item layadmin-flexible" lay-unselect>
                    <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                        <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                    </a>
                </li>
                <!--<li class="layui-nav-item layui-hide-xs" lay-unselect>-->
                <!--<a href="" target="_blank" title="前台">-->
                <!--<i class="layui-icon layui-icon-website"></i>-->
                <!--</a>-->
                <!--</li>-->
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;" layadmin-event="refresh" title="刷新">
                        <i class="layui-icon layui-icon-refresh-3"></i>
                    </a>
                </li>
                <!--<li class="layui-nav-item layui-hide-xs" lay-unselect>-->
                <!--<input type="text" placeholder="搜索..." autocomplete="off" class="layui-input layui-input-search" layadmin-event="serach" lay-action="template/search.html?keywords=">-->
                <!--</li>-->
            </ul>
            <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="theme">
                        <i class="layui-icon layui-icon-theme"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="note">
                        <i class="layui-icon layui-icon-note"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="fullscreen">
                        <i class="layui-icon layui-icon-screen-full"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect style="margin-right: 30px;">
                    <a href="javascript:;">
                        <cite><?php echo htmlentities($mg_user['username']); ?></cite>
                    </a>
                    <dl class="layui-nav-child">
                        <!--<dd><a lay-href="set/user/info.html">基本资料</a></dd>-->
                        <dd><a lay-href="<?php echo url('MgMember/edit_pwd'); ?>">修改密码</a></dd>
                        <!--<hr>-->
                        <dd style="text-align: center;"><a href="<?php echo url('MgMember/logout'); ?>">退出</a></dd>
                    </dl>
                </li>
            </ul>
        </div>

        <!-- 侧边菜单 -->
        <div class="layui-side layui-side-menu">
            <div class="layui-side-scroll">
                <div class="layui-logo">
                    <span>后台管理</span>
                </div>
                <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
                    <li class="layui-nav-item layui-this">
                        <a href="javascript:;" lay-href="<?php echo htmlentities($home_page); ?>">
                            <i class="layui-icon layui-icon-home"></i>
                            <cite>控制台</cite>
                        </a>
                    </li>
                    <?php foreach($access_list as $item): if(!empty($item['router'])): ?>
                    <li data-name="get" class="layui-nav-item">
                        <a href="javascript:;" lay-href="<?php echo url((strpos($item['router'], '.') !== false ? '@' : '').$item['router']); ?>">
                            <i class="layui-icon <?php echo htmlentities($item['icon']); ?>"></i>
                            <cite><?php echo htmlentities($item['name']); ?></cite>
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="layui-nav-item">
                        <a href="javascript:;">
                            <i class="layui-icon <?php echo htmlentities($item['icon']); ?>"></i>
                            <cite><?php echo htmlentities($item['name']); ?></cite>
                        </a>
                        <dl class="layui-nav-child">
                            <?php foreach($item['sub_list'] as $sub_item): ?>
                            <dd>
                                <a lay-href="<?php echo url((strpos($sub_item['router'], '.') !== false ? '@' : '').$sub_item['router']); ?>"><?php echo htmlentities($sub_item['name']); ?></a>
                            </dd>
                            <?php endforeach; ?>
                        </dl>
                    </li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- 页面标签 -->
        <div class="layadmin-pagetabs" id="LAY_app_tabs">
            <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-down">
                <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;"></a>
                        <dl class="layui-nav-child layui-anim-fadein">
                            <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                            <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                            <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
            <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
                <ul class="layui-tab-title" id="LAY_app_tabsheader">
                    <li lay-id="<?php echo htmlentities($home_page); ?>" lay-attr="<?php echo htmlentities($home_page); ?>" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
                </ul>
            </div>
        </div>


        <!-- 主体内容 -->
        <div class="layui-body" id="LAY_app_body">
            <div class="layadmin-tabsbody-item layui-show">
                <iframe src="<?php echo htmlentities($home_page); ?>" frameborder="0" class="layadmin-iframe"></iframe>
            </div>
        </div>

        <!-- 辅助元素，一般用于移动设备下遮罩 -->
        <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
</div>
<script src="<?php echo htmlentities($resource_url); ?>layuiadmin/layui/layui.js"></script>
<script src="<?php echo htmlentities($resource_url); ?>admin/js/common.js"></script>
<script>
    layui.config({
        base: '<?php echo htmlentities($resource_url); ?>layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['layer', 'form'], function() {
        var $       = layui.$;
        var form    = layui.form;
        var layer   = layui.layer;
        form.render();
        form.on('checkbox(checkAll)', function(data) {
            if($(data.elem).prop('checked')) {
                $('[name="ids[]"]').prop("checked", true);
                form.render();
            }else {
                $('[name="ids[]"]').prop("checked", false);
                form.render();
            }
        });
        $('.img_preview').click(function () {
            // 创建对象
            var img = new Image();
            img.src = $(this).attr('src');
            var height = img.height, width = img.width;
            while(width > 800 || height > 400) {
                height /= 2;
                width /= 2;
            }
            layer.open({
                type: 1,
                shade: false,
                title: false,
                area: [width+'px', height+ 'px'],
                content: '<img style="width: '+width+'px; height: '+height+'px;" src="'+$(this).attr('src')+'"/>'
            });
        });
    });
    // 图片预览
    function img_preview(obj) {
        var $ = layui.$;
        var layer = layui.layer;
        // 创建对象
        var img = new Image();
        img.src = $(obj).attr('src');
        var height = img.height, width = img.width;
        while(width > 800 || height > 400) {
            height /= 2;
            width /= 2;
        }
        layer.open({
            type: 1,
            shade: false,
            title: false,
            area: [width+'px', height+ 'px'],
            content: '<img style="width: '+width+'px; height: '+height+'px;" src="'+$(obj).attr('src')+'"/>'
        });
    }
</script>
</body>
</html>
<script>
    layui.use('index');
</script>