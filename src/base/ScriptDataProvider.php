<?php
/**
 * 脚本数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class ScriptDataProvider extends \yii\data\ArrayDataProvider implements ReportDataInterface
{
    use \datacenter\base\ReportDataProviderTrait;
    
    /**
     * 数据集
     */
    public $sets;
    
    /**
     * 数据报表
     */
    public $report;
    
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
            $callModel = new \datacenter\models\DcSets();
            $list = $this->sets->getModels();
            foreach($list as $key=>$item){
                $list[$key] = call_user_func_array([$callModel, 'formatValue'], [$this->filterColumns($item), $this->report->columns]);
            }
            $this->setPaginationTotalCount();
        }else{
            $this->filterAllModels();
            $list = parent::prepareModels();
            foreach($list as $key=>$item){
                $list[$key] = $this->sets->formatValue($this->filterSetsColumns($item), $this->sets->columns); // 格式化数据
            }
        }
        return $list;
    }
    
    /**
     * 汇总数据
     */
    protected function summaryModels()
    {
        return [];
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
        if($columns===false){
            $this->_wheres = [];
        }else{
            $columns = $this->getColumns($columns, $values);
            $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
            $this->_wheres[] = [$op, $columns, $values];
        }
        return $this;
    }
    
    /**
     * 添加过滤值查询条件
     */
    public function filterWhere($columns, $values, $op = false)
    {
        $columns = $this->getColumns($columns, $values);
        $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
        if(is_array($values) || strlen($values)) $this->_wheres[] = [$op, $columns, $values];
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
    }
    
}
