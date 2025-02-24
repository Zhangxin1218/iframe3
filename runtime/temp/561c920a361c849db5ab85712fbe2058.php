<?php /*a:4:{s:72:"D:\phpstudy_pro\benren\iframe3\application\develop\view\index\index.html";i:1617781666;s:73:"D:\phpstudy_pro\benren\iframe3\application\develop\view\index\header.html";i:1617781666;s:73:"D:\phpstudy_pro\benren\iframe3\application\common\view\public\header.html";i:1617787436;s:73:"D:\phpstudy_pro\benren\iframe3\application\common\view\public\footer.html";i:1617781666;}*/ ?>
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
<ul class="layui-nav">
    <li class="layui-nav-item layui-this"><a href="<?php echo url('Index/add_module'); ?>">添加后台管理</a></li>
    <li class="layui-nav-item">
        <a href="javascript:;">菜单管理</a>
        <dl class="layui-nav-child">
            <?php foreach($module_list as $item): ?>
            <dd><a href="<?php echo url('Menu/menu_list', ['mg_module'=>$item]); ?>"><?php echo htmlentities($item); ?></a></dd>
            <?php endforeach; ?>
        </dl>
    </li>
    <li class="layui-nav-item">
        <a href="javascript:;">插件安装</a>
        <dl class="layui-nav-child">
            <?php foreach($module_list as $key => $item): if($key > 0): break;?>
            <?php endif; ?>
            <dd><a href="<?php echo url('Plugin/plugin_list', ['mg_module'=>$item]); ?>"><?php echo htmlentities($item); ?></a></dd>
            <?php endforeach; ?>
        </dl>
    </li>
</ul>
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
    //注意：导航 依赖 element 模块，否则无法进行功能性操作
    layui.use('element', function(){
        var element = layui.element;

        //…
    });
</script>