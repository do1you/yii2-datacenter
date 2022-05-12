<?php
/**
 * 脚本数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class ScriptDataProvider extends BaseDataProvider
{
    /**
     * 主键
     */
    public $key;
    
    /**
     * 数组数据
     */
    public $allModels;
    
    /**
     * 脚本实例对象
     */
    public $scriptObj;
    
    /**
     * 过滤条件数组
     */
    private $_wheres = [];
    
    /**
     * 初始化
     */
    public function init()
    {
        if(!($sets = $this->sets)){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '数据集尚未配置正确的数据集.'));
        }
        
        if(!$this->scriptObj && (!($class = $sets->v_run_script) || !class_exists($class))){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '数据集尚未配置正确的数据脚本实例.'));
        }
        
        $this->scriptObj = $this->scriptObj ? $this->scriptObj : Yii::createObject([
            'class' => $class,
            'dataProvider' => $this,
        ]);
        
        // 默认数据
        Yii::configure($this,[
            'pagination' => ['pageSizeLimit' => [1, 500]],
        ]);
        
        // 增加基础排序
        $sort = $this->getSort();
        $columns = $this->report ? $this->report->columns : $sets->columns;
        if($columns && is_array($columns)){
            foreach($columns as $item){
                $col = $this->report ? ($item->col_id>0 ? $item->setsCol : null) : $item;
                if($col){
                    // 添加排序
                    $sort->attributes[$item->v_alias] = [
                        'asc' => [$col['name'] => SORT_ASC],
                        'desc' => [$col['name'] => SORT_DESC],
                        'label' => $item->v_label,
                    ];
                }
            }
        }
        
        // 应用过滤条件
        $this->applySearchModels(false);
    }
    
    /**
     * 获取数据
     */
    protected function prepareModels()
    {
        if($this->report){
            $models = $this->sets->getModels();
            foreach($models as $key=>$item){
                $models[$key] = $this->filterColumns($item);
            }
        }else{
            if (($models = $this->filterAllModels()) === null) {
                return [];
            }
            
            if (($sort = $this->getSort()) !== false) {
                $models = $this->sortModels($models, $sort);
            }
            
            if (($pagination = $this->getPagination()) !== false) {
                $pagination->totalCount = $this->getTotalCount();
                
                if($pagination->getPageSize() > 0) {
                    $models = array_slice($models, $pagination->getOffset(), $pagination->getLimit(), true);
                }
            }
            
            foreach($models as $key=>$item){
                $models[$key] = $this->filterSetsColumns($item);
            }
        }
        return $models;
    }
    
    /**
     * 获取主键
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
            
            return $keys;
        }
        
        return array_keys($models);
    }
    
    /**
     * 总记录数
     */
    protected function prepareTotalCount()
    {
        if($this->report){
            return parent::prepareTotalCount();
        }else{
            return is_array($this->allModels) ? count($this->allModels) : 0;
        }
    }
    
    /**
     * 排序
     */
    protected function sortModels($models, $sort)
    {
        $orders = $sort->getOrders();
        if (!empty($orders)) {
            \yii\helpers\ArrayHelper::multisort($models, array_keys($orders), array_values($orders));
        }
        
        return $models;
    }
    
    /**
     * 汇总数据
     */
    protected function summaryModels()
    {
        $row = [];
        if($this->report){
            $row = $this->sets->getSummary();
            $row = $this->filterColumns($row);
        }else{
            if(($summaryColumns = $this->getSummaryModels())){
                $models = $this->filterAllModels();
                foreach($models as $model){
                    foreach($summaryColumns as $index){
                        if(isset($model[$index]) && is_numeric($model[$index])){
                            if(!isset($row[$index])) $row[$index] = 0;
                            $row[$index] += $model[$index];
                        }
                    }
                }
                $row = $this->filterSetsColumns($row);
            }
        }
        return $row;   
    }
        
    /**
     * 添加查询条件
     */
    public function where($columns, $values, $op = false)
    {
        if($columns===false){
            $this->_wheres = [];
        }else{
            if($values===false && is_array($columns)){
                
            }else{
                $columns = $this->getColumns($columns, $values);
                $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
                $this->_wheres[] = [$op, $columns, $values];
            }
        }
        return $this;
    }
      
    /**
     * 过滤条件数据
     */
    public function filterAllModels()
    {
        if($this->_wheres){
            $list = [];
            $allModels = $this->sets->set_type=='script' ? $this->scriptObj->getModels($this->_wheres) : $this->allModels;
            foreach($allModels as $model){
                $isHave = true;
                foreach($this->_wheres as $where){
                    list($op,$columns,$values) = $where;
                    if(!is_array($columns) && !isset($model[$columns])){
                        $isHave = false;
                    }else{
                        switch($op){
                            case '>':
                                if(!($model[$columns] > $values)){
                                    $isHave = false;
                                }
                            break;
                            case '>=':
                                if(!($model[$columns] >= $values)){
                                    $isHave = false;
                                }
                            break;
                            case '=':
                                if(!($model[$columns] == $values)){
                                    $isHave = false;
                                }
                            break;
                            case '<':
                                if(!($model[$columns] < $values)){
                                    $isHave = false;
                                }
                            break;
                            case '<=':
                                if(!($model[$columns] <= $values)){
                                    $isHave = false;
                                }
                            break;
                            case '!=':
                            case '<>':
                                if(!($model[$columns] != $values)){
                                    $isHave = false;
                                }
                            break;
                            case 'in':
                                $values = is_array($values) ? $values : [$values];
                                if(is_array($columns)){
                                    foreach($values as $arr){
                                        $isHave1 = true;
                                        foreach($columns as $k=>$col){
                                            if($arr[$k]!=$model[$col]){
                                                $isHave1 = false;
                                            }
                                        }
                                        if($isHave1) break;
                                    }
                                    if(!$isHave1) $isHave = false;
                                }else{
                                    if(in_array($model[$columns],$values)===false){
                                        $isHave = false;
                                    }
                                }
                            break;
                            case 'like':
                            default:
                                if(stripos($model[$columns],$values)===false){
                                    $isHave = false;
                                }
                            break;
                            
                        }
                    }
                }
                if($isHave){
                    $list[] = $model;
                }
            }
            $this->allModels = $list;
        }else{
            $this->allModels = $this->sets->set_type=='script' ? $this->scriptObj->getModels() : $this->allModels;
        }
        
        return $this->allModels;
    }
    
}
