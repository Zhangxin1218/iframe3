<?php

/**
 * 根据权限生成按钮
 * @param $router
 * @param array $param
 * @param string $name
 * @param string $type
 * @param array $area frame窗口大小
 * @param string $style 按钮样式
 * @return string
 */
function access_button($router, $param=[], $name='', $type='url', $area=[], $style='') {
    global $mg_user_id, $mg_router;
    $router = count(explode('/', $router)) > 2 ? $router : "{$mg_router}/{$router}";
    $button = '<a href="{url}" class="layui-btn {style}">{icon}{name}</a>';
    $data = cache("{$mg_router}_menu_{$mg_user_id}");
    $link = url($router, $param);
    if($type == 'url') {
        $url = $link;
    }else if($type == 'confirm') {
        $url = "javascript: confirm('{$link}', '是否确认执行此操作？')";
    }else if($type == 'frame') {
        if(is_array($area) && count($area) > 1) {
            $frame_area = ", ['{$area[0]}', '{$area[1]}']";
        }else if(is_string($area)){
            $frame_area = ", ['{$area}', '90%']";
        }else {
            $frame_area = ", ['90%', '90%']";
        }
        $url = "javascript: open_frame('{$link}', '{$data['access_menu'][$router]['name']}'{$frame_area})";
    }else if($type == 'prompt') {
        $url = "javascript: _prompt('{$link}', '{$area}')";
    }else if($type == 'upload') {
        $area = empty($area) ? '' : $area;
        $url = "javascript: _upload('{$link}', '{$area}')";
    }else if($type == 'batch') {
        $url = "javascript: batch_action('{$link}', '{$area}')";
    }else {
        $url = '';
    }
    $real_style = empty($style) ? $data['access_menu'][$router]['style'] : $style;
    if(in_array($router, array_keys($data['access_menu']))) {
        return str_replace([
            '{url}',
            '{style}',
            '{icon}',
            '{name}'
        ], [
            $url,
            $real_style,
            $data['access_menu'][$router]['icon'] ? '<i class="layui-icon '.$data['access_menu'][$router]['icon'].'"></i>' : '',
            empty($name) ? $data['access_menu'][$router]['name'] : $name
        ], $button);
    }
}

/**
 * 后台JSON格式返回数据[layui数据表格]
 * @param int $code
 * @param string $msg
 * @param array $data
 * @param int $count
 */
function admin_response($code=0, $msg='', $data=[], $count=0) {
    echo json_encode([
        'code'  => $code,
        'msg'   => $msg,
        'data'  => $data,
        'count' => $count
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * 数据表格按钮
 * @param $router
 * @param string $name
 * @param string $event
 * @param string $text
 * @param array $area frame大小, 默认90% * 90% [宽, 高] ['30%', '300px']
 * @param string $condition 按钮展示条件
 * @return string
 * @date 2020/12/23 15:50
 */
function table_button($router, $name='', $event='frame', $text='是否确认执行此操作?', $area=[], $condition='') {
    if($text == '') $text = '是否确认执行此操作?';
    global $mg_user_id, $mg_router;
    $router = count(explode('/', $router)) > 2 ? $router : "{$mg_router}/{$router}";
    $button = '<a data-url="{url}" data-text="{text}" data-param="{param}" class="layui-btn {style}" lay-event="{event}">{icon}{name}</a>';
    if(!empty($condition)) $button = " {{# if(d.{$condition}){ }} {$button} {{# } }} ";
    $data = cache("{$mg_router}_menu_{$mg_user_id}");
    $link = url($router);
    $real_style = empty($style) ? $data['access_menu'][$router]['style'] : $style;
    $param = '';
    if($event == 'frame') {
        if(is_array($area) && count($area) > 1) {
            $param = "['{$area[0]}', '{$area[1]}']";
        }else if(is_string($area)){
            $param = "['{$area}', '90%']";
        }else {
            $param = "['90%', '90%']";
        }
        $text = $data['access_menu'][$router]['name'];
    }
    if(in_array($router, array_keys($data['access_menu']))) {
        return str_replace([
            '{url}',
            '{text}',
            '{param}',
            '{style}',
            '{event}',
            '{icon}',
            '{name}'
        ], [
            $link,
            $text,
            $param,
            $real_style,
            $event,
            $data['access_menu'][$router]['icon'] ? '<i class="layui-icon '.$data['access_menu'][$router]['icon'].'"></i>' : '',
            empty($name) ? $data['access_menu'][$router]['name'] : $name
        ], $button);
    }
}

/**
 * 数据表格图片
 * @param string $src
 * @return string
 * @date 2021/3/16 17:15
 */
function table_img($src='') {
    return '<img src="'.$src.'" class="table_img_preview" onclick="img_preview(this)" />';
}

/**
 * 数据表格switch操作
 * @param string $router 状态改变后请求接口地址
 * @param array $param 参数
 * @param bool $checked 是否选择
 * @param string $text
 * @return string
 * @date 2021/3/17 11:17
 */
function table_switch($router, $param=[], $checked=true, $text='上架|下架') {
    return '<input type="checkbox" lay-skin="switch" lay-text="'.$text.'" lay-filter="table_switch" data-url="'.url($router, $param).'"'.($checked ? ' checked' : '').' />';
}

// 定义换行符
define('BR', '
');

define('FORM_ATTR', [
    'type' => 'type="{type}"',
    'name' => 'name="{name}"',
    'value' => 'value="{value}"',
    'placeholder' => 'placeholder="{placeholder}"',
    'lay-verify' => 'lay-verify="{lay-verify}"',
    'lay-filter' => 'lay-filter="{lay-filter}"',
    'disabled' => 'disabled'
]);

/**
 * 表单属性生成
 * @param $data
 * @return string
 * @date 2021/5/12 17:06
 */
function form_attr($data) {
    $attr = '';

    // name
    $attr .= empty($data['name']) ? ' name="" ' : " name=\"{$data['name']}\" ";

    // lay-skin
    if($data['type'] == 'checkbox' && !empty($data['lay-skin'])) {
        $attr .= ' lay-skin="'.$data['lay-skin'].'" ';
    }

    // lay-verify
    $attr .= empty($data['lay-verify']) ? '' : " lay-verify=\"{$data['lay-verify']}\" ";

    // lay-filter
    $attr .= empty($data['lay-filter']) ? '' : " lay-filter=\"{$data['lay-filter']}\" ";

    // lay-search
    if($data['type'] == 'select') {
        $attr .= !isset($data['lay-search']) ? '' : ' lay-search ';
    }

    // readonly
    $attr .= !isset($data['readonly']) ? '' : ' readonly="readonly" ';

    // disabled
    $attr .= !isset($data['disabled']) ? '' : ' disabled="disabled" ';

    // placeholder
    if(in_array($data['type'], ['text', 'textarea'])) {
        $action = in_array($data['text_type'], ['date', 'datetime']) ? '选择' : '输入';
        $attr .= empty($data['placeholder']) ? " placeholder=\"请{$action}{$data['title']}\" " : " placeholder=\"{$data['placeholder']}}\" ";
    }

    // type
    if(in_array($data['type'], ['checkbox', 'text'])) {
        if($data['type'] == 'text' && $data['text_type'] == 'number') {
            $attr .= ' type="number" ';
        }else if($data['type'] == 'checkbox') {
            $attr .= ' type="checkbox" ';
        }else if($data['type'] == 'text' && $data['text_type'] == 'password') {
            $attr .= ' type="password" ';
        }
    }
    return $attr;
}

/**
 * 生成表单文本输入框
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/3/11 15:09
 */
function form_text($data, $value='') {
    $tpl = '<input class="layui-input" {attr} {value}/>';

    $attr = form_attr($data);
    if(in_array($data['text_type'], ['date', 'datetime'])) {
        $rand = mt_rand(1, 999);
        $attr .= ' id="'.$data['name'].$rand.'" ';
        $js = "layui.laydate.render({elem:'#{$data['name']}{$rand}', type:'{$data['text_type']}'});".BR;
    }

    $html = str_replace([
        '{attr}',
        '{value}'
    ], [
        $attr,
        "value=\"{$value}\""
    ], $tpl);

    return [
        'html'  => $html,
        'js'    => isset($js) ? $js : '',
        'id'    => isset($data['id']) ? " id=\"{$data['id']}\"" : ''
    ];
}

/**
 * 生成表单多选框
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/5/12 14:42
 */
function form_checkbox($data, $value='') {
    $tpl = '<input class="layui-input" {attr}/>';
    $value = is_string($value) ? json_decode($value, true) : $value;
    if(empty($value)) $value = [];

    $html = '';
    $attr = form_attr($data);
    foreach ($data['option'] as $item) {
        $item_attr = $attr;
        $checked =  in_array($item['value'], $value) ? ' checked' : '';
        $item_attr .= " title=\"{$item['title']}\" value=\"{$item['value']}\" {$checked} ";
        $html .= str_replace([
            '{attr}',
        ], [
            $item_attr,
        ], $tpl);
    }

    return [
        'html'  => $html,
        'js'    => isset($js) ? $js : '',
        'id'    => isset($data['id']) ? " id=\"{$data['id']}\"" : ''
    ];
}

/**
 * 生成表单下拉框
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/3/11 15:59
 */
function form_select($data, $value='') {
    $tpl = '<select {attr}><option value="">{placeholder}</option>{option}</select>';
    $option = '';
    foreach ($data['option'] as $item) {
        $selected = $value !== '' && (string)$value == (string)$item['value'] ? ' selected' : '';
        $option .= '<option value="'.$item['value'].'"'.$selected.'>'.$item['title'].'</option>';
    }
    if(!empty($data['multiple'])) {
        $id = $data['name'].mt_rand(1,999);
        $html = "<div id='{$id}'></div>";
        $option = empty($data['option']) ? [] : $data['option'];
        foreach($option as &$item) {
            $item['name'] = $item['title'];
            unset($item['title']);
            if(isset($item['children'])) {
                foreach($item['children'] as &$v) {
                    $v['name'] = $v['title'];
                    unset($v['title']);
                }
            }
        }
        $option = json_encode($option, JSON_UNESCAPED_UNICODE);
        $value = empty($value) ? '[]' : json_encode($value);
        $js = "xmSelect.render({el: '#{$id}', filterable: true, data: {$option}, initValue: {$value}});";
    }else {
        $html = str_replace([
            '{attr}',
            '{placeholder}',
            '{option}'
        ], [
            form_attr($data),
            isset($data['placeholder']) ? $data['placeholder'] : ('请选择'.(isset($data['title']) ? $data['title'] : '')),
            $option
        ], $tpl);
    }
    return [
        'html'  => $html,
        'id'    => isset($data['id']) ? " id=\"{$data['id']}\"" : '',
        'js'    => isset($js) ? $js : ''
    ];
}

/**
 * 生成表单单选框
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/3/11 17:53
 */
function form_radio($data, $value='') {
    $tpl = '<input type="radio" {attr}/>';
    $html = '';
    $attr = form_attr($data);

    foreach ($data['option'] as $num=>$item) {
        $checked = $value !== '' && (string)$value == (string)$item['value'] ? ' checked' : '';
        if($num == 0 && $value === '') $checked = 'checked';
        $item_attr = $attr." title=\"{$item['title']}\" {$checked} value=\"{$item['value']}\" ";
        $html .= str_replace([
            '{attr}',
        ], [
            $item_attr,
        ], $tpl);
    }
    return [
        'html'  => $html,
        'id'    => isset($data['id']) ? " id=\"{$data['id']}\"" : '',
        'js'    => ''
    ];
}


/**
 * 生成表单地址下拉框
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/3/12 11:44
 */
function form_area($data, $value='') {
    $tpl = '<select name="{name}" class="{class}" data-value="{value}" lay-search{lay-filter}></select>';
    if(isset($data['area_type']) && $data['area_type'] == 'area') $data['area_type'] = 'county';
    $html = str_replace(['{name}', '{class}', '{value}', '{lay-filter}'], [
        isset($data['name']) ? $data['name'] : '',
        (isset($data['area_type']) ? $data['area_type'] : 'province').'-selector',
        $value,
        isset($data['lay-filter']) && $data['lay-filter'] ? ' lay-filter="'.$data['lay-filter'].'"' : ''
    ], $tpl);
    $rand = mt_rand(1, 9999);
    return [
        'html'  => $html,
        'js'    => $data['area_type'] == 'province' ? "layui.layarea.render({elem:'#area-picker{$rand}'});".BR : '',
        'id'    => $data['area_type'] == 'province' ? ' id="area-picker'.$rand.'""' : ''
    ];
}

/**
 * 生成表单上传插件
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/3/18 13:53
 */
function form_upload($data, $value='') {
    $tpl = '<input type="hidden" name="{name}" id="{name}" value=\'{value}\' />';
    $html = str_replace(['{name}', '{value}'], [
        isset($data['name']) ? $data['name'] : '',
        $value
    ], $tpl);
    $upload_type = isset($data['upload_type']) ? $data['upload_type'] : 'image';
    $rand = mt_rand(1, 9999);
    return [
        'html'  => $html,
        'js'    => "uploadInit('{$data['name']}', '{$upload_type}', '{$data['remark']}');".BR,
        'id'    => isset($data['id']) ? " id=\"{$data['id']}\"" : '',
    ];
}

/**
 * 生成表单富文本插件
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/4/13 15:34
 */
function form_editor($data, $value='') {
    $tpl = '<textarea name="{name}" id="{id}" style="width: 100%; height: {height};">{value}</textarea>';
    $id = $data['name'].mt_rand(1, 999);
    $html = str_replace(['{name}', '{value}', '{height}', '{id}'], [
        isset($data['name']) ? $data['name'] : '',
        $value,
        isset($data['height']) ? $data['height'] : '500px',
        $id
    ], $tpl);
    if($data['editor_type'] == 'umeditor') {
        $js = "UM.getEditor('{$id}', {imageUrl: UPLOAD_URL, imagePath:'', videoUrl: UPLOAD_URL})".BR;
    }else {
        $js = "UE.getEditor('{$id}')".BR;
    }
    return [
        'html'  => $html,
        'js'    => $js,
        'id'    => isset($data['id']) ? " id=\"{$data['id']}\"" : '',
    ];
}

/**
 * 生成表单textarea
 * @param $data
 * @param string $value
 * @return array
 * @date 2021/5/12 14:36
 */
function form_textarea($data, $value='') {
    $tpl = '<textarea class="layui-textarea" {attr}>{value}</textarea>';
    $html = str_replace([
        '{attr}',
        '{value}'
    ], [
        form_attr($data),
        $value
    ], $tpl);
    return [
        'html'  => $html,
        'js'    => '',
        'id'    => isset($data['id']) ? " id=\"{$data['id']}\"" : '',
    ];
}