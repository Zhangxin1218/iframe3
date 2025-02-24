/**
 * POST请求
 * @param url
 * @param data
 * @param callback
 * @constructor
 */
 function Post(url, data, callback=function(){}) {
    var layer = layui.layer;
    var $ = layui.$;
    var index = layer.load(1, {
        shade: [0.6,'#000'] //0.1透明度的白色背景
    });
    $.ajax({
        url: url,
        type:'post',
        data: data,
        dataType: 'json',
        timeout: 600000000,
        success: function(res) {
            if(res.code == 99) {
                alert_error(res.msg, function() {
                    if(window.parent.parent.parent) {
                        var dom = window.parent.parent.parent;
                    }else if(window.parent.parent) {
                        var dom = window.parent.parent;
                    }else if(window.parent) {
                        var dom = window.parent;
                    }else {
                        var dom = window;
                    }
                    dom.location.href = LOGIN_URL;
                })
            }else {
                callback(res);
            }
        },
        error: function() {
            alert_error('请求失败');
        },
        complete: function() {
            layer.close(index);
        }
    });
}

/**
 * 确认弹窗
 * @param url
 * @param text
 * @param is_table
 */
function confirm(url, text, is_table) {
    var layer = layui.layer;
    var $ = layui.$;
    layer.confirm(text, {
        btn: ['确认','取消']
    }, function(){
        Post(url, {a:1}, function (res) {
            if(res.code == 1) {
                alert_success(res.msg, function () {
                    if(is_table) {
                        table_reload();
                    }else {
                        location.reload();
                    }
                })
            }else {
                alert_error(res.msg);
            }
        });
    });
}

/**
 * 输入内容并提交
 * @param url
 * @param text
 * @param is_table
 */
function _prompt(url, text, is_table) {
    var layer = layui.layer;
    var $ = layui.$;
    var index = layer.prompt({title: text, formType: 2}, function(text, index){
        layer.close(index);
        Post(url, {'text': text}, function(res) {
            if(res.code == 1) {
                alert_success(res.msg, function () {
                    if(is_table) {
                        table_reload();
                    }else {
                        location.reload();
                    }
                })
            }else {
                alert_error(res.msg);
            }
        });
    });
}

/**
 * 上传文件到接口并刷新页面
 * @param url 接口地址
 * @param accept 文件类型过滤
 * @param is_table
 * @private
 */
function _upload(url, accept, is_table) {
    var $ = layui.$;
    if(!$('#_upload').length) {
        $('body').append('<input style="display: none;" type="file" accept="'+accept+'" id="_upload" />');
        $('#_upload').on('change', function() {
            var data = new FormData();
            var file = $(this)[0].files[0];
            if(!file) return;
            data.append('file', file);
            Upload(url, data, function(res) {
                if(res.code == 1) {
                    alert_success(res.msg, function() {1
                        if(is_table) {
                            table_reload();
                        }else {
                            location.reload();
                        }
                    })
                }else {
                    alert_error(res.msg);
                }
            });
        });
    }
    $('#_upload').click();
}

/**
 * 批量操作
 * @param url
 * @param text
 * @param is_table
 */
function batch_action(url, text, is_table) {
    var $ = layui.$;
    var id = [];
    $('[name="ids[]"]').each(function() {
        if($(this).is(":checked")) {
            id.push($(this).val());
        }
    });
    if(id.length == 0) {
        alert_error('请至少选择一行数据');
        return false;
    }
    layer.confirm(text, {
        btn: ['确认','取消']
    }, function(){
        Post(url, {id:id}, function (res) {
            if(res.code == 1) {
                alert_success(res.msg, function () {
                    if(is_table) {
                        table_reload();
                    }else {
                        location.reload();
                    }
                });
            }else {
                alert_error(res.msg);
            }
        });
    });
}

/**
 * 打开frame窗口
 * @param url
 * @param title
 * @param area
 */
function open_frame(url, title, area) {
    if(!area) {
        area = [document.body.clientWidth * 0.9 + 'px', document.body.clientHeight * 0.9 + 'px'];
    }
    layer.open({
        type: 2,
        title: title,
        shade: 0.6,
        area: area,
        content: url
    });
}

/**
 * 关闭frame窗口
 */
function close_frame() {
    if(window.is_close_frame) return;
    window.is_close_frame = true;
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
}

/**
 * 回退到上一页
 */
function back_url() {
    if(window.is_back_url) return;
    if(document.referrer) {
        location.href = document.referrer;
    }else {
        history.go(-1);
    }
}

/**
 * 成功提示
 * @param msg
 * @param callback
 */
function tips_success(msg, callback=function(){}) {
    var layer = layui.layer;
    layer.msg(msg, {
        icon: 1,
        time: 1500
    }, function () {
        callback()
    });
}

/**
 * 失败提示
 * @param msg
 */
function tips_error(msg) {
    var layer = layui.layer;
    layer.msg(msg, {
        icon: 0,
        time: 500
    });
}

/**
 * 成功弹窗
 * @param msg
 * @param callback
 */
function alert_success(msg, callback=function(){}) {
    var layer = layui.layer;
    layer.alert(msg, {icon: 6}, function (index) {
        layer.close(index);
        callback()
    });
}

/**
 * 失败弹窗
 * @param msg
 */
function alert_error(msg, callback=function(){}) {
    var layer = layui.layer;
    layer.alert(msg, {icon: 5}, function(index) {
        layer.close(index);
        callback()
    });
}

/**
 * 上传绑定事件
 * @param id
 * @param type [image-图片 video-视频]
 * @param remarks 备注
 * @param upload_url
 * @param upload_name
 */
function uploadInit(id, type='image', remarks='', upload_url='', upload_name='file') {
    var file_list = [];
    file_list[id] = [];
    var $ = layui.$;
    var obj = $('#'+id);
    if(obj.data('lock')) return;
    var accept_ext = '*';

    upload_url = upload_url == '' ? window.UPLOAD_URL : upload_url;
    // 解析type
    if(type.indexOf('|') > 0) {
        var _t = type.split('|');
        type = _t[0];
        accept_ext = _t[1];

    }
    obj.data('lock', 1);
    var default_img = window.RESOURCE_URL+ 'admin/img/default.png';
    var preview_html = '', text_1 = '', remarks_html = '<div style="display: inline-block; margin: 0 0 0 40px; color: red;">{remarks}</div>';
    if(type == 'image') { // 单图上传
        // preview_html = '<img class="upload_preview_'+id+'" src="'+default_img+'" id="i_'+id+'" title="点击预览" style="cursor: pointer; height: 150px; width: 150px; display: block;">';
        // text_1 = '选择图片';
        preview_html = '<div class="pictrue onepic_'+id+'" style="display:none"><img src="'+default_img+'" class="upload_preview_'+id+'" id="i_'+id+'" title="点击预览"><i class="layui-icon closes upload_colse_'+id+'"">ဇ</i></div>' ;
    }else if(type == 'images') { // 多图上传
        text_1 = '选择图片';
    }else if(type == 'video') {
        // preview_html = '<video class="upload_preview_'+id+'" id="i_'+id+'" title="点击预览" style="cursor: pointer; height: 150px; width: 150px; display: none;">';
        // text_1 = '选择视频';
        preview_html = ''
    }else if(type == 'audio') {
        preview_html = '<audio class="upload_preview_'+id+'" id="i_'+id+'" controls style="display: none; outline: none;">';
        text_1 = '选择音频';
    }else if(type == 'file') {
        preview_html = '<p class="upload_preview_'+id+'" src="'+default_img+'" id="i_'+id+'" title="点击预览" style="cursor: pointer;"></p>';
        text_1 = '选择文件';
    }
    // obj.parent().append('<div id="_'+id+'" class="pictrueBox">'+
    //     '<button type="button" class="layui-btn" id="b_'+id+'">'+text_1+'</button>'+
    //     remarks_html.replace('{remarks}', remarks) +
    //     '<blockquote class="layui-elem-quote layui-quote-nm layui-row" style="margin-top: 10px;">' +
    //     preview_html+
    //     '</blockquote>' +
    //     '</div>');


    if(type == 'video'){
        progress_ini = 0;
        obj.parent().append(preview_html+
            '<div id="_'+id+'" >'+
            '<div  id="v_'+id+'">' +
            '<button type="button" class="layui-btn layui-btn-sm layui-btn-normal">上传视频</button>'+
            '</div>' +
            '</div>');
        obj.parent().parent().append(' <div class="layui-input-block video_progress_'+id+'" style="display:none;width: 30%;margin-top: 20px;">' +
                                        '<div class="layui-progress" style="margin-bottom: 10px">' +
                                        ' <div class="layui-progress-bar layui-bg-blue" id="progress_'+id+'" style="width:0%"></div>'+
                                        ' </div>' +
                                        ' <button type="button" class="layui-btn layui-btn-sm layui-btn-danger percent" id="button_progress_'+id+'">0%</button>'+
                                        '</div>');
        obj.parent().parent().append('<div class="layui-input-block video_show_'+id+'" style="display:none;">' +
                                        '<div class="layui-video-box" style="">' +
                                        ' <video src="" controls="controls" id="video_src_'+id+'" style="width: 100%; height: 100% !important; border-radius: 10px;">您的浏览器不支持 video 标签</video>'+
                                        ' <div class="mark" style="">' +
                                        '<span class="layui-icon layui-icon-delete delete_video_'+id+'" data-id="'+id+'" style="font-size: 30px; color: rgb(30, 159, 255);"></span>'+
                                        '</div></div></div>');
    }else{
        obj.parent().append(preview_html+
            '<div id="_'+id+'" class="pictrueBox" >'+
            '<div class="upLoad"  id="b_'+id+'">' +
            '<i class="layui-icon layui-icon-picture" style="font-size: 26px;"></i>'+
            '</div>' +
            '</div>');
    }
    if(obj.val() != '') {
        if(type == 'image' || type == 'video') {
            file_list[id] = [obj.val()];
        }else if(type == 'audio'){
            file_list[id] = [obj.val()];
        }else if(type == 'images') {
            file_list[id] = eval(obj.val());
        }else if(type == 'file') {
            file_list[id] = [obj.val()];
        }
        parse_html();
    }

    //视频上传
    $('#v_'+id).click(function(){
        $('#f_'+id).remove();
        obj.parent().append('<input type="file" id="f_'+id+'" accept="video" style="display: none;">');
        document.getElementById('f_'+id).click();
        $('#f_'+id).on('change', function() {
            var inputFile = this.files[0];
            $.ajax({
                'url' : OSS_SIGN_URL,
                dataType: 'json',
                success:function(res){
                    console.log(res);
                    if(res.code){
                        AdminUpload.upload(res.data.uploadType,{
                            token: res.data.uploadToken || '',
                            file: inputFile,
                            accessKeyId: res.data.accessKey || '',
                            accessKeySecret: res.data.secretKey || '',
                            bucketName: res.data.storageName || '',
                            region: res.data.storageRegion || '',
                            domain: res.data.domain || '',
                            static_path:res.data.static_path || '',
                            static_url : res.data.static_url || '',
                            uploadIng:function (progress) {
                                $('.video_progress_'+id).show();
                                $('#button_progress_'+id).text(progress+'%');
                                $('#progress_'+id).css('width',progress+'%')
                                console.log(progress);
                            }
                        }).then(function (res) {
                            //成功
                            $('#'+id).val(res.url);
                            $('.video_show_'+id).show();
                            $('#video_src_'+id).attr('src',res.url);
                            $('.video_progress_'+id).hide();
                            layer.msg('上传成功');
                        }).catch(function (err) {
                            //失败
                            console.info(err);
                            return layer.alert('上传错误请检查您的配置');
                        });
                    }
                }
            });
        })
    });
    $('.delete_video_'+id).click(function(){
        $('.video_show_'+id).hide();
        $('#video_src_'+id).attr('src','');
        $('#button_progress_'+id).text('0%');
        $('#progress_'+id).css('width','0%');
        $('#'+id).val('');
    });
    // 上传按钮点击事件
    $('#b_'+id).click(function() {
        var accept = '', enctype = '';
        if(type == 'image' || type == 'images') accept = 'image';
        if(type == 'video' || type == 'videos') accept = 'video';
        if(type == 'audio') accept = 'audio';
        if(type == 'images' || type == 'videos') enctype = 'multiple';
        if(type == 'file') accept = 'application';
        obj.parent().append('<input type="file" id="f_'+id+'" accept="'+accept+'/'+accept_ext+'" '+enctype+' style="display: none;">');
        document.getElementById('f_'+id).click();
        // input选中文件事件
        $('#f_'+id).on('change', function() {
            var files = this.files;
            $(this).remove();
            // 单图上传
            if(type == 'image' || type == 'video' || type == 'audio') {
                var data = new FormData();
                data.append(upload_name, files[0]);
                Upload(upload_url, data, function(res) {
                    if(res.code == 1) {
                        file_list[id] = [res.data.src];
                        // 渲染
                        parse_html(preview_html);
                    }else {
                        alert_error(res.msg);
                    }
                });
            }else if(type == 'images') {
                var data = new FormData();
                for(var i =0; i<=files.length; i++) {
                    data.append(upload_name+'[]', files[i]);
                }
                Upload(upload_url, data, function(res) {
                    if(res.code == 1) {
                        for(var i=0; i<res.data.src.length; i++) {
                            file_list[id].push(res.data.src[i]);
                        }
                        // 渲染
                        parse_html();
                    }else {
                        alert_error(res.msg);
                    }
                });
            }else if(type == 'video') {

            }else if(type == 'file') {
                var data = new FormData();
                data.append(upload_name, files[0]);
                Upload(upload_url, data, function(res) {
                    if(res.code == 0) {
                        file_list[i] = [res.data.src];
                        // 渲染
                        parse_html();
                    }else {
                        alert_error(res.msg);
                    }
                });
            }
        });
    });

    // 渲染html
    function parse_html(preview_html = '') {
        // var imgs_preview = '<div style="display: inline-block; position: relative; margin: 0 10px 5px 0;"><i title="删除图片" class="layui-icon layui-icon-close upload_colse_'+id+'" data-src="{d_src}" style="cursor: pointer; position: absolute; top: 10px; right: 10px; font-size: 30px; font-weight: bold;"></i><img src="{src}" class="upload_preview_'+id+'" title="点击预览" style="cursor: pointer; height: 150px; width: 150px;"></div>';
        var html = '';
        var imgs_preview = '<div class="pictrueBox pictrue pics_'+id+'"><img src="{src}"  class="upload_preview_'+id+'" title="点击预览"> <i data-src="{d_src}"  class="layui-icon closes upload_colse_'+id+'" ">ဇ</i></div>';
        if(type == 'image') {
            $('#i_'+id).attr('src', file_list[id][0]);
            $('.onepic_'+id).show();
            obj.val(file_list[id][0]);
            $('.upload_colse_'+id).click(function() {
                $('.onepic_'+id).hide();
                $('#i_'+id).attr('src','');
                $('#'+id).val('');
            });
        }else if(type == 'images') {
            $('.pics_'+id).remove();
            console.log(file_list);
            for(var i=0; i<file_list[id].length; i++) {
                var temp = imgs_preview;
                temp = temp.replace('{src}', file_list[id][i]);
                temp = temp.replace('{d_src}', file_list[id][i]);
                $('#'+id).parent().prepend(temp);
                // html += temp;
            }
            obj.val(JSON.stringify(file_list[id]));
            // obj.parent().find('blockquote').html(html);
            $('.upload_colse_'+id).click(function() {
                file_list[id].splice(file_list[id].indexOf($(this).data('src')), 1);
                parse_html();
            });
        }else if(type == 'video') {
            $('#i_'+id).attr('src', file_list[0]);
            $('#i_'+id).css('display', 'block');
            obj.val(file_list[id][0]);
            $('.video_show_'+id).show();
            $('#video_src_'+id).attr('src',file_list[id][0]);
        }else if(type == 'audio') {
            $('#i_'+id).attr('src', file_list[0]);
            $('#i_'+id).css('display', 'block');
            obj.val(file_list[id][0]);
           
        }else if(type == 'file') {
            obj.val(file_list[0]);
            $('#i_'+id).attr('src', file_list[0]);
            $('#i_'+id).text(file_list[id][0]);
        }

        // 预览事件
        $('.upload_preview_'+id).unbind('click');
        $('.upload_preview_'+id).click(function() {
            var max_height = document.body.clientHeight;
            var max_width  = document.body.clientWidth;
            if(type == 'image' || type == 'images') {
                // 创建对象
                var img = new Image();
                img.src = $(this).attr('src');
                var height = img.height, width = img.width;
                while(width > max_width || height > max_height) {
                    height /= 2;
                    width /= 2;
                }
                layer.open({
                    type: 1,
                    shade: 0.6,
                    title: false,
                    area: [width+'px', height+ 'px'],
                    content: '<img style="width: '+width+'px; height: '+height+'px;" src="'+$(this).attr('src')+'"/>'
                });
            }else if(type == 'video' || type == 'videos'){
                var height = document.getElementById('i_'+id).videoHeight;
                var width = document.getElementById('i_'+id).videoWidth;
                while(width > max_width || height > max_height) {
                    height /= 2;
                    width /= 2;
                }
                layer.open({
                    type: 1,
                    shade: 0.6,
                    title: false,
                    area: [width+'px', height+ 'px'],
                    content: '<video autoplay controls style="width: 100%; height:100%;" src="'+$(this).attr('src')+'"/>'
                });
            }else if(type == 'file') {
                window.open($('#'+id).val());
            }
        });
    }
}

/**
 * 上传方法
 * @param upload_url
 * @param data
 * @param callback
 * @constructor
 */
function Upload(upload_url, data, callback=function(){}) {
    var $ = layui.$;
    var loading = layer.load(1, {
        shade: [0.4, '#000'],
        id: 'loading',
        content: '正在上传......0%',
        success: function (layero) {
            layero.find('.layui-layer-content').css({
                'padding-left': '45px',
                'width': '200px',
                'line-height': '36px',
                'font-size': '16px',
                'color': '#ddd'
            });
        }
    });
    $.ajax({
        url: upload_url,
        type:'post',
        data: data,
        contentType: false,
        processData: false,
        dataType: 'json',
        timeout: 600000000,
        xhr: function() {
            var xhr = new XMLHttpRequest();
            //使用XMLHttpRequest.upload监听上传过程，注册progress事件，打印回调函数中的event事件
            xhr.upload.addEventListener('progress', function (e) {
                //loaded代表上传了多少
                //total代表总数为多少
                var progressRate =  Math.round((e.loaded / e.total) * 100) + '%';
                //通过设置进度条的宽度达到效果
                $('#loading').text('正在上传......'+progressRate);
            });
            return xhr;
        },
        success: function(res) {
            layer.closeAll();
            callback(res);
        },
        error: function() {
            layer.closeAll();
            alert_error('上传失败');
        }
    });
}

/**
 * 展示图片
 */
function show_img(src) {
// 创建对象
    var img = new Image();
    img.src = src;
    var height = img.height, width = img.width;
    while(width > 800 || height > 600) {
        height /= 2;
        width /= 2;
    }
    layer.open({
        type: 1,
        shade: false,
        title: false,
        area: [width+'px', height+ 'px'],
        content: '<img style="width: '+width+'px; height: '+height+'px;" src="'+src+'"/>'
    });
}

/**
 * 导出excel
 */
function export_excel(obj) {
    var $ = layui.$;
    $(obj).parent().append('<input type="hidden" name="export_excel" value="1">');
    var fields = {};
    $('.layui-search input').each(function () {
        var name = $(this).attr('name');
        if(name) {
            fields[name] = $(this).val();
        }
    })
    $('.layui-search select').each(function () {
        var name = $(this).attr('name');
        if(name) {
            fields[name] = $(this).val();
        }
    })
    var url = location.href.split('?')[0]+'?';
    for(var i in fields) {
        url += i+'='+fields[i]+'&';
    }
    window.open(url.slice(0, url.length - 1));
    $(obj).parent().find('[name=export_excel]').remove();
    $('.layui-search').submit();
}

/**
 * 搜索
 */
function search(obj) {
    var $ = layui.$;
    $(obj).parent().find('[name=export_excel]').remove();
    $('.layui-search').submit();
}