<?php
/**
 * QUERY数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class ActiveDataProvider extends BaseDataProvider
{    
    /**
     * 初始化
     */
    public function init()
    {
        if(!($sets = $this->sets) || !$sets->mainModel || !$sets->mainModel['source'] || !($db = $sets->mainModel['source']->getSourceDb())){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '数据集尚未配置正确的主模型.'));
        }
        
        $query = new \yii\db\Query();
        $query->from("{$sets->mainModel['v_model']} as {$sets->mainModel['v_alias']}");
        
        // 关联模型
        $allModels = $models = $sets->getV_relation_models();
        unset($models[$sets->mainModel['id']]);
        $sets->mainModel->joinModel($query, $models);
        if($models){
            $model = reset($models);
            throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的模型关系')."{$model['tb_label']}({$model['id']}.{$model['tb_name']})");
        }
        
        // 默认条件、分组、排序
        $sets->rel_where && $query->andWhere($sets->formatSql($sets->rel_where));
        $sets->rel_group && $query->addGroupBy(new \yii\db\Expression(($gSql = $sets->formatSql($sets->rel_group)))); 
        $sets->rel_having && $query->andHaving($sets->formatSql($sets->rel_having));
        $sets->rel_order && $query->addOrderBy($sets->formatSql($sets->rel_order));
        
        // 默认数据源
        Yii::configure($this,[
            'db' => $db,
            'query' => $query,
            'pagination' => ['pageSizeLimit' => [1, 500]],
        ]);
        
        // 增加基础排序和查询字段
        $sort = $this->getSort();
        $columns = $this->report ? $this->report->columns : $sets->columns;
        if($columns && is_array($columns)){
            foreach($columns as $item){
                $col = $this->report ? ($item->col_id>0 ? $item->setsCol : null) : $item;
                if($col){
                    // 添加排序
                    $sort->attributes[$item->v_alias] = [
                        'asc' => [$col['v_column'] => SORT_ASC],
                        'desc' => [$col['v_column'] => SORT_DESC],
                        'label' => $item->v_label,
                    ];
                    
                    // 添加查询
                    if(!$item->formula && !$col->formula && (!$this->report || $this->sets['id']==$col['set_id'])){
                        $v_column = $col->v_fncolumn;
                        if($col->fun){
                            $query->addSelect([new \yii\db\Expression("{$v_column} as {$col->v_alias}")]);
                        }else{
                            $query->addSelect(["{$v_column} as {$col->v_alias}"]);
                        }
                    }
                }
            }
        }
        
        // 应用过滤条件
        $this->applySearchModels(false);
    }
    
    /**
     * 获取数据列表
     */
    protected function prepareModels()
    {
        if($this->report){
            $list = $this->sets->getModels();
            foreach($list as $key=>$item){
                $list[$key] = $this->filterColumns($item);
            }
        }else{
            $list = parent::prepareModels();
            foreach($list as $key=>$item){
                $list[$key] = $this->filterSetsColumns($item);
            }
        }
        return $list;
    }
    
    /**
     * 获取汇总数据
     */
    protected function summaryModels()
    {
        $row = [];
        if($this->report){
            $row = $this->sets->getSummary();
            $row = $this->filterColumns($row);
        }else{
            if(($summaryColumns = $this->getSummaryModels())){
                $query = $this->query;
                if($query->having){
                    $newQuery = new \yii\db\Query([
                        'from' => ['sub' => $query],
                    ]);
                    foreach($summaryColumns as $select){
                        if($select instanceof \yii\db\ExpressionInterface) {
                            $select = $select->expression;
                        }
                        
                        if(preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $select, $matches)) {
                            $select = $matches[2];
                        }
                        $newQuery->addSelect($select);
                    }
                }else{
                    foreach($summaryColumns as $select){
                        $query->addSelect($select);
                    }
                    $newQuery = $query;
                }
                
                $row = $newQuery->groupBy ? array_map([$this, 'filterSetsColumns'], $newQuery->all($this->db)) : $this->filterSetsColumns($newQuery->one($this->db));
            }
        }
        return $row;        
    }
    
    /**
     * 添加查询字段
     */
    public function select($columns)
    {
        if($columns===false){
            $this->query->select([]);
        }else{
            $values = null;
            $this->query->addSelect($this->getColumns($columns, $values, true));
        }
        return $this;
    }
    
    /**
     * 添加查询条件
     */
    public function where($columns, $values, $op = false)
    {
        if($columns===false){
            $this->query->where([]);
        }else{
            $columns = $this->getColumns($columns, $values);
            $op = $op ? $op : ((is_array($columns) || is_array($values) || is_object($values)) ? 'in' : '=');
            if(is_object($values)){
                if($values instanceof \datacenter\models\DcSets){
                    $values = $values->getDataProvider()->query;
                }
            }
            $this->query->andWhere([$op, $columns, $values]);
        }
        return $this;
    }
    
    /**
     * 添加分组条件
     */
    public function having($columns, $values, $op = false)
    {
        if($columns===false){
            $this->query->having([]);
        }else{
            $columns = $this->getColumns($columns, $values);
            $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
            $this->query->andHaving([$op, $columns, $values]);
        }
        return $this;
    }
    
    /**
     * 添加分组
     */
    public function group($columns)
    {
        if($columns===false){
            $this->query->groupBy([]);
        }else{
            $this->query->addGroupBy($this->getColumns($columns));
        }
        return $this;
    }
    
    /**
     * 添加排序
     */
    public function order($columns)
    {
        if($columns===false){
            $this->query->orderBy([]);
        }else{
            $columns = $this->getColumns($columns);
            if(is_array($columns)){
                foreach($columns as $col){
                    $this->query->addOrderBy($col);
                }
            }else{
                $this->query->addOrderBy($columns);
            }
        }
        return $this;
    }
}