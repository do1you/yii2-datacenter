<?php
/**
 * SQL数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class SqlDataProvider extends \yii\data\SqlDataProvider implements ReportDataInterface
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
     * 过滤条件数组
     */
    private $_wheres = [];
    
    /**
     * 初始化
     */
    public function init()
    {
        if(!($sets = $this->sets) || !$this->sets->source || !($db = $this->sets->source->getSourceDb()) || !$this->sets['run_sql']){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '数据集尚未配置正确的数据集.'));
        }
        
        // 默认数据
        Yii::configure($this,[
            'db' => $db,
            'sql' => $this->sets['run_sql'],
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
                        'asc' => [$col['v_field'] => SORT_ASC],
                        'desc' => [$col['v_field'] => SORT_DESC],
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
            $query = new \yii\db\Query([
                'from' => ['sub' => "({$this->sql})"],
                'params' => $this->params,
            ]);
            foreach($this->_wheres as $item){
                $query->andWhere($item);
            }
        }
        
        if(!empty($query)){
            $command = $query->createCommand($this->db);
            $this->sql = $command->sql;
            $this->params = $command->params;
        }
    }
    
}


