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
     * ID分组数据集字段
     */
    private $_id_columns;
    
    /**
     * 别名分组数据集字段
     */
    private $_alias_columns;
    
    /**
     * 初始化
     */
    public function init()
    {
        parent::init();
        
        if(!($sets = $this->sets) || !$sets->mainModel || !$sets->mainModel['source'] || !($db = $sets->mainModel['source']->getSourceDb())){
            throw new \yii\base\InvalidConfigException(Yii::t('datacenter', '数据集尚未配置正确的主模型.'));
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
        $sets->rel_group && $query->addGroupBy($sets->formatSql($sets->rel_group));
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
                        $query->addSelect(["{$col->v_fncolumn} as {$col->v_alias}"]);
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
        }else{
            $list = parent::prepareModels();
            foreach($list as $key=>$item){
                $list[$key] = $this->sets->formatValue($item, $this->sets->columns); // 格式化数据
            }
        }
        return $list;
    }
    
    /**
     * 添加查询字段
     */
    public function select($columns)
    {
        $this->query->addSelect($this->getColumns($columns));
        return $this;
    }
    
    /**
     * 添加查询条件
     */
    public function where($columns, $values, $op = false)
    {
        $columns = $this->getColumns($columns, $values);
        $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
        $this->query->andWhere([$op, $columns, $values]);
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
        $columns = $this->getColumns($columns, $values);
        $op = $op ? $op : ((is_array($columns) || is_array($values)) ? 'in' : '=');
        $this->query->andHaving([$op, $columns, $values]);
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
        $this->query->addGroupBy($this->getColumns($columns));
        return $this;
    }
    
    /**
     * 添加排序
     */
    public function order($columns)
    {
        $this->query->addOrderBy($this->getColumns($columns));
        return $this;
    }
    
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
    public function getColumns($columns, &$values = null)
    {
        if($this->sets && $this->sets['columns']){
            if(is_array($columns)){
                foreach($columns as $k=>$col){
                    $columns[$k] = $this->getColumns($col);
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
                    $columns = $idColumns[$columns]['v_fncolumn'];
                }elseif($aliasColumns && isset($aliasColumns[$columns])){
                    $columns = $aliasColumns[$columns]['v_fncolumn'];
                }
            }
        }
        
        return $columns;
    }
}