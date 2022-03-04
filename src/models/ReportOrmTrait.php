<?php
/**
 * 数据报表ORM集合动作
 */

namespace datacenter\models;

use Yii;

trait ReportOrmTrait
{
    /**
     * 关联所有的字段
     */
    private $_setColumns;
    
    /**
     * 冻结列数
     */
    private $_frozen_num;
    
    // 返回格式化所有字段
    public function getV_columns()
    {
        if($this->_setColumns===null){
            $this->_setColumns = \yii\helpers\ArrayHelper::map($this->columns, 'v_alias', 'v_label');
        }
        
        return $this->_setColumns;
    }
    
    // 返回冻结列数
    public function getV_frozen()
    {
        if($this->_frozen_num === null){
            $num = 0;
            foreach($this->columns as $colItem){
                if($colItem['is_frozen']) $num++;
            }
            $this->_frozen_num = $num;
        }
        return $this->_frozen_num;
    }
    
    // 增加排序索引
    public function orderColumns(\yii\data\Sort $sort)
    {
        foreach($this->columns as $col){
            $col->orderColumn($sort);
        }
        return $this;
    }
    
    // 返回查询数据的模型
    public function getSearchModels()
    {
        $list = [];
        $params = Yii::$app->request->get("SysConfig",[]);
        foreach($this->columns as $item){
            if($this instanceof DcReport){ // 报表
                if(($colnmn = $item['setsCol']) && $item['setsCol']['model_id']){
                    $_ = [
                        'config_type' => ($colnmn['type'] ? $colnmn['type'] : 'text'),
                        'value' => $params[$item['v_alias']],
                        'attribute' => $item['v_alias'],
                        'label_name' => $colnmn['v_label'],
                        'config_params' => $colnmn['search_params'],
                        'v_config_params' => $colnmn['v_search_params'],
                        'v_config_ajax' => $colnmn['v_search_ajax'],
                    ];
                    $list[] = $_;
                }
            }elseif($this instanceof DcSets){ // 数据集
                if($item['model_id']){
                    $_ = [
                        'config_type' => ($item['type'] ? $item['type'] : 'text'),
                        'value' => $params[$item['v_alias']],
                        'attribute' => $item['v_alias'],
                        'label_name' => $item['v_label'],
                        'config_params' => $item['search_params'],
                        'v_config_params' => $item['v_search_params'],
                        'v_config_ajax' => $item['v_search_ajax'],
                    ];
                    $list[] = $_;
                }
            }
        }
        return $list;
    }
    
    // 应用过滤条件
    public function setSearchModels($params = null)
    {
        if($params === false){
            $params = Yii::$app->request->get("SysConfig",[]);
        }
        
        if($params && ($colnmns = $this->columns)){
            if($this instanceof DcReport){
                // 报表
                $mainSet = $this->getV_mainSet();
                $sets = $this->getV_sets();
                $setSearchParams = [];
                foreach($colnmns as $col){
                    if(isset($params[$col['v_alias']]) && (is_array($params[$col['v_alias']]) || strlen($params[$col['v_alias']])>0) && $col['setsCol']){
                        $setSearchParams[$col['set_id']][$col['setsCol']['v_alias']] = $params[$col['v_alias']];
                    }
                }
                
                foreach($sets as $set){
                    if(isset($setSearchParams[$set['id']]) && is_array($setSearchParams[$set['id']])){
                        $set->setSearchModels($setSearchParams[$set['id']]);
                        
                        // 非主数据集的,查询出结果数据并入到主数据集条件
                        if($mainSet['id'] != $set['id']){
                            $set->filterSourceSearch($mainSet);
                        }
                    }
                }
            }elseif($this instanceof DcSets){
                // 数据集
                foreach($colnmns as $col){
                    if(isset($params[$col['v_alias']]) && (is_array($params[$col['v_alias']]) || strlen($params[$col['v_alias']])>0) && $col['model_id']){
                        switch($col['type'])
                        {
                            case 'date': // 日期
                                $this->where($col['id'], $params[$col['v_alias']].' 00:00:00', '>=');
                                $this->where($col['id'], $params[$col['v_alias']].' 23:59:59', '<=');
                                break;
                            case 'daterange': // 日期范围
                            case 'datetimerange': // 日期时间范围
                                if(strpos($params[$col['v_alias']], '至')!==false){
                                    list($startTime, $endTime) = explode('至', $params[$col['v_alias']]);
                                    if($col['type']=='daterange'){
                                        $startTime .= ' 00:00:00';
                                        $endTime .= ' 23:59:59';
                                    }
                                    $this->where($col['id'], trim($startTime), '>=');
                                    $this->where($col['id'], trim($endTime), '<=');
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
                                $this->where($col['id'], $params[$col['v_alias']]); // 直接匹配
                                break;
                            case 'text': // 文本框
                            case 'textarea': //  多行文本框
                            case 'mask': //  格式化文本
                            default: // 默认文本框
                                if(strpos($params[$col['v_alias']], '~')!==false){ // 范围查询
                                    list($start, $end) = explode('~', $params[$col['v_alias']]);
                                    $this->where($col['id'], trim($start), '>=');
                                    $this->where($col['id'], trim($end), '<=');
                                }elseif(preg_match('/^(<>|>=|>|<=|<|=)/', $params[$col['v_alias']], $matches)){
                                    $operator = $matches[1];
                                    $value = substr($params[$col['v_alias']], strlen($operator));
                                    $this->where($col['id'], $value, $operator); // 指定操作
                                }else{
                                    $this->where($col['id'], $params[$col['v_alias']], 'like'); // 模糊查询
                                }
                                break;
                        }
                    }
                }
            }
            
        }
        
        return $this;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
}
