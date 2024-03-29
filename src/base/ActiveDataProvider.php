<?php
/**
 * QUERY数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class ActiveDataProvider extends BaseDataProvider
{    
    /**
     * 查询的字段
     */
    private $_selects = [];
    
    /**
     * 查询的字段别名
     */
    private $_selectAlias = [];
    
    /**
     * 是否数据集并集
     */
    public $isUnion = false;
    
    /**
     * 初始化
     */
    public function initData()
    {
        if(!($sets = $this->sets) || !$sets->mainModel || !$sets->mainModel['source'] || !($db = $sets->mainModel['source']->getSourceDb())){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '数据集尚未配置正确的主模型.'));
        }
        
        $query = new \yii\db\Query();
        $query->from("{$sets->mainModel['v_model']} as {$sets->mainModel['v_alias']}");
        
        // 关联模型
        $allModels = $models = $sets->getV_relation_models();
        $allSets = $setLists = $sets->getV_relation_sets();
        unset($models[$sets->mainModel['id']],$allSets[$sets->id]);
        $sets->mainModel->joinModel($query, $models);
        if($models){
            $model = reset($models);
            throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的模型关系')."{$model['tb_label']}({$model['id']}.{$model['tb_name']})");
        }
        
        // 关联数据集
        $sets->joinQuerySets($query, $setLists);
        if($setLists){
            $model = reset($setLists);
            throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的数据集关系')."{$model['v_title']}");
        }
        
        // 默认条件、分组、排序
        $this->report ? $this->report->setSearchValues($this->getSearchValues()) : $this->sets->setSearchValues($this->getSearchValues());
        $sets->rel_where && $query->andWhere($sets->formatSql($sets->rel_where));
        $sets->rel_group && $query->addGroupBy(new \yii\db\Expression(($gSql = $sets->formatSql($sets->rel_group)))); 
        $sets->rel_having && $query->andHaving($sets->formatSql($sets->rel_having));
        $sets->rel_order && $query->addOrderBy(new \yii\db\Expression($sets->formatSql($sets->rel_order)));
        
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
                    if($this->sets['id']==$col['set_id']){
                        $sort->attributes[$item->v_alias] = [
                            'asc' => [$col['v_column'] => SORT_ASC],
                            'desc' => [$col['v_column'] => SORT_DESC],
                            'label' => $item->v_label,
                        ];
                    }
                    
                    // 添加查询
                    if(!$item->formula && !$col->formula && (!$this->report || $this->sets['id']==$col['set_id'])){
                        $col['column_id'] && $this->select($col['id']);
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
            $totalRow = $this->summaryLists($list);
            foreach($list as $key=>$item){
                $list[$key] = $this->filterColumns($item, false, $totalRow);
            }
        }else{
            if (!$this->query instanceof \yii\db\QueryInterface) {
                throw new \yii\base\InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
            }
            
            $this->union();
            $query = clone $this->query;
            if (($pagination = $this->getPagination()) !== false) {
                $pagination->totalCount = $this->getTotalCount();
                if ($pagination->totalCount === 0) {
                    return [];
                }
                $query->limit($pagination->getLimit())->offset($pagination->getOffset());
            }
            if (($sort = $this->getSort()) !== false) {
                $orderBy = $query->orderBy;
                $query->orderBy($sort->getOrders());
                $query->addOrderBy($orderBy);
            }
            
            // 预设变量
            $this->setVars($query);
            
            $list = $query->all($this->db);
            $totalRow = $this->summaryLists($list);
            foreach($list as $key=>$item){
                $list[$key] = $this->filterSetsColumns($item, false, $totalRow);
            }
        }
        $list = $this->filterGroupColumns($list);
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
            $row = $this->filterColumns($row, true);
        }else{
            $this->union();
            if(($summaryColumns = $this->getSummaryModels())){
                $query = $this->query;
                $relation = $this->sets ? $this->sets['v_relation'] : null;
                $values = null;
                if($query->having || $this->isUnion){ //  || ($query->groupBy && $this->sets->v_sets)
                    $newQuery = $this->isUnion ? $query : (new \yii\db\Query([
                        'from' => ['sub' => $query],
                    ]));
                    foreach($summaryColumns as $select){
                        if($select instanceof \yii\db\ExpressionInterface) {
                            $select = $select->expression;
                        }
                        if(preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $select, $matches)) {
                            $select = "SUM({$matches[2]}) as {$matches[2]}";
                        }
                        $newQuery->addSelect($select);
                    }
                    
                    // 分组统计
                    if($relation && $relation['rel_type']=='group'){
                        $groupColumn = $this->getColumns($relation['v_group_col'], $values, 'v_alias');
                        $newQuery->addSelect($groupColumn)->groupBy($groupColumn);
                    }
                }else{
                    $query->orderBy([]);
                    $this->select(false);
                    $query->groupBy([]);
                    
                    foreach($summaryColumns as $select){
                        $query->addSelect($select);
                    }
                    
                    // 分组统计
                    if($relation && $relation['rel_type']=='group'){
                        $groupColumn = $this->getColumns($relation['v_group_col']);
                        $query->addSelect($groupColumn)->groupBy($groupColumn);
                    }
                    
                    $newQuery = $query;
                }
                
                if($newQuery->where && preg_match('/\s+\(?0\=1\)?\s*?/', $newQuery->createCommand($this->db)->sql)){
                    $row = [];
                }else{
                    $this->setVars($newQuery);
                    if($newQuery->groupBy){
                        $row = $newQuery->all($this->db);
                        foreach($row as $key=>$item){
                            $row[$key] = $this->filterSetsColumns($item, true);
                        }
                    }else{
                        $row = $this->filterSetsColumns($newQuery->one($this->db), true);
                    }
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
            $query = $this->query;
            if(!$query->having){
                $this->query->select([]);
                $this->_selectAlias = $this->_selects = [];
            }
        }else{
            $values = null;
            $columns = is_array($columns) ? $columns : [$columns];
            foreach($columns as $select){
                if($select){
                    $v_column = $this->getColumns($select, $values, 'v_fncolumn');
                    $v_alias = $this->getColumns($select, $values, 'v_alias');
                    if(!isset($this->_selects[$v_alias])){
                        $this->_selects[$v_alias] = new \yii\db\Expression("{$v_column} as {$v_alias}");
                        $this->_selectAlias[trim($v_column)] = $v_alias;
                        $this->query->addSelect([$this->_selects[$v_alias]]);
                    }
                }
                
            }
        }
        return $this;
    }
    
    /**
     * 添加查询条件
     */
    public function where($columns, $values = false, $op = false)
    {
        if($columns===false){
            $this->query->where([]);
        }else{
            if($values===false && is_array($columns)){
                $this->query->andWhere($this->formatColumns($columns));
            }else{
                $columns = $this->getColumns($columns, $values);
                $op = $op ? $op : ((is_array($columns) || is_array($values) || is_object($values)) ? 'in' : '=');
                if(is_object($values)){
                    if($values instanceof \datacenter\models\DcSets 
                        && in_array($values['set_type'], ['model','sql'])
                        && $this->db==$values->getDataProvider()->db
                    ){
                        $values = $values->getDataProvider()->query;
                    }else{
                        // 跨数据库无法提取汇总
                        $this->query->andWhere("0=1"); 
                        return $this;
                    }
                }
                $this->query->andWhere([$op, $columns, $values]);
            }
        }
        return $this;
    }
    
    /**
     * 添加分组条件
     */
    public function having($columns, $values = false, $op = false)
    {
        if($columns===false){
            $this->query->having([]);
        }else{
            if($values===false && is_array($columns)){
                $this->query->andHaving($this->formatColumns($columns));
            }else{
                $columns = $this->getColumns($columns, $values);
                $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
                $this->query->andHaving([$op, $columns, $values]);
            }
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
            $columns = $this->getColumns($columns);
            $columns = is_array($columns) ? $columns : [$columns];
            foreach($columns as $select){
                $select && $this->query->addGroupBy(new \yii\db\Expression($select));
            }
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
            $columns = is_array($columns) ? $columns : [$columns];
            foreach($columns as $select){
                $select && $this->query->addOrderBy(new \yii\db\Expression($select));
            }
        }
        return $this;
    }
    
    /**
     * 合并数据集
     */
    public function union()
    {
        $v_alias = "title"; // 标识出数据集名称
        
        if(($sets = $this->sets->_relation_union)){
            foreach($sets as $set){
                $query = $set->getDataProvider()->query;
                if(!isset($this->_selects[$v_alias])){
                    $title = $set['forUserModel'] ? $set['forUserModel']['v_name'] : $set['v_title'];
                    $query->addSelect(new \yii\db\Expression("'{$title}' as {$v_alias}"));
                }
                $this->query->union($query, true);
                $this->isUnion = true;
            }
        }
        
        if(!isset($this->_selects[$v_alias])){
            $title = $this->sets['forUserModel'] ? $this->sets['forUserModel']['v_name'] : $this->sets['v_title'];
            $this->_selects[$v_alias] = new \yii\db\Expression("'{$title}' as {$v_alias}");
            $this->query->addSelect([$this->_selects[$v_alias]]);
        }
        
        // 重组SQL
        if($this->isUnion){
            $query = new \yii\db\Query();
            $allQuery = $this->query;
            $query->from(['sub' => $allQuery]);
            if($allQuery->orderBy && is_array($allQuery->orderBy)){
                foreach($allQuery->orderBy as $orderBy){
                    if($orderBy instanceof \yii\db\ExpressionInterface) {
                        $orderBy = $orderBy->expression;
                    }
                    
                    $orderBy = explode(',', $orderBy);
                    foreach($orderBy as $order){
                        list($col, $sort) = explode(' ', trim($order));
                        $col = trim($col);
                        
                        if(isset($this->_selectAlias[$col])){
                            $query->addOrderBy("{$this->_selectAlias[$col]} {$sort}");
                        }
                    }                    
                }
            }
            $this->query = $query;
        }
    }
}