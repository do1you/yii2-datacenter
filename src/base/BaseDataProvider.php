<?php
/**
 * 数据集、报表数据提供器基类
 */
namespace datacenter\base;

use Yii;
use yii\base\ModelEvent;

abstract class BaseDataProvider extends \yii\data\ActiveDataProvider implements ReportDataInterface
{
    
    /**
     * 数据集
     */
    public $sets;
    
    /**
     * 数据报表
     */
    public $report;
    
    /**
     * 运行数据集时，所归集的报表
     */
    public $forReport;
    
    /**
     * 数据缓存
     */
    protected $_models;
    
    /**
     * 主键缓存
     */
    protected $_keys;
    
    /**
     * 汇总数据缓存
     */
    protected $_summarys;
    
    /**
     * 查询数据前事件名称
     */
    public static $EVENT_BEFORE_MODEL = 'beforeModel';
    
    /**
     * 查询数据后事件名称
     */
    public static $EVENT_AFTER_MODEL = 'afterModel';
    
    /**
     * 汇总数据前事件名称
     */
    public static $EVENT_BEFORE_SUMMARY = 'beforeSummary';
    
    /**
     * 汇总数据后事件名称
     */
    public static $EVENT_AFTER_SUMMARY = 'afterSummary';

    /**
     * 用于查询的数据模型缓存
     */
    private $_searchModels;
    
    /**
     * 用于查询的数据值缓存
     */
    private $_searchValues;
    
    /**
     * 用于汇总数据模型缓存
     */
    private $_summaryModels;
    
    /**
     * 用于分组数据集字段/报表字段的缓存
     */
    private $_groupColumns;
    
    /**
     * 合并数据集缓存
     */
    private $_unionSets = [];
    
    /**
     * 计算公式模板
     */
    private $_replace_params;
    
    /**
     * 初始化
     */
    public function init()
    {
    }
    
    /**
     * 初始化ID/别名分组字段
     */
    protected function initColumns($key=null)
    {
        if($this->_groupColumns===null){
            $labels2 = $alias2 = $ids2 = $labels1 = $alias1 = $ids1 = $labels = $alias = $ids = [];
            foreach($this->sets['columns'] as $column){
                $labels[$column['v_label']] = $alias[$column['v_alias']] = $ids[$column['id']] = $column;
            }
            
            if($this->report){
                foreach($this->report['columns'] as $column){
                    $labels1[$column['v_label']] = $alias1[$column['v_alias']] = $ids1[$column['id']] = $column;
                }
            }
            
            if($this->forReport && $this->forReport!==true){
                foreach($this->forReport['columns'] as $column){
                    $labels2[$column['v_label']] = $alias2[$column['v_alias']] = $ids2[$column['id']] = $column;
                }
            }
            
            $this->_groupColumns = [
                'setIdCols' => $ids,
                'setAliasCols' => $alias,
                'setLabelCols' => $labels,
                'reportIdCols' => $ids1,
                'reportAliasCols' => $alias1,
                'reportLabelCols' => $labels1,
                'forIdCols' => $ids2,
                'forAliasCols' => $alias2,
                'forLabelCols' => $labels2,
            ];
        }
        if($key && isset($this->_groupColumns[$key])) return $this->_groupColumns[$key];
        return $this->_groupColumns;
    }
    
    /**
     * 根据ID或别名字段获取数据集字段
     * 参数：别名或ID；值：传值时根据字段进行编排值；是否输出as别名的字段
     */
    public function getColumns($columns, &$values = null, $isAs = false)
    {
        if($this->sets && $this->sets['columns']){
            if(is_array($columns)){
                foreach($columns as $k=>$col){
                    $columns[$k] = $this->getColumns($col, $values, $isAs);
                }
                if($values && is_array($values)){
                    foreach($values as $k=>$value){
                        if(is_array($value)){
                            $values[$k] = array_combine($columns, $value);
                        }
                    }
                }
            }else{
                $idColumns = $this->initColumns('setIdCols');
                $aliasColumns = $this->initColumns('setAliasCols');
                if($idColumns && isset($idColumns[$columns])){
                    $column = $idColumns[$columns];
                }elseif($aliasColumns && isset($aliasColumns[$columns])){
                    $column = $aliasColumns[$columns];
                }
                if(!empty($column)){
                    if($isAs && $isAs!==true && isset($column[$isAs])){
                        $columns = $column[$isAs];
                    }elseif($this->sets['set_type']=='model'){
                        $columns = $column['v_fncolumn'] . ($isAs===true ? " as {$column['v_alias']}" : ''); // 模型输出v_fncolumn
                    }else{
                        $columns = $column['name']; // 非模型输出name字段
                    }
                }
            }
        }
        
        return $columns;
    }
    
    /**
     * 返回数据查询表单的key=>value对应值
     */
    public function getSearchValues()
    {
        if($this->_searchValues === null){
            $list = $this->getSearchModels();
            $this->_searchValues = [];
            foreach($list as $item){
                $this->_searchValues[$item['label_name']] = is_array($item['value']) ? implode(',', $item['value']) : $item['value'];
            }
        }
        
        return $this->_searchValues;
    }
    
    /**
     * 返回数据查询条件的表单构建模型
     */
    public function getSearchModels()
    {
        if($this->_searchModels === null){
            $list = [];
            $params = Yii::$app->request->get("SysConfig",[]);
            $forUserModel = $this->report ? $this->report['forUserModel'] : $this->sets['forUserModel'];
            if($forUserModel && ($search_values = $forUserModel['v_search_values'])){
                $params = array_merge($search_values,$params);
            }
            $columns = $this->report ? $this->report->columns : $this->sets->columns;
            foreach($columns as $item){
                $colnmn = $this->report ? $item['setsCol'] : $item;
                // if(!empty($item['formula']) || !empty($colnmn['formula'])) continue;
                
                if($colnmn && $colnmn['is_search']){ // && ($colnmn['model_id'] || $colnmn['sets']['set_type']!='model')
                    $_ = [
                        'config_type' => ($colnmn['type'] ? $colnmn['type'] : 'text'),
                        'value' => (isset($params[$item['v_alias']]) ? $params[$item['v_alias']] : $colnmn['v_search_defval']),
                        'attribute' => $item['v_alias'],
                        'label_name' => $item['v_label'],
                        'config_params' => $colnmn['search_params'],
                        'v_config_params' => $colnmn['v_search_params'],
                        'v_config_ajax' => $colnmn['v_search_ajax'],
                    ];
                    $list[] = $_;
                    // 反向查询
                    if($colnmn['is_back_search']){
                        $attribute = '-'.$item['v_alias'];
                        $label = "不含{$item['v_label']}";
                        $_ = [
                            'config_type' => ($colnmn['type'] ? $colnmn['type'] : 'text'),
                            'value' => (isset($params[$attribute]) ? $params[$attribute] : ''), // $colnmn['v_search_defval']
                            'attribute' => $attribute,
                            'label_name' => $label,
                            'config_params' => $colnmn['search_params'],
                            'v_config_params' => $colnmn['v_search_params'],
                            'v_config_ajax' => $colnmn['v_search_ajax'],
                        ];
                        $list[] = $_;
                    }
                }
            }
            $this->_searchModels = $list;
        }
        
        return $this->_searchModels;
    }
    
    /**
     * 应用过滤条件
     */
    public function applySearchModels($params = null)
    {
        if($this->forReport && $params===false) return; // 属于数据集初始化的应用条件跳过
        if($params === false){
            // 默认条件
            $searchModels = $this->getSearchModels();
            $labelParams = $params = [];
            foreach($searchModels as $sModel){
                if(isset($sModel['value']) && (is_array($sModel['value']) || strlen($sModel['value'])>0)){
                    $labelParams[$sModel['label_name']] = $params[$sModel['attribute']] = $sModel['value'];
                }
            }
        }
        
        if(($colnmns = $this->report ? $this->report->columns : $this->sets->columns)){
            if($this->report){
                // 报表条件
                $mainSet = $this->report->getV_mainSet();
                $sets = $this->report->getV_sets();
                $setSearchParams = [];
                foreach($colnmns as $col){
                    if($col['formula']) continue;
                    foreach([$col['v_alias'], '-'.$col['v_alias']] as $attribute){
                        if(isset($params[$attribute]) && (is_array($params[$attribute]) || strlen($params[$attribute])>0) && $col['setsCol']){
                            $is_back_search = substr($attribute,0,1)=='-';
                            $setSearchParams[$col['set_id']][($is_back_search ? '-' : '').$col['setsCol']['v_alias']] = $params[$attribute];
                        }
                    }
                }
                
                foreach($sets as $index=>$set){
                    $searchParams = (isset($setSearchParams[$set['id']]) && is_array($setSearchParams[$set['id']])) ? $setSearchParams[$set['id']] : [];
                    
                    if($searchParams){
                        $set->applySearchModels($searchParams);
                        // 非主数据集的,查询出结果数据并入到主数据集条件
                        if($mainSet !== $set){
                            $set->filterSourceSearch($mainSet);
                        }
                    }
                    
                    // 同标签条件带入
                    $label_values = [];
                    if($set['columns']){
                        foreach($set['columns'] as $col){
                            if($col['formula']) continue;
                            foreach([$col['v_label'], '不含'.$col['v_label']] as $attribute){
                                if(isset($labelParams[$attribute]) && (is_array($labelParams[$attribute]) || strlen($labelParams[$attribute])>0)){
                                    $is_back_search = strpos($attribute, '不含')!==false;
                                    $label_values[($is_back_search ? '-' : '').$col['v_alias']] = $labelParams[$attribute];
                                }
                            }
                        }
                    }
                    
                    // 带入用户过滤条件
                    if($label_values || ($set['forUserModel'] && ($search_values = $set['forUserModel']['v_search_values']))){
                        $searchParams = array_merge((is_array($search_values) ? $search_values : []),$label_values,$searchParams);
                        $set->applySearchModels($searchParams);
                    }
                    
                }
            }else{
                // 数据集条件
                foreach($colnmns as $col){
                    if($col['formula']) continue;
                    foreach([$col['v_alias'],'-'.$col['v_alias']] as $attribute){
                        $is_back_search = substr($attribute,0,1)=='-';
                        if(empty($col['is_back_search']) && $is_back_search) continue; // 没有反向查询的跳过
                        
                        $callFn = $col['v_isfn'] ? 'having' : 'where';
                        if(isset($params[$attribute]) && (is_array($params[$attribute]) || strlen($params[$attribute])>0) && ($col['model_id'] || $this->sets['set_type']!='model')){
                            switch($col['type'])
                            {
                                case 'date': // 日期
                                    if($is_back_search){
                                        $this->$callFn(['or',
                                            [$col['id'], $params[$attribute], '<'],
                                            [$col['id'], $params[$attribute].' 23:59:59', '>'],
                                        ]);
                                    }else{
                                        $this->$callFn($col['id'], $params[$attribute], '>=');
                                        $this->$callFn($col['id'], $params[$attribute].' 23:59:59', '<=');
                                    }
                                    break;
                                case 'daterange': // 日期范围
                                case 'datetimerange': // 日期时间范围
                                    if(strpos($params[$attribute], '至')!==false){
                                        list($startTime, $endTime) = explode('至', $params[$attribute]);
                                        if($col['type']=='daterange'){
                                            $startTime = trim($startTime);
                                            $endTime = trim($endTime).' 23:59:59';
                                        }
                                        if($is_back_search){
                                            $this->$callFn(['or',
                                                [$col['id'], trim($startTime), '<'],
                                                [$col['id'], trim($endTime), '>'],
                                            ]);
                                        }else{
                                            $this->$callFn($col['id'], trim($startTime), '>=');
                                            $this->$callFn($col['id'], trim($endTime), '<=');
                                        }
                                    }
                                    break;
                                case 'time': // 时间
                                case 'datetime': // 日期时间
                                case 'dateyear': // 年份
                                case 'checkbox': // 复选框
                                case 'select': // 下拉框
                                case 'selectmult': // 下拉多选框
                                case 'select2': // 升级下拉框
                                case 'select2mult': // 升级下拉框多选
                                case 'ddselect2': // 数据字典
                                case 'ddmulti': // 数据字典多选
                                case 'dd': // 数据字典
                                case 'ddselect2multi': // 数据字典多选
                                case 'selectajax': // 下拉异步
                                case 'selectajaxmult': // 下拉异步多选框
                                    if(is_array($params[$attribute]) || is_array($col['id']) || is_object($params[$attribute])){
                                        $this->$callFn($col['id'], $params[$attribute], ($is_back_search ? 'not in' : 'in')); // 直接匹配
                                    }else{
                                        $this->$callFn($col['id'], $params[$attribute], ($is_back_search ? '!=' : '=')); // 直接匹配
                                    }
                                    
                                    break;
                                case 'text': // 文本框
                                case 'textarea': //  多行文本框
                                case 'mask': //  格式化文本
                                default: // 默认文本框
                                    if(strpos($params[$attribute], '~')!==false){ // 范围查询
                                        list($start, $end) = explode('~', $params[$attribute]);
                                        if($is_back_search){
                                            $this->$callFn(['or',
                                                [$col['id'], trim($start), '<'],
                                                [$col['id'], trim($end), '>'],
                                            ]);
                                        }else{
                                            $this->$callFn($col['id'], trim($start), '>=');
                                            $this->$callFn($col['id'], trim($end), '<=');
                                        }
                                    }elseif(preg_match('/^(<>|!=|>=|>|<=|<|=)/', $params[$attribute], $matches)){
                                        $operator = $matches[1];
                                        $value = substr($params[$attribute], strlen($operator));
                                        if($is_back_search){
                                            $notOperators = [
                                                '<>' => '=',
                                                '!=' => '=',
                                                '>' => '<=',
                                                '>=' => '<',
                                                '<' => '>=',
                                                '<=' => '>',
                                                '=' => '!=',
                                            ];
                                            if(isset($notOperators[$operator])){
                                                $this->$callFn($col['id'], $value, $notOperators[$operator]); // 指定操作
                                            }
                                        }else{
                                            $this->$callFn($col['id'], $value, $operator); // 指定操作
                                        }
                                    }else{
                                        $this->$callFn($col['id'], $params[$attribute], ($is_back_search ? 'not like' : 'like')); // 模糊查询
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * 返回数据汇总的字段
     */
    public function getSummaryModels()
    {
        if($this->_summaryModels === null){
            $list = [];
            $columns = $this->report ? $this->report->columns : $this->sets->columns;
            foreach($columns as $item){
                $colnmn = $this->report ? $item['setsCol'] : $item;
                if(!empty($item['formula']) || !empty($colnmn['formula'])) continue;
                
                if($colnmn && $colnmn['is_summary'] && ($colnmn['model_id'] || $colnmn['sets']['set_type']!='model')){
                    $v_column = $colnmn['sets']['set_type']!='model' ? $colnmn->name : $colnmn->v_fncolumn;
                    if($colnmn->v_isfn){
                        $list[] = new \yii\db\Expression("{$v_column} as {$colnmn->v_alias}");
                    }elseif(
                        !in_array(strtolower(substr($v_column,-2)), ['id','no'])
                        && !in_array(strtolower(substr($v_column,-4)), ['type','flag'])
                    ){
                        if(in_array($colnmn['sets']['set_type'],['sql','model'])){
                            $list[] = new \yii\db\Expression("SUM({$v_column}) as {$colnmn->v_alias}");
                        }else{
                            $list[] = $v_column;
                        }                        
                    }
                }
            }
            $this->_summaryModels = $list;
        }
        
        return $this->_summaryModels;
    }
    
    /**
     * 过滤出报表的字段
     */
    public function filterColumns($values, $isSummery = false)
    {
        if(!$this->report) return $values;
        $data = [];
        $setLists = $this->report->v_sets;
        $labelColmns = \yii\helpers\ArrayHelper::map($this->report->getV_columns(), 'label', 'name');
        foreach($this->report->columns as $col){
            if($isSummery && $col['setsCol'] && $col['setsCol']['is_summary']!='1') continue;
            $sIndex = ($col['user_set_id'] && $col['userSets']) ? '-'.$col['user_set_id'] : $col['set_id'];
            $set = isset($setLists[$sIndex]) ? $setLists[$sIndex] : null;
            $relation = $set ? $set['v_relation'] : null;
            if($set && $relation && $relation['rel_type']=='group'){
                $groupCols = $relation->getV_group_list($set);
                if($groupCols && is_array($groupCols)){
                    foreach($groupCols as $k=>$v){
                        $index = $col['v_alias'].'_'.$k;
                        $data[$index] = isset($values['_'][$sIndex][$k][$col['setsCol']['v_alias']])
                            ? $values['_'][$sIndex][$k][$col['setsCol']['v_alias']]
                            : ($isSummery ? '' : $col['v_default_value']);
                        
                        $data[$index] = $this->formatRespFun($col['resp_fun'],$data[$index]);
                    }
                }
            }else{
                $index = $col['v_alias'];
                $data[$index] = isset($values[$col['setsCol']['v_alias']])
                    ? $values[$col['setsCol']['v_alias']]
                    : (
                        isset($values['_'][$sIndex][$col['setsCol']['v_alias']])
                        ? $values['_'][$sIndex][$col['setsCol']['v_alias']]
                        : ($isSummery ? '' : $col['v_default_value'])
                    );
                
                $data[$index] = $this->formatRespFun($col['resp_fun'],$data[$index]);
            }
            
            if($set && $relation && $relation['rel_type']=='union'){
                if(strlen($data[$labelColmns[$col['v_label']]])<=0){
                    $index = $labelColmns[$col['v_label']];
                    $data[$index] = $this->formatRespFun($col['resp_fun'],$data[$col['v_alias']]);
                }
                $this->_unionSets[$set['id']] = $set;
            }
        }
        return $this->formatValue($data);
    }
    
    /**
     * 过滤出数据集的字段
     */
    public function filterSetsColumns($values, $isSummery = false)
    {
        if(!$this->sets) return $values;
        
        $data = [];
        list($formatFormulas, $formatLabels, $formatDd) = $this->formatValueTpl();
        foreach($this->sets->columns as $col){
            $key = $col['v_alias'];
            $v = isset($values[$key])
                ? $values[$key]
                : (isset($values[$col['name']]) ? $values[$col['name']] : ($isSummery ? '' : $col['v_default_value']));
            
            if(isset($formatDd[$key]) && isset($formatDd[$key]['type']) && strlen($v)>0){
                switch($formatDd[$key]['type']){
                    // 数据字典
                    case 'dd':
                    case 'ddmulti':
                    case 'ddselect2':
                    case 'ddselect2multi':
                        $value = !empty($formatDd[$key]['search_params']) ? \webadmin\modules\config\models\SysLdItem::dd($formatDd[$key]['search_params'],$v) : null;
                        $v = $value!==null ? $value : $v;
                        break;
                        // 下拉选项
                    case 'select':
                    case 'selectmult':
                    case 'select2':
                    case 'select2mult':
                        $v_search_params = $formatDd[$key]['v_search_params'];
                        $value = is_array($v_search_params)&&isset($v_search_params[$v]) ? $v_search_params[$v] : null;
                        $v = $value!==null ? $value : $v;
                        break;
                }
            }elseif(strlen($v)<=0){
                $v = '&nbsp;';
            }elseif(is_numeric($v) && !preg_match("/\d{8,50}/",$v) && (substr($v,0,2)=='0.' || substr($v,0,1)!='0')){
                $v = floatval($v);
            }
            $data[$key] = $this->formatRespFun($col['resp_fun'],$v);
        }
        
        return $this->formatValue($data);
    }
    
    // 返回输出函数处理过的内容
    public function formatRespFun($respFun,$val)
    {
        if($respFun){
            list($fun,$params) = explode(':',$respFun);
            $params = $params ? explode(',',$params) : [];
            array_unshift($params,$val);
            if(strpos($fun, '.')===false){
                if(function_exists($fun)) $val = call_user_func_array($fun,$params);
            }else{
                list($class, $function) = explode('.',$fun);
                if(class_exists($class) && method_exists($class, $function)) $val = call_user_func_array([$class, $function],$params);
            }
        }
        return $val;
    }
    
    // 返回格式化计算公式参数
    public function formatValueTpl()
    {
        // 一次性匹配出模板
        if($this->_replace_params === null
            || !isset($this->_replace_params['format_formulas'])
            || !isset($this->_replace_params['format_labels'])
            || !isset($this->_replace_params['format_dd'])
        ){
                $formatDd = $formatLabels = $formatFormulas = [];
                $columns = $this->report ? $this->report->columns : $this->sets->columns;
                foreach($columns as $col){
                    if($col['formula']){
                        $formatFormulas[$col['v_alias']] = $col['formula'];
                    }
                    if(isset($col['type']) && in_array($col['type'],['dd', 'ddmulti', 'select', 'selectmult', 'select2', 'select2mult', 'ddselect2', 'ddselect2multi'])){
                        $formatDd[$col['v_alias']] = $col;
                    }
                    
                    $formatLabels[$col['v_format_label']] = "\${$col['v_alias']}";
                }
                $this->_replace_params['format_formulas'] = $formatFormulas;
                $this->_replace_params['format_labels'] = $formatLabels;
                $this->_replace_params['format_dd'] = $formatDd;
        }
        return [$this->_replace_params['format_formulas'],$this->_replace_params['format_labels'],$this->_replace_params['format_dd']];
    }
    
    // 格式化计算公式字符串
    public function formatValue($values)
    {
        list($formatFormulas, $formatLabels, $formatDd) = $this->formatValueTpl();
        
        // 公式数据
        if($formatFormulas && is_array($formatFormulas)){
            $search = $formatLabels ? array_keys($formatLabels) : [];
            $replace = $formatLabels ? array_values($formatLabels) : [];
            $searchParams = $this->getSearchValues();
            if($searchParams && is_array($searchParams)) extract($searchParams, EXTR_OVERWRITE);
            foreach($formatFormulas as $key=>$formula){
                try {
                    $values[$key] = '';
                    $formula = str_replace($search, $replace, $formula);
                    extract($values, EXTR_OVERWRITE);
                    $formula = preg_replace('/[{][^}]*?[}]/','$null',$formula);
                    //var_dump('$values[$key] = '.$formula.';');
                    eval('$values[$key] = '.$formula.';');
                    $values[$key] = (string)$values[$key];
                    if(strlen($values[$key])<=0) $values[$key] = '&nbsp;';
                }catch(\Exception $e) {
                }
            }
        }
        
        return $values;
    }
    
    // 格式化字段查询数据
    protected function formatColumns($columns)
    {
        foreach($columns as $key=>$items){
            if(is_array($items) && !empty($items[0]) && isset($items[1])){
                $columns[$key] = [
                    (isset($items[2]) ? $items[2] : '='),
                    $this->getColumns($items[0]),
                    $items[1],
                ];
            }
        }
        return $columns;
    }
    
    /**
     * 处理数据前触发事件
     */
    public function beforeModel()
    {
        $event = new ModelEvent();
        $this->sets->trigger(self::$EVENT_BEFORE_MODEL, $event);
        
        return $event->isValid;
    }
    
    /**
     * 处理数据后触发事件
     */
    public function afterModel()
    {
        $this->sets->trigger(self::$EVENT_AFTER_MODEL, new ModelEvent());
    }
    
    /**
     * 处理数据，增加事件
     */
    public function prepare($forcePrepare = false)
    {
        if ($forcePrepare || $this->_models === null) {
            if (!$this->beforeModel()) {
                return false;
            }
            if($this->report){
                $this->sets->setPagination($this->getPagination());
                $this->sets->setSort($this->getSort());
            }
            $this->_models = $this->prepareModels();
            $this->afterModel();
        }
        
        if ($forcePrepare || $this->_keys === null) {
            $this->_keys = $this->prepareKeys($this->_models);
        }
    }
    
    /**
     * 获取数据
     */
    public function getModels()
    {
        $this->prepare();
        return $this->_models;
    }
    
    /**
     * 设置数据
     */
    public function setModels($models)
    {
        $this->_models = $models;
    }
    
    /**
     * 处理汇总数据前触发事件
     */
    public function beforeSummary()
    {
        $event = new ModelEvent();
        $this->sets->trigger(self::$EVENT_BEFORE_SUMMARY, $event);
        
        return $event->isValid;
    }
    
    /**
     * 处理汇总数据后触发事件
     */
    public function afterSummary()
    {
        $this->sets->trigger(self::$EVENT_AFTER_SUMMARY, new ModelEvent());
    }
    
    /**
     * 处理汇总数据
     */
    public function prepareSummary($forcePrepare = false)
    {
        if ($forcePrepare || $this->_summarys === null) {
            if (!$this->beforeSummary()) {
                return false;
            }
            $this->_summarys = $this->summaryModels();
            foreach($this->_summarys as $key=>$val){
                if(is_array($val)){
                    foreach($val as $k=>$v){
                        if(empty($v) || $v=='&nbsp;' || !is_numeric($v)){
                            unset($val[$k]);
                        }
                    }
                    if(empty($val)){
                        unset($this->_summarys[$key]);
                    }
                }elseif(empty($val) || $val=='&nbsp;' || !is_numeric($val)){
                    unset($this->_summarys[$key]);
                }
            }
            $this->afterSummary();
        }
    }
    
    /**
     * 计算汇总数据
     */
    protected function summaryModels()
    {
        return [];
    }
    
    /**
     * 获取汇总数据
     */
    public function getSummary()
    {
        $this->prepareSummary();
        return $this->_summarys;
    }
    
    /**
     * 设置汇总数据
     */
    public function setSummary($value)
    {
        $this->_summarys = $value;
    }
    
    /**
     * 获取总的记录数
     */
    protected function prepareTotalCount()
    {
        if($this->report){
            $total = $this->sets->getTotalCount();
            // 包含合并的数据集，进行累加
            if($this->_unionSets){
                foreach($this->_unionSets as $set){
                    $total += $set->getTotalCount();
                }
            }
            return $total;
        }
        
        return parent::prepareTotalCount();
    }
    
    /**
     * 获取主键
     */
    public function getKeys()
    {
        $this->prepare();
        
        return $this->_keys;
    }
    
    /**
     * 设置主键
     */
    public function setKeys($keys)
    {
        $this->_keys = $keys;
    }
    
    /**
     * 刷新数据
     */
    public function refresh()
    {
        $this->setTotalCount(null);
        $this->_models = null;
        $this->_keys = null;
    }
    
    /**
     * 添加查询字段
     */
    public function select($columns)
    {
        return $this;
    }
    
    /**
     * 添加查询条件
     */
    public function where($columns, $values, $op = false)
    {
        return $this;
    }
    
    /**
     * 添加过滤值查询条件
     */
    public function filterWhere($columns, $values, $op = false)
    {
        if(is_array($values) || strlen($values)){
            return $this->where($columns, $values, $op);
        }
        return $this;
    }
    
    /**
     * 添加分组条件
     */
    public function having($columns, $values, $op = false)
    {
        return $this;
    }
    
    /**
     * 添加过滤值分组查询条件
     */
    public function filterHaving($columns, $values, $op = false)
    {        
        if(is_array($values) || strlen($values)){
            return $this->having($columns, $values, $op);
        }
        return $this;
    }
    
    /**
     * 添加分组
     */
    public function group($columns)
    {
        return $this;
    }
    
    /**
     * 添加排序
     */
    public function order($columns)
    {
        return $this;
    }
}