<?php
/**
 * 数据库表 "dc_sets" 的模型对象.
 * @property int $id 流水号
 * @property string $title 标题
 * @property string $set_type 数据集类型
 * @property int $main_model 主模型
 * @property string $relation_models 模型匹配关系
 * @property string $run_script 运行脚本
 * @property string $run_sql 运行SQL
 * @property string $excel_file EXCEL文件
 * @property int $state 状态
 * @property string $rel_where 条件
 * @property string $rel_group 分组
 * @property string $rel_order 排序
 * @property string $update_time 更新时间
 */

namespace datacenter\models;

use Yii;

class DcSets extends \webadmin\ModelCAR
{
    use ReportDataTrait;
    use ReportOrmTrait;
    
    /**
     * 关联的所有模型
     */
    private $_relation_models;
    
    /**
     * 用于格式化SQL的替换字符
     */
    private $_replace_params;
    
    /**
     * 关联的源数据集合
     */
    public $_relation_source = [];
    
    /**
     * 关联的目标数据集合
     */
    public $_relation_target = [];
            
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_sets';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['title', 'set_type', 'cat_id'], 'required'],
            [['main_model', 'relation_models', 'run_script', 'run_sql', 'excel_file', 'rel_where', 'rel_group', 'rel_order', 'update_time'], 'safe'],
            [['main_model', 'state', 'cat_id'], 'integer'],
            [['run_sql'], 'string'],
            [['update_time'], 'safe'],
            [['title', 'set_type'], 'string', 'max' => 50],
            [['relation_models'], 'string', 'max' => 255],
            [['run_script', 'excel_file'], 'string', 'max' => 150],
            [['rel_where'], 'string', 'max' => 200],
            [['rel_group', 'rel_order'], 'string', 'max' => 100],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'title' => Yii::t('datacenter', '标题'),
            'set_type' => Yii::t('datacenter', '数据集类型'),
            'main_model' => Yii::t('datacenter', '主模型'),
            'relation_models' => Yii::t('datacenter', '模型匹配关系'),
            'run_script' => Yii::t('datacenter', '运行脚本'),
            'run_sql' => Yii::t('datacenter', '运行SQL'),
            'excel_file' => Yii::t('datacenter', 'EXCEL文件'),
            'state' => Yii::t('datacenter', '状态'),
            'rel_where' => Yii::t('datacenter', '条件'),
            'rel_group' => Yii::t('datacenter', '分组'),
            'rel_order' => Yii::t('datacenter', '排序'),
            'update_time' => Yii::t('datacenter', '更新时间'),
            'cat_id' => Yii::t('datacenter', '数据集分类'),
        ];
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        $this->update_time = date('Y-m-d H:i:s');
        
        return parent::beforeSave($insert);
    }
    
    // 获取分类关系
    public function getCat(){
        return $this->hasOne(DcCat::className(), ['id'=>'cat_id']);
    }
    
    // 获取主数据模型关系
    public function getMainModel(){
        return $this->hasOne(DcModel::className(), ['id'=>'main_model']);
    }
    
    // 获取字段关系
    public function getColumns(){
        return $this->hasMany(DcSetsColumns::className(), ['set_id' => 'id'])->addOrderBy("is_frozen desc,paixu desc,id asc");
    }
    
    // 获取源数据集关系
    public function getSourceRelation(){
        return $this->hasMany(DcSetsRelation::className(), ['source_sets' => 'id']);
    }
    
    // 获取目标数据集关系
    public function getTargetRelation(){
        return $this->hasMany(DcSetsRelation::className(), ['target_sets' => 'id']);
    }
    
    // 获取数据集类型
    public function getV_set_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_set_type', ($val !== null ? $val : $this->set_type));
    }
    
    // 获取数据集类型
    public function getV_state($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('record_status', ($val !== null ? $val : $this->state));
    }
    
    // 返回格式化标题
    public function getV_title()
    {
        return "{$this->title}[{$this->id}]";
    }
    
    // 获取模型匹配关系
    public function getV_relation_models()
    {
        if($this->_relation_models === null){
            $list = \yii\helpers\ArrayHelper::map($this->columns, 'model_id', 'model');
            if($list){
                foreach($list as $k=>$model){
                    if(!$model) unset($list[$k]);
                }
            }
            $this->_relation_models = $list;
        }
        
        return $this->_relation_models;
    }
    
    // 格式化显示模型匹配关系
    public function getV_relation_models_str()
    {
        $list = $this->getV_relation_models();
        $names = \yii\helpers\ArrayHelper::map($list, 'id', 'v_tb_name');
        return implode(',', $names);
    }
    
    // 获取分类
    public function getV_cat_id($val = null)
    {
        if($val===false){
            return DcCat::treeOptions();
        }else{
            if($val!== null){
                $model = DcCat::findOne($val);
                return ($model ? $model['name'] : '');
            }else{
                return ($this->cat ? $this->cat['name'] : '');
            }
        }
    }
    
    // 格式化SQL字符串
    public function formatSql($str)
    {
        if($this->_replace_params === null || !isset($this->_replace_params['search']) || !isset($this->_replace_params['replace'])){
            $search = $replace = [];
            $models = $this->getV_relation_models();
            foreach($models as $model){
                $search[] = "{{$model['tb_label']}}.";
                $replace[] = "{$model['v_alias']}.";
                $search[] = "{$model['tb_name']}.";
                $replace[] = "{$model['v_alias']}.";
            }
            $this->_replace_params['search'] = $search;
            $this->_replace_params['replace'] = $replace;
        }
        
        return str_replace($this->_replace_params['search'], $this->_replace_params['replace'], $str);
    }
    
    // 格式化计算公式字符串
    public function formatValue($values, $columns)
    {
        if($this->_replace_params === null || !isset($this->_replace_params['format_formulas']) || !isset($this->_replace_params['format_labels'])){
            $formatLabels = $formatFormulas = [];
            foreach($columns as $col){
                if($col['formula']){
                    $formatFormulas[$col['v_alias']] = $col['formula'];
                }
                
                $formatLabels[$col['v_format_label']] = "\${$col['v_alias']}";
            }
            $this->_replace_params['format_formulas'] = $formatFormulas;
            $this->_replace_params['format_labels'] = $formatLabels;
        }
        
        $formatFormulas = $this->_replace_params['format_formulas'];
        $formatLabels = $this->_replace_params['format_labels'];
        if($formatFormulas && is_array($formatFormulas)){
            $search = $formatLabels ? array_keys($formatLabels) : [];
            $replace = $formatLabels ? array_values($formatLabels) : [];
            foreach($formatFormulas as $key=>$formula){
                try {
                    $values[$key] = '';
                    $formula = str_replace($search, $replace, $formula);
                    extract($values, EXTR_OVERWRITE);
                    $formula = preg_replace('/[{][^}]*?[}]/','$null',$formula);
                    eval('$values[$key] = '.$formula.';');
                    $values[$key] = (string)$values[$key];
                    if(strlen($values[$key])<=0) $values[$key] = '&nbsp;';
                }catch(\Exception $e) {
                }
            }
        }else{
            foreach($values as $key=>$v){
                if(strlen($v)<=0) $values[$key] = '&nbsp;';
            }
        }
        
        return $values;
    }   
    
    // 查询所有字段信息
    public function selectColumns(\yii\db\Query $query)
    {
        foreach($this->columns as $col){
            $col->selectColumn($query);
        }
        
        return $this;
    }
    
    // 连接其他数据集
    public function joinSets(&$sets=[])
    {
        if(isset($sets[$this->id])) unset($sets[$this->id]);
        
        $relations = \yii\helpers\ArrayHelper::map($this->sourceRelation, 'target_sets', 'v_self');
        $joinSets = [];
        foreach($sets as $k=>$set){
            if(empty($set)){
                unset($sets[$k]);
            }
            if(isset($relations[$set['id']])){
                $this->_relation_source[$set['id']] = [$relations[$set['id']], $set];
                $set->_relation_target[$this['id']] = [$relations[$set['id']], $this];
                $set->off(self::$EVENT_AFTER_MODEL, [$set, 'prepareSets']);
                $set->on(self::$EVENT_AFTER_MODEL, [$set, 'prepareSets']);
                $relations[$set['id']]->joinWhere($this, $set);
                $joinSets[$set['id']] = $set;
                unset($sets[$k]);
            }
        }
        
        if($joinSets){
            foreach($joinSets as $set){
                if(!empty($sets)){
                    $set->joinSets($sets);
                }
            }
        }
        
        return $this;
    }
    
    // 数据集合匹配
    public function prepareSets()
    {
        if($this->_relation_target){
            $target = $this;
            foreach($this->_relation_target as $key=>$item){
                list($relation, $source) = $item;
                $buckets = $values = [];
                
                // 目标数据集合
                $targetList = $target->getModels();
                $columns = $relation->getV_target_columns($target);
                foreach($targetList as $model){
                    $k = $this->getModelKey($model, $columns);
                    $buckets[$k] = $model;
                }
                
                // 源数据集合
                $sourceList = $source->getModels();
                $keys = $relation->getV_source_columns($source);
                foreach($sourceList as $index=>$model){
                    $k = $this->getModelKey($model, $keys);
                    $sourceList[$index]['_'][$target['id']] = isset($buckets[$k]) ? $buckets[$k] : null;
                }
                
                $source->setModels($sourceList);
            }
        }
    }
    
    // 数据集关联条件过滤，参数：查询字段，查询值
    public function where($columns, $values, $op = false)
    {
        $colModels = \yii\helpers\ArrayHelper::map($this['columns'], 'id', 'v_self');
        $dataProvider = $this->getDataProvider();
        switch($this->set_type){
            case 'sql': // SQL
                break;
            case 'excel': // EXCEL文档
                break;
            case 'script': // 脚本
                break;
            case 'model': // 数据库模型
                if(is_array($columns)){
                    foreach($columns as $k=>$col){
                        if(isset($colModels[$col])){
                            $columns[$k] = $colModels[$col]['v_column'];
                        }
                    }
                    foreach($values as $k=>$value){
                        if(is_array($value)){
                            $values[$k] = array_combine($columns, $value);
                        }
                    }
                }else{
                    if(isset($colModels[$columns])){
                        $columns = $colModels[$columns]['v_column'];
                    }
                }
                
                if($op && !is_array($columns) && !is_array($values)){
                    if($op=='like'){
                        $likeKeyword = $dataProvider->db->driverName === 'pgsql' ? 'ilike' : 'like';
                        $dataProvider->query->andFilterWhere([$likeKeyword, $columns, $values]);
                    }else{
                        $dataProvider->query->andFilterWhere([$op, $columns, $values]);
                    }
                }else{
                    if(is_array($columns) && is_array($values)){
                        $dataProvider->query->andWhere(['in', $columns, $values]);
                    }else{
                        $dataProvider->query->andFilterWhere([$columns => $values]);
                    }                    
                }
                break;
            default: // 未知
                throw new \yii\web\HttpException(200, Yii::t('datacenter','未知的数据集类型'));
                break;
        }
    }
    
    // 预处理数据
    public function findModel($condition, $muli = false)
    {
        $query = parent::findByCondition($condition)->with([
            'columns.column',
            'columns.model.sourceRelation.sourceModel',
            'columns.model.sourceRelation.targetModel',
            'columns.model.columns.model',
            'mainModel.source',
            'mainModel.sourceRelation.sourceModel',
            'mainModel.sourceRelation.targetModel',
        ]);
        
        return ($muli ? $query->all() : $query->one());
    }
    
    // 返回API请求地址
    public function getV_apiurl($cache='1')
    {
        $params = Yii::$app->request->get("SysConfig",[]);
        $arr = [
            'report-api/set-data',
            'cache'=>$cache,
            'id'=>$this['id'],
            'access-token'=>Yii::$app->user->identity['access_token'],
        ];
        $params && ($arr['SysConfig'] = $params);
        return \yii\helpers\Url::to($arr);
    }
    
    // 返回汇总字段（预留）
    public function getV_summary()
    {
        return [];
    }
    
    // 组装数据集数据
    protected function prepareModels()
    {
        $dataProvider = $this->getDataProvider();
        
        // 应用过滤条件
        $this->setSearchModels(false);
        $data = $dataProvider->getModels();
        foreach($data as $k=>$v){
            $data[$k] = $this->formatValue($v, $this->columns);
        }
        return $data;
    }
    
    // 返回数据集提供器
    protected function prepareDataProvider()
    {
        switch($this->set_type){
            case 'sql': // SQL
                $dataProvider = $this->data_sql();
                break;
            case 'excel': // EXCEL文档
                $dataProvider = $this->data_excel();
                break;
            case 'script': // 脚本
                $dataProvider = $this->data_script();
                break;
            case 'model': // 数据库模型
                $dataProvider = $this->data_model();
                break;
            default: // 未知
                throw new \yii\web\HttpException(200, Yii::t('datacenter','未知的数据集类型'));
                break;
        }
        
        return $dataProvider;
    }
    
    // 返回SQL数据提供器
    private function data_sql()
    {
        throw new \yii\web\HttpException(200, Yii::t('datacenter','未知的数据集类型'));
        /*
        $provider = new SqlDataProvider([
            'sql' => $this->run_sql,
            'totalCount' => $count,
            'pagination' => ['pageSizeLimit' => [1, 500]],
        ]);*/
    }
    
    // 返回EXCEL数据提供器
    private function data_excel()
    {
        throw new \yii\web\HttpException(200, Yii::t('datacenter','未知的数据集类型'));
        /*
        $provider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => ['pageSizeLimit' => [1, 500]],
        ]);*/
    }
    
    // 返回脚本数据提供器
    private function data_script()
    {
        throw new \yii\web\HttpException(200, Yii::t('datacenter','未知的数据集类型'));
        /*
         $provider = new ArrayDataProvider([
         'allModels' => $data,
         'pagination' => ['pageSizeLimit' => [1, 500]],
         ]);*/
    }
    
    // 返回模型数据提供器
    private function data_model()
    {
        if(!$this->mainModel || !$this->mainModel['source'] || !($db = $this->mainModel['source']->getSourceDb())) return null;
        
        $query = new \yii\db\Query();
        $query->from("{$this->mainModel['v_model']} as {$this->mainModel['v_alias']}");
        
        // 关联模型
        $allModels = $models = $this->getV_relation_models();
        unset($models[$this->mainModel['id']]);
        $this->mainModel->joinModel($query, $models);
        if($models){
            $model = reset($models);
            throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的模型关系')."{$model['tb_label']}({$model['id']}.{$model['tb_name']})");
        }
        
        // 查询字段
        $this->selectColumns($query);
        $this->rel_where && $query->andWhere($this->formatSql($this->rel_where));
        $this->rel_group && $query->addGroupBy($this->formatSql($this->rel_group));
        $this->rel_order && $query->addOrderBy($this->formatSql($this->rel_order));

        $dataProvider = new \yii\data\ActiveDataProvider([
            'db' => $db,
            'query' => $query,
            'pagination' => ['pageSizeLimit' => [1, 500]],
        ]);
        
        // 增加排序关联
        $sort = $dataProvider->getSort();
        foreach($allModels as $model){
            $model->orderColumns($sort);
        }
        $this->orderColumns($sort);
        
        return $dataProvider;
    }
}
