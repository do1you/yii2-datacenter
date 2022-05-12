<?php
/**
 * SQL数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class SqlDataProvider extends BaseDataProvider
{
    /**
     * 数据库连接
     */
    public $db = 'db';
    
    /**
     * SQL
     */
    public $sql;
    
    /**
     * 源SQL
     */
    public $sourceSql;
    
    /**
     * 动态参数
     */
    public $params = [];
    
    /**
     * 主键
     */
    public $key;
    
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
            'sourceSql' => $this->sets['run_sql'],
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
            $models = $this->sets->getModels();
            foreach($models as $key=>$item){
                $models[$key] = $this->filterColumns($item);
            }
        }else{
            $this->filterAllModels();
            $sort = $this->getSort();
            $pagination = $this->getPagination();
            if ($pagination === false && $sort === false) {
                return $this->db->createCommand($this->sql, $this->params)->queryAll();
            }
            
            $sql = $this->sql;
            $orders = [];
            $limit = $offset = null;
            
            if ($sort !== false) {
                $orders = $sort->getOrders();
                $pattern = '/\s+order\s+by\s+([\w\s,\.]+)$/i';
                if (preg_match($pattern, $sql, $matches)) {
                    array_unshift($orders, new Expression($matches[1]));
                    $sql = preg_replace($pattern, '', $sql);
                }
            }
            
            if ($pagination !== false) {
                $pagination->totalCount = $this->getTotalCount();
                $limit = $pagination->getLimit();
                $offset = $pagination->getOffset();
            }
            
            $sql = $this->db->getQueryBuilder()->buildOrderByAndLimit($sql, $orders, $limit, $offset);
            
            $models = $this->db->createCommand($sql, $this->params)->queryAll();
            foreach($models as $key=>$item){
                $models[$key] = $this->filterSetsColumns($item); // 格式化数据
            }
        }
        return $models;
    }
    
    /**
     * 获取主键
     */
    protected function prepareKeys($models)
    {
        $keys = [];
        if ($this->key !== null) {
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
     * 获取总记录数年
     */
    protected function prepareTotalCount()
    {
        if($this->report){
            return parent::prepareTotalCount();
        }else{
            return (new \yii\db\Query([
                'from' => ['sub' => "({$this->sql})"],
                'params' => $this->params,
            ]))->count('*', $this->db);
        }
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
                $this->filterAllModels();
                $query = new \yii\db\Query([
                    'from' => ['sub' => "({$this->sql})"],
                    'params' => $this->params,
                ]);
                foreach($summaryColumns as $item){
                    $query->addSelect($item);
                }
                $row = $query->groupBy ? array_map([$this, 'filterSetsColumns'], $query->all($this->db)) : $this->filterSetsColumns($query->one($this->db));
            }
        }
        return $row;   
    }
        
    /**
     * 添加查询条件
     */
    public function where($columns, $values = false, $op = false)
    {
        if($columns===false){
            $this->_wheres = [];
        }else{
            if($values===false && is_array($columns)){
                $this->_wheres[] = $this->formatColumns($columns);
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
            $query = new \yii\db\Query([
                'from' => ['sub' => "({$this->sourceSql})"],
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
        }else{
            $this->sql = $this->sourceSql;
        }
    }
    
}


