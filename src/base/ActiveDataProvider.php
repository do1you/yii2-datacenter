<?php
/**
 * QUERY数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class ActiveDataProvider extends \yii\data\ActiveDataProvider implements ReportDataInterface
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
     * 需要统计汇总的字段
     */
    private $summaryColumns = [];
    
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
                        // 汇总查询
                        if($col['model_id'] && $col['column'] && in_array($col['column']['type'], [
                            'int', 'mediumint', 'integer', 'float', 'double', 'decimal', 'bigint'
                        ]) && (empty($col['type']) || in_array($col['type'], ['text']))){
                            if($col->fun){
                                $this->summaryColumns[] = new \yii\db\Expression("{$v_column} as {$col->v_alias}");
                            }elseif(!in_array(strtolower(substr($v_column,-2)), ['id','no'])
                                && !in_array(strtolower(substr($v_column,-4)), ['type','flag'])
                            ){
                                $this->summaryColumns[] = "SUM({$v_column}) as {$col->v_alias}";
                            }
                        }
                    }
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
            $list = parent::prepareModels();
            foreach($list as $key=>$item){
                $list[$key] = $this->sets->formatValue($item, $this->sets->columns); // 格式化数据
            }
        }
        return $list;
    }
    
    /**
     * 汇总数据
     */
    protected function summaryModels()
    {
        $row = [];
        if($this->report){
            $callModel = new \datacenter\models\DcSets();
            $row = $this->sets->getV_summary();
            $row = $this->sets->sourceAfterFindModels($row);
            $row = call_user_func_array([$callModel, 'formatValue'], [$this->filterColumns($row), $this->report->columns]);
            unset($row['_']);
        }else{
            if($this->summaryColumns){
                $this->sets->group(false)->select(false);
                if($this->forReport && ($arr = $this->sets->getV_relation(true))){
                    list($relation, $source) = $arr;
                    
                    // 写入条件
                    $columns = $relation->getV_target_columns($this->sets, false);
                    $keys = $relation->getV_source_columns($source);
                    $source->select(false)->select($keys);
                    $this->sets->where($columns, $source);
                    
                    // 分组写入
                    if($relation->rel_type=='group' && $relation->group_col){
                        $this->sets->group($relation['v_group_col'])->select($relation['v_group_col']);
                    }
                }
                
                $query = $this->query;
                if($query->having){
                    $newQuery = new \yii\db\Query([
                        'from' => ['sub' => $query],
                    ]);
                    foreach($this->summaryColumns as $select){
                        if($select instanceof \yii\db\ExpressionInterface) {
                            $select = $select->expression;
                        }
                        
                        if(preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $select, $matches)) {
                            $select = $matches[2];
                        }
                        $newQuery->addSelect($select);
                    }
                }else{
                    foreach($this->summaryColumns as $select){
                        $query->addSelect($select);
                    }
                    $newQuery = $query;
                }
                
                if(!empty($relation) && $relation['rel_type']=='group'){
                    $list = $newQuery->all($this->db);
                    $groupCols = $relation->getV_source_columns($this->sets, true, $relation['v_group_col']);
                    $buckets = [];
                    foreach($list as $item){
                        $gk = $relation->getModelKey($item, $groupCols);
                        $row['_'][$this->sets['id']][$gk] = $this->sets->formatValue($item, $this->sets->columns);
                    }
                }else{
                    $row = $this->sets->formatValue($newQuery->one($this->db), $this->sets->columns);
                }
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
     * 添加过滤值查询条件
     */
    public function filterWhere($columns, $values, $op = false)
    {
        $columns = $this->getColumns($columns, $values);
        $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
        $this->query->andFilterWhere([$op, $columns, $values]);
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
     * 添加过滤值分组查询条件
     */
    public function filterHaving($columns, $values, $op = false)
    {
        $columns = $this->getColumns($columns, $values);
        $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
        $this->query->andFilterHaving([$op, $columns, $values]);
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