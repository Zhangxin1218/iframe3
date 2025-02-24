<?php


namespace app\__MODULE__\controller;

use app\common\extend\MgBase;

// 公共继承文件
class Base extends MgBase
{
    protected $redis_key; // redis目录名
    protected $table_param = []; // 渲染表格参数
    protected $form_param = []; // 渲染表单参数

    /**
     * 构造方法
     * Base constructor.
     */
    public function __construct() {
        // 声明管理端，此处不可删除修改移动
        $this->mg_module = '__MODULE__';
        // 父级初始化
        parent::__construct();

        // 定义是否POST请求
        defined('IS_POST') OR define('IS_POST', request()->isPost());
        // 定义是否导出EXCEL
        defined('IS_EXCEL') OR define('IS_EXCEL', !empty($_GET['export_excel']) ? true : false);

        // 计算项目名称
        $temp = explode("/", str_replace('\\', '/', substr(app()->getRootPath(), 0, strlen(app()->getRootPath()) - 1)));
        $this->redis_key = end($temp);
    }

    /**
     * 渲染table页面
     * @param array $params [page 是否开启分页true, false] [cols table展示字段同lay ui] [toolbar 工具条html]
     * @param bool $tpl [true-使用通用模板额外引用js, string-使用自定义模板, false-使用通用模板不引用js]
     * @return mixed
     * @date 2020/12/23 14:13
     */
    protected function render_table($params=[], $tpl=false) {
        // 解析toolbar按钮
        $data = []; $params = empty($params) ? $this->table_param : $params;
        $toolbar = '';
        if(!empty($params['toolbar'])) {
            foreach ($params['toolbar'] as $item) {
                $item = str_replace('layui-btn-xs', '', $item);
                $item = str_replace('layui-btn ', 'layui-btn layui-btn-sm ', $item);
                $toolbar .= $item;
            }
        }

        // 解析行内toolbar按钮
        if(!empty($params['toolbar_row'])) {
            if(empty($params['cols'])) $params['cols'] = [];
            $toolbar_row = implode('', $params['toolbar_row']);
            $data['toolbar_orw'] = $toolbar_row;
            $params['cols'][] = ['title' => '操作', 'width' => empty($params['toolbar_row_width']) ? 200 : $params['toolbar_row_width'], 'fixed' => 'right', 'toolbar' => '#toolbar_row'];
        }

        // 解析where
        $data['where'] = $data['js'] = '';
        if(!empty($params['where'])) {
            list($data['where'], $data['js']) = $this->parse_where($params['where'], !empty($params['export']) ? $params['export'] : false);
        }

        // 是否是框架展示
        $data['frame'] = empty($params['frame']) ? false : true;

        // 链接where条件
        $data['query'] = empty($params['query']) ? '' : $params['query'];

        // 参数赋值
        $data['table'] = json_encode([
            'limit'             => empty($params['limit']) ? 20 : $params['limit'],
            'limits'            => [10,15,20,30,40,50,60,70,80,90,100],
            'elem'              => '#table', // 渲染元素
            'height'            => !empty($params['height']) ? $params['height'] : 'full', // 表格高度
            'url'               => url(request()->controller().'/'.request()->action(), $_REQUEST), // 接口地址
            'page'              => $params['page'] ? true : false, // 是否分页
            'method'            => 'POST', // 请求方式
            'cols'              => !empty($params['cols']) ? [$params['cols']] : [], // 展示内容
            'toolbar'           => empty($toolbar) ? false : "<div>{$toolbar}</div>",
            'defaultToolbar'    => empty($toolbar) ? false : ['filter', 'print'],
        ], JSON_UNESCAPED_UNICODE);

        // 行内编辑传参地址
        $data['table_edit_url'] = !empty($params['edit_url']) ? $params['edit_url'] : '';

        // 赋值
        $this->assign($data);

        // 模板判断
        if($tpl === true) {
            $tpl = app()->getAppPath().request()->module().'/view/'.str_format(request()->controller()).'/'.request()->action().'.html';
            $this->assign('tpl', $tpl);
        }else if(is_string($tpl)) {
            return $this->fetch($tpl);
        }
        // 模板渲染
        return $this->fetch('common@common/data_table');
    }

    /**
     * 渲染form页面
     * @param array $params [渲染参数]
     * @param array $values [表单赋值]
     * @param bool $tpl [true-使用通用模板额外引用js, string-使用自定义模板, false-使用通用模板不引用js]
     * @param bool $frame [是否是iframe内访问]
     * @return string
     * @date 2021/3/12 10:32
     */
    protected function render_form($params=[], $values=[], $tpl=false, $frame=true) {
        $html = ''; $js=''; $params = empty($params) ? $this->form_param : $params;
        foreach ($params as $data) {
            $html .= '<div class="layui-form-item"{id} {hidden}>';
            if(is_array($data[0])) {
                // 解析inline元素
                foreach ($data as $k=>$item) {
                    if(!empty($item['title'])) $html .= str_replace('{title}', $item['title'], '<div class="layui-form-label">{title}</div>');
                    if($k == 0) $html .= '<div class="layui-input-block">';
                    $fun = "form_{$item['type']}";
                    $res = is_callable($fun) ? $fun($item, isset($item['name']) && isset($values[$item['name']]) ? $values[$item['name']] : '') : '';
                    $js .= isset($res['js']) ? $res['js'] : '';
                    $hidden = isset($item['hidden']) && $item['hidden'] ? 'style="display: none"' : '';
                    $html = str_replace(['{id}', '{hidden}'], [$res['id'], $hidden], $html);
                    $html .= '<div class="layui-input-inline">'.$res['html'].'</div>';
                    if(!empty($item['remark'])) $html .= '<div class="layui-input-inline" style="width: auto;"><div class="layui-form-mid layui-word-aux">'.$item['remark'].'</div></div>';
                }
                $html .= '</div>';
            }else {
                // 解析block元素
                $html .= str_replace('{title}', isset($data['title']) ? $data['title'] : '', '<div class="layui-form-label">{title}</div>');
                $fun = "form_{$data['type']}";
                $res = is_callable($fun) ? $fun($data, isset($data['name']) && isset($values[$data['name']]) ? $values[$data['name']] : '') : '';
                $js .= isset($res['js']) ? $res['js'] : '';
                $hidden = isset($data['hidden']) && $data['hidden'] ? 'style="display: none"' : '';
                $html = str_replace(['{id}', '{hidden}'], [$res['id'], $hidden], $html);
                $html .= '<div class="layui-input-block">'.$res['html'].'</div>';
            }
            if(!empty($data['remark']) && $data['type'] != 'upload') $html .= '<div class="layui-input-block layui-form-intro"><div class="layui-form-mid layui-word-aux">'.$data['remark'].'</div></div>';
            $html .= '</div>';
        }
        $this->assign([
            'html'  => $html,
            'js'    => $js,
            'frame' => $frame
        ]);
        if($tpl === true) {
            $tpl = app()->getAppPath().request()->module().'/view/'.str_format(request()->controller()).'/'.request()->action().'.html';
            $this->assign('tpl', $tpl);
        }else if(is_string($tpl)) {
            return $this->fetch($tpl);
        }
        return $this->fetch('common@common/data_form');
    }

    /**
     * 解析where参数为表单
     * @param $params
     * @param $export
     * @return array
     * @date 2020/12/24 11:34
     */
    private function parse_where($params, $export=false) {
        $html = [
            0 => '<form class="layui-search layui-form">{content}</form>',
            1 => '<div class="layui-col-md3"><label>{title}</label><div class="layui-input-inline">{input}</div></div>',
            2 => '<input type="text" class="layui-input" name="{name}" value="" placeholder="{placeholder}" {readonly} {id}>',
            3 => '<select name="{name}" lay-search lay-filter="{name}">{option}</select>',
            4 => '<option value="{value}">{title}</option>',
            5 => '<div class="layui-col-md9" id="area-picker">
                        <div class="layui-col-md4">
                            <label>省份</label>
                            <div class="layui-input-inline">
                                <select name="province" class="province-selector" data-value="" lay-filter="province-1" lay-search>
                                    <option value="">请选择省</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-col-md4">
                            <label>城市</label>
                            <div class="layui-input-inline">
                                <select name="city" class="city-selector" data-value="" lay-filter="city-1" lay-search>
                                    <option value="">请选择市</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-col-md4">
                            <label>区域</label>
                            <div class="layui-input-inline">
                                <select name="area" class="county-selector" data-value="" lay-filter="county-1" lay-search>
                                    <option value="">请选择区</option>
                                </select>
                            </div>
                        </div>
                    </div>',
            6 => 'laydate.render({elem: "#{id}",type:"{type}"});',
            7 => 'layarea.render({elem: "#area-picker",});'
        ];
        $where = $html[0];
        $js = $content = '';
        foreach($params as $name=>$row) {
            if($row['type'] == 'input') {
                $placeholder = "请输入{$row['title']}";
            }else {
                $placeholder = "请选择{$row['title']}";
            }
            if($row['type'] == 'input') {
                $content .= str_replace(['{title}','{input}'], [
                    $row['title'],
                    str_replace(['{name}', '{placeholder}', '{readonly}', '{id}'], [$name, $placeholder, '', ''], $html[2])
                ], $html[1]);
            }else if($row['type'] == 'select') {
                $option = str_replace(['{value}', '{title}'], ['', $placeholder], $html[4]);
                foreach($row['option'] as $v) {
                    $option .= str_replace(['{value}', '{title}'], [$v['value'], $v['title']], $html[4]);
                }
                $content .= str_replace(['{title}', '{input}'], [$row['title'], str_replace(['{name}', '{option}'], [$name, $option], $html[3])], $html[1]);
            }else if(in_array($row['type'], ['date', 'datetime'])) {
                $content .= str_replace(['{title}','{input}'], [
                    $row['title'],
                    str_replace(['{name}', '{placeholder}', '{readonly}', '{id}'], [$name, $placeholder, 'readonly', 'id="'.$name.'"'], $html[2])
                ], $html[1]);
                $js .= str_replace(['{id}', '{type}'], [$name, $row['type']], $html[6]);
            }else if($row['type'] == 'province,city,area') {
                $content .= $html[5];
                $js .= $html[7];
            }
        }
        $content .= '<div class="layui-col-md3 layui-search-submit"><div class="layui-input-inline"><button class="layui-btn layui-btn-normal" type="button" onclick="table_search(this)">搜索</button>{export}</div></div><div class="layui-clear"></div>';
        $export_html = $export ? '<button class="layui-btn layui-btn-danger" type="button" onclick="export_excel(this)">导出</button>' : '';
        $content = str_replace('{export}', $export_html, $content);
        $where = str_replace('{content}', $content, $where);
        return [$where, $js];
    }
}