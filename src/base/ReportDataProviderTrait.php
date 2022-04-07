<?php
/**
 * 数据提供器扩展方法
 */
namespace datacenter\base;

use Yii;
use yii\base\ModelEvent;

trait ReportDataProviderTrait
{
    protected $_models;
    protected $_keys;
    
    /**
     * 查询数据前事件名称
     */
    public static $EVENT_BEFORE_MODEL = 'beforeModel';
    
    /**
     * 查询数据后事件名称
     */
    public static $EVENT_AFTER_MODEL = 'afterModel';
    
    /**
     * 用于查询的数据模型
     */
    private $_searchModels;
    
    /**
     * 运行数据集时归集的报表模型
     */
    public $forReport;
    
    /**
     * 初始化分组字段
     */
    private function initColumns()
    {
        // 取出字段
        if($this->_alias_columns===null || $this->_id_columns===null){
            $this->_alias_columns = $this->_id_columns = [];
            foreach($this->sets['columns'] as $column){
                $this->_alias_columns[$column['v_alias']] = $this->_id_columns[$column['id']] = $column;
            }
        }
        return [$this->_id_columns, $this->_alias_columns];
    }
    
    /**
     * 获取字段
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
                list($idColumns, $aliasColumns) = $this->initColumns();
                if($idColumns && isset($idColumns[$columns])){
                    $columns = $idColumns[$columns]['v_fncolumn'] . ($isAs ? " as {$idColumns[$columns]['v_alias']}" : '');
                }elseif($aliasColumns && isset($aliasColumns[$columns])){
                    $columns = $aliasColumns[$columns]['v_fncolumn'] . ($isAs ? " as {$aliasColumns[$columns]['v_alias']}" : '');
                }
            }
        }
        
        return $columns;
    }
    
    /**
     * 返回数据查询条件的表单构建模型
     */
    public function getSearchModels()
    {
        if($this->_searchModels === null){
         $list = [];
            $params = Yii::$app->request->get("SysConfig",[]);
            $columns = $this->report ? $this->report->columns : $this->sets->columns;
            foreach($columns as $item){
                $colnmn = $this->report ? $item['setsCol'] : $item;
                if(!empty($item['formula']) || !empty($colnmn['formula'])) continue;
                
                if($colnmn && $colnmn['model_id'] && $colnmn['is_search']){
                    $_ = [
                        'config_type' => ($colnmn['type'] ? $colnmn['type'] : 'text'),
                        'value' => (isset($params[$item['v_alias']]) ? $params[$item['v_alias']] : $colnmn['v_search_defval']),
                        'attribute' => $item['v_alias'],
                        'label_name' => $colnmn['v_label'],
                        'config_params' => $colnmn['search_params'],
                        'v_config_params' => $colnmn['v_search_params'],
                        'v_config_ajax' => $colnmn['v_search_ajax'],
                    ];
                    $list[] = $_;
                }
            }
            $this->_searchModels = $list;
        }
        
        return $this->_searchModels;
    }
    
    /**
     * 设置数据查询条件的表单构建模型
     */
    public function setSearchModels($models)
    {
        $this->_searchModels = $models;
    }
    
    /**
     * 应用过滤条件
     */
    public function applySearchModels($params = null)
    {
        if($this->forReport && $params===false) return;
        if($params === false){
            // 默认条件
            $searchModels = $this->getSearchModels();
            $params = [];
            foreach($searchModels as $sModel){
                if(isset($sModel['value']) && (is_array($sModel['value']) || strlen($sModel['value'])>0)){
                    $params[$sModel['attribute']] = $sModel['value'];
                }
            }
        }

        if($params && ($colnmns = $this->report ? $this->report->columns : $this->sets->columns)){
            if($this->report){
                // 报表条件
                $mainSet = $this->report->getV_mainSet();
                $sets = $this->report->getV_sets();
                $setSearchParams = [];
                foreach($colnmns as $col){
                    if($col['formula']) continue;
                    if(isset($params[$col['v_alias']]) && (is_array($params[$col['v_alias']]) || strlen($params[$col['v_alias']])>0) && $col['setsCol']){
                        $setSearchParams[$col['set_id']][$col['setsCol']['v_alias']] = $params[$col['v_alias']];
                    }
                }
                
                foreach($sets as $set){
                    if(isset($setSearchParams[$set['id']]) && is_array($setSearchParams[$set['id']])){
                        $set->applySearchModels($setSearchParams[$set['id']]);
                        // 非主数据集的,查询出结果数据并入到主数据集条件
                        if($mainSet['id'] != $set['id']){
                            $set->filterSourceSearch($mainSet);
                        }
                    }
                }
            }else{
                // 数据集条件
                foreach($colnmns as $col){
                    if($col['formula']) continue;
                    $callFn = $col['fun'] ? 'having' : 'where';
                    if(isset($params[$col['v_alias']]) && (is_array($params[$col['v_alias']]) || strlen($params[$col['v_alias']])>0) && $col['model_id']){
                        switch($col['type'])
                        {
                            case 'date': // 日期
                                $this->$callFn($col['id'], $params[$col['v_alias']].' 00:00:00', '>=');
                                $this->$callFn($col['id'], $params[$col['v_alias']].' 23:59:59', '<=');
                                break;
                            case 'daterange': // 日期范围
                            case 'datetimerange': // 日期时间范围
                                if(strpos($params[$col['v_alias']], '至')!==false){
                                    list($startTime, $endTime) = explode('至', $params[$col['v_alias']]);
                                    if($col['type']=='daterange'){
                                        $startTime .= ' 00:00:00';
                                        $endTime .= ' 23:59:59';
                                    }
                                    $this->$callFn($col['id'], trim($startTime), '>=');
                                    $this->$callFn($col['id'], trim($endTime), '<=');
                                }
                                break;
                            case 'time': // 时间
                            case 'datetime': // 日期时间
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
                                $this->$callFn($col['id'], $params[$col['v_alias']]); // 直接匹配
                                break;
                            case 'text': // 文本框
                            case 'textarea': //  多行文本框
                            case 'mask': //  格式化文本
                            default: // 默认文本框
                                if(strpos($params[$col['v_alias']], '~')!==false){ // 范围查询
                                    list($start, $end) = explode('~', $params[$col['v_alias']]);
                                    $this->$callFn($col['id'], trim($start), '>=');
                                    $this->$callFn($col['id'], trim($end), '<=');
                                }elseif(preg_match('/^(<>|>=|>|<=|<|=)/', $params[$col['v_alias']], $matches)){
                                    $operator = $matches[1];
                                    $value = substr($params[$col['v_alias']], strlen($operator));
                                    $this->$callFn($col['id'], $value, $operator); // 指定操作
                                }else{
                                    $this->$callFn($col['id'], $params[$col['v_alias']], 'like'); // 模糊查询
                                }
                                break;
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * 过滤出报表的字段
     */
    public function filterColumns($values)
    {
        if(!$this->report) return $values;
        $data = [];
        $setLists = $this->report->v_sets;
        foreach($this->report->columns as $col){
            $set = isset($setLists[$col['set_id']]) ? $setLists[$col['set_id']] : null;
            $relation = $set ? $set['v_relation'] : null;
            if($set && $relation && $relation['rel_type']=='group'){
                $groupCols = $relation->getCache('getV_group_list', [$set, $this->report->v_cache_key]);
                if($groupCols && is_array($groupCols)){
                    foreach($groupCols as $k=>$v){
                        $data[$col['v_alias'].'_'.$k] = isset($values['_'][$col['set_id']][$k][$col['setsCol']['v_alias']])
                            ? $values['_'][$col['set_id']][$k][$col['setsCol']['v_alias']]
                            : $col['v_default_value'];
                    }
                }
            }else{
                $data[$col['v_alias']] = isset($values[$col['setsCol']['v_alias']])
                    ? $values[$col['setsCol']['v_alias']]
                    : (
                        isset($values['_'][$col['set_id']][$col['setsCol']['v_alias']])
                        ? $values['_'][$col['set_id']][$col['setsCol']['v_alias']]
                        : $col['v_default_value']
                    );
            }
        }
        return $data;
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
            $this->_models = $this->prepareModels();
            $this->afterModel();
        }
        
        if ($forcePrepare || $this->_keys === null) {
            $this->_keys = $this->prepareKeys($this->_models);
        }
    }
    
    /**
     * 获取汇总数据，预留
     */
    public function getSummary()
    {
        return [];
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
}