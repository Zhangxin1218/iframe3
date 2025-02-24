<?php /*a:3:{s:74:"D:\phpstudy_pro\benren\iframe3\application\develop\view\install\step1.html";i:1622621568;s:73:"D:\phpstudy_pro\benren\iframe3\application\common\view\public\header.html";i:1617787436;s:73:"D:\phpstudy_pro\benren\iframe3\application\common\view\public\footer.html";i:1617781666;}*/ ?>
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
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md1"></div>
        <div class="layui-card layui-col-md10">
            <div class="layui-card-header">三国服务器 THINKPHP-5.1 自动化后台3.0  - Step1 开发配置写入</div>
            <div class="layui-card-body">
                <form action="" class="layui-form">
                    <fieldset class="layui-elem-field">
                        <legend>上传配置</legend>
                        <div class="layui-field-box">
                            <div class="layui-form-item">
                                <label class="layui-form-label">OSS阿里</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="oss_open" value="1" lay-text="On|Off" lay-skin="switch" lay-filter="oss_switch">
                                </div>
                            </div>
                            <div id="oss_box" style="display: none;">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">上传目录名</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="oss_dir" value="" placeholder="请输入上传目录名" class="layui-input">
                                    </div>
                                    <div class="layui-input-block layui-form-intro">
                                        <div class="layui-form-mid layui-word-aux">OSS上传目录应该按照项目名称分项目存储, 命名格式以字母开头，可以为字母和数字</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="layui-field-box">
                            <div class="layui-form-item">
                                <label class="layui-form-label">COS腾讯</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="cos_open" value="1" lay-text="On|Off" lay-skin="switch" lay-filter="cos_switch">
                                </div>
                            </div>
                            <div id="cos_box" style="display: none;">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">上传目录名</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="cos_dir" value="" placeholder="请输入上传目录名" class="layui-input">
                                    </div>
                                    <div class="layui-input-block layui-form-intro">
                                        <div class="layui-form-mid layui-word-aux">COS上传目录应该按照项目名称分项目存储, 命名格式以字母开头，可以为字母和数字</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="layui-elem-field">
                        <legend>开发环境配置</legend>
                        <div class="layui-field-box">
                            <div class="layui-form-item">
                                <label class="layui-form-label">DB_HOST</label>
                                <div class="layui-input-block">
                                    <input type="text" name="develop[db_host]" value="127.0.0.1" lay-verify="required" placeholder="数据库连接地址" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">DB_PORT</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[db_port]" value="3306" lay-verify="required" placeholder="数据库端口" class="layui-input">
                                </div>
                                <label class="layui-form-label">DB_CHAR</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[db_char]" value="utf8mb4" lay-verify="required" placeholder="数据库编码" class="layui-input">
                                </div>
                                <label class="layui-form-label">DB_NAME</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[db_name]" value="" lay-verify="required" placeholder="数据库名称" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">DB_USER</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[db_user]" value="root" lay-verify="required" placeholder="数据库账号" class="layui-input">
                                </div>
                                <label class="layui-form-label">DB_PASS</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[db_pass]" value="root" placeholder="数据库密码" class="layui-input">
                                </div>
                                <label class="layui-form-label">DB_PRE</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[db_pre]" value="" lay-verify="required" placeholder="数据表前缀" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">REDIS_HOST</label>
                                <div class="layui-input-block">
                                    <input type="text" name="develop[redis_host]" value="47.110.91.60" lay-verify="required" placeholder="redis连接地址" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">REDIS_PORT</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[redis_port]" value="27635" lay-verify="required" placeholder="redis端口" class="layui-input">
                                </div>
                                <label class="layui-form-label">REDIS_PASS</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[redis_pass]" value="1yVXE0uOItLIBCE" placeholder="redis密码" class="layui-input">
                                </div>
                                <label class="layui-form-label">REDIS_INDEX</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="develop[redis_index]" value="" lay-verify="required" placeholder="redis库" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-input-block layui-form-intro">
                                <div class="layui-form-mid layui-word-aux">
                                    Redis库使用说明：41-曾鑫 42-吴锦文 43-杨云天, 李万鹏 44-韶敏 45-李典彪
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn layui-btn-normal" lay-submit="">写入开发配置</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="layui-col-md1"></div>
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
    layui.use(['form'], function() {
        var form = layui.form;
        var $    = layui.$;

        form.render();

        form.on('switch(oss_switch)', function(data) {
            if($(data.elem).prop('checked')) {
                $('#oss_box').show();
            }else {
                $('#oss_box').hide();
            }
        });

        form.on('switch(cos_switch)', function(data) {
            if($(data.elem).prop('checked')) {
                $('#cos_box').show();
            }else {
                $('#cos_box').hide();
            }
        });

        form.on('submit()', function(data) {
            Post('<?php echo url("Install/step1"); ?>', data.field, function(res) {
                if(res.code == 1) {
                    alert_success(res.msg, function() {
                        location.href = '<?php echo url("Install/step2"); ?>';
                    });
                }else {
                    alert_error(res.msg);
                }
            });
            return false;
        });
    });
</script>