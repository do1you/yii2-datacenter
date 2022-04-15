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
    use \datacenter\models\ReportOrmTrait;
    
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
     * 数据集所属的报表实例
     */
    public $report;
    
    /**
     * 数据提供器驱动类
     */
    const dataProviderMap = [
        'model' => '\datacenter\base\ActiveDataProvider', // 模型
        'excel' => '\datacenter\base\ExcelDataProvider', // EXCEL
        'sql' => '\datacenter\base\SqlDataProvider', // SQL
        'script' => '\datacenter\base\ScriptDataProvider', // 脚本
    ];
            
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
            [['main_model', 'state', 'cat_id', 'source_id'], 'integer'],
            [['run_sql'], 'string'],
            [['update_time'], 'safe'],
            [['title', 'set_type'], 'string', 'max' => 50],
            [['relation_models'], 'string', 'max' => 255],
            [['run_script', 'excel_file'], 'string', 'max' => 150],
            [['rel_where'], 'string', 'max' => 200],
            [['rel_group', 'rel_order', 'rel_having'], 'string', 'max' => 100],
            [['excel_file'], 'required', 'when' => function ($model) {
                return ($model->set_type=='excel');
            }],
            [['run_script'], 'required', 'when' => function ($model) {
                return ($model->set_type=='script');
            }],
            [['run_sql', 'source_id'], 'required', 'when' => function ($model) {
                return ($model->set_type=='sql');
            }],
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
            'source_id' => Yii::t('datacenter', 'SQL数据源'),
            'excel_file' => Yii::t('datacenter', 'EXCEL文件'),
            'state' => Yii::t('datacenter', '状态'),
            'rel_where' => Yii::t('datacenter', '条件'),
            'rel_group' => Yii::t('datacenter', '分组'),
            'rel_having' => Yii::t('datacenter', '分组条件'),
            'rel_order' => Yii::t('datacenter', '排序'),
            'update_time' => Yii::t('datacenter', '更新时间'),
            'cat_id' => Yii::t('datacenter', '数据集分类'),
        ];
    }
    
    /**
     * 定义数据行为
     */
    public function behaviors()
    {
        return [
            // 可以直接调用数据输出提供器的方法
            'reportDataBehaviors' => [
                'class' => \datacenter\behaviors\ReportDataBehaviors::className(),
                'sets' => $this,
            ],
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
        return $this->hasMany(DcSetsColumns::className(), ['set_id' => 'id'])->addOrderBy("dc_sets_columns.is_frozen desc,dc_sets_columns.paixu desc,dc_sets_columns.id asc");
    }
    
    // 获取角色数据集关系
    public function getRoleSets(){
        return $this->hasMany(DcRoleAuthority::className(), ['source_id' => 'id'])->onCondition("dc_role_authority.source_type=4");
    }
    
    // 获取源数据集关系
    public function getSourceRelation(){
        return $this->hasMany(DcSetsRelation::className(), ['source_sets' => 'id']);
    }
    
    // 获取目标数据集关系
    public function getTargetRelation(){
        return $this->hasMany(DcSetsRelation::className(), ['target_sets' => 'id']);
    }
    
    // 获取数据报表字段属性关系
    public function getReportColumns(){
        return $this->hasMany(DcReportColumns::className(), ['set_id' => 'id']);
    }
    
    // 获取数据源关系
    public function getSource(){
        return $this->hasOne(DcSource::className(), ['id'=>'source_id']);
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
    
    // 返回运行脚本
    public function getV_run_script()
    {
        if(!$this->run_script) return false;
        return '\\datacenter\\base\\script\\'.ucfirst($this->run_script);
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
            return DcCat::authorityTreeOptions(Yii::$app->user->id);
        }else{
            if($val!== null){
                $model = DcCat::findOne($val);
                return ($model ? $model['name'] : '');
            }else{
                return ($this->cat ? $this->cat['name'] : '');
            }
        }
    }
    
    // 返回API请求地址
    public function getV_apiurl($cache='1')
    {
        $params = Yii::$app->request->post("SysConfig",Yii::$app->request->get("SysConfig",[]));
        $arr = [
            'report-api/set-data',
            'cache'=>$cache,
            'id'=>$this['id'],
            //'access-token'=>Yii::$app->user->identity['access_token'],
        ];
        $params && ($arr['SysConfig'] = $params);
        return \yii\helpers\Url::to($arr);
    }
    
    // 返回数据集关联关系
    public function getV_relation($retrnSet = false)
    {
        if($this->_relation_target && is_array($this->_relation_target)){
            foreach($this->_relation_target as $key=>$item){
                if($retrnSet) return $item;
                list($relation, $source) = $item;
                return $relation;
            }
        }
        
        return null;
    }
    
    // 保存后动作
    public function afterSave($insert, $changedAttributes)
    {
        // 保存默认数据集权限
        if($insert && Yii::$app instanceof \yii\web\Application){
            $roleIds = \yii\helpers\ArrayHelper::map(\webadmin\modules\authority\models\AuthUserRole::findAll(['user_id'=>Yii::$app->user->id]), 'role_id', 'role_id');
            foreach($roleIds as $roleId){
                $model = new DcRoleAuthority;
                $model->role_id = $roleId;
                $model->source_id = $this->id;
                $model->source_type = '4';
                $model->save(false);
            }
            \datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'], 86400, true);
        }
        
        // excel文件保存
        if($this->set_type=='excel'){
            $this->saveExcelData();
        }elseif($this->set_type=='sql'){
            $this->saveSqlData();
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    // 保存excel数据
    public function saveExcelData()
    {
        if(!$this->excel_file){
            return;
        }
        $array = \webadmin\ext\PhpExcel::readfile($this->excel_file);
        $titles = $array ? array_shift($array) : [];
        if($titles){
            $columns = \yii\helpers\ArrayHelper::map($this->columns, 'name', 'v_self');
            foreach($titles as $key=>$label){
                $model = isset($columns[$key]) ? $columns[$key] : (new DcSetsColumns());
                $model->load([
                    'set_id' => $this->id,
                    'name' => $key,
                    'label' => $label,
                ],'');
                $model->save(false);
                unset($columns[$key]);
            }
            foreach($columns as $item){
                if(!$item->formula) $item->delete();
            }
        }
    }
    
    // 保存SQL数据
    public function saveSqlData()
    {
        if(!$this->run_sql || !$this->source_id || !$this->source || !($db = $this->source->getSourceDb())){
            return;
        }
        
        $sql = $db->getQueryBuilder()->buildOrderByAndLimit($this->run_sql, [], 1, 0);
        $titles = $db->createCommand($sql)->queryOne();
        if($titles){
            $columns = \yii\helpers\ArrayHelper::map($this->columns, 'name', 'v_self');
            foreach($titles as $key=>$value){
                $model = isset($columns[$key]) ? $columns[$key] : (new DcSetsColumns());
                $model->load([
                    'set_id' => $this->id,
                    'name' => $key,
                    'label' => (($model->id && $model->name==$key && $model->label) ? $model->label : $key),
                ],'');
                $model->save(false);
                unset($columns[$key]);
            }
            foreach($columns as $item){
                if(!$item->formula) $item->delete();
            }
        }
    }
    
    // 删除判断
    public function delete()
    {
        if($this->getReportColumns()->count() > 0 ){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '该数据集下存在数据报表，请先删除数据数据报表！'));
        }
        
        if($this->id<0){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '系统内置数据集，禁止删除！'));
        }
        
        if($this->columns){
            foreach($this->columns as $item){
                $item->delete();
            }
        }
        
        if($this->sourceRelation){
            foreach($this->sourceRelation as $item){
                $item->delete();
            }
        }
        
        if($this->targetRelation){
            foreach($this->targetRelation as $item){
                $item->delete();
            }
        }
        
        return parent::delete();
    }
    
    // 预处理数据
    public function findModel($condition, $muli = false)
    {
        $query = parent::findByCondition($condition)->with([
            'source',
            'columns.column',
            'columns.sets.columns.model',
            'columns.model.sourceRelation.sourceModel',
            'columns.model.sourceRelation.targetModel',
            'columns.model.columns.model',
            'columns.model.source',
            'mainModel.source',
            'mainModel.sourceRelation.sourceModel',
            'mainModel.sourceRelation.targetModel',
        ]);
        
        return ($muli ? $query->all() : $query->one());
    }
    
    // 返回格式化SQL的参数
    public function formatSqlTpl()
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
        
        return [$this->_replace_params['search'], $this->_replace_params['replace']];
    }
    
    // 格式化SQL替换字符串参数
    public function formatSql($str)
    {
        list($search, $replace) = $this->formatSqlTpl();
        return str_replace($search, $replace, $str);
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
                $set->off(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_MODEL, [$set, 'targetAfterFindModels']);
                $set->off(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_SUMMARY, [$set, 'targetAfterFindSummary']);
                $set->on(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_MODEL, [$set, 'targetAfterFindModels']);
                $set->on(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_SUMMARY, [$set, 'targetAfterFindSummary']);
                if($relations[$set['id']]->rel_type=='union'){
                    $this->setPagination(false);
                    $set->setPagination(false);
                }
                $joinSets[$set['id']] = $set;
                unset($sets[$k]);
            }
        }
        
        if($joinSets){
            if(!empty($sets)){
                foreach($joinSets as $set){
                    if(!empty($sets)){
                        $set->joinSets($sets);
                    }
                }
            }
            $this->off(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_MODEL, [$this, 'sourceAfterFindModels']);
            $this->off(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_SUMMARY, [$this, 'sourceAfterFindSummary']);
            $this->on(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_MODEL, [$this, 'sourceAfterFindModels']);
            $this->on(\datacenter\base\ActiveDataProvider::$EVENT_AFTER_SUMMARY, [$this, 'sourceAfterFindSummary']);
        }
        
        return $this;
    }
    
    // 同时匹配查询关联数据集合
    public function sourceAfterFindModels()
    {
        if($this->_relation_source){
            foreach($this->_relation_source as $key=>$item){
                list($relation, $target) = $item;
                $relation->joinWhere($this, $target);
                $target->getModels();
            }
        }
    }
    
    // 同时匹配出汇总数据集合
    public function sourceAfterFindSummary()
    {
        if($this->_relation_source){
            foreach($this->_relation_source as $key=>$item){
                list($relation, $target) = $item;
                $relation->joinWhere($this, $target, false, true);
                $target->getSummary();
            }
        }
    }
    
    // 数据结果集合匹配
    public function targetAfterFindModels()
    {
        if($this->_relation_target){
            $target = $this;
            foreach($this->_relation_target as $key=>$item){
                list($relation, $source) = $item;
                $buckets = $values = [];
                
                if($relation['rel_type']=='union'){
                    $sourceList = $source->getModels();
                    $targetList = $target->getModels();
                    $source->setModels(\yii\helpers\ArrayHelper::merge($sourceList,$targetList));
                }else{
                    // 目标数据集合
                    $targetList = $target->getModels();
                    $columns = $relation->getV_target_columns($target);
                    if($relation['rel_type']=='group'){
                        $groupCols = $relation->getV_source_columns($target, true, $relation['v_group_col']);
                    }
                    foreach($targetList as $model){
                        $k = $this->getModelKey($model, $columns);
                        if($relation['rel_type']=='group' && !empty($groupCols)){
                            $gk = $this->getModelKey($model, $groupCols);
                            $buckets[$k][$gk] = $model;
                        }else{
                            $buckets[$k] = $model;
                        }
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
    }
    
    //　汇总数据集合匹配
    public function targetAfterFindSummary()
    {
        if($this->_relation_target){
            $target = $this;
            foreach($this->_relation_target as $key=>$item){
                list($relation, $source) = $item;
                $buckets = [];
                
                // 目标数据汇总
                $targetList = $target->getSummary();
                if($relation['rel_type']=='group'){
                    $groupCols = $relation->getV_source_columns($target, true, $relation['v_group_col']);
                }
                if($relation['rel_type']=='group' && !empty($groupCols)){
                    foreach($targetList as $model){
                        $gk = $this->getModelKey($model, $groupCols);
                        $buckets[$gk] = $model;
                    }
                }else{
                    $buckets = $targetList;
                }
                
                $sourceList = $source->getSummary();
                $sourceList['_'][$target['id']] = $buckets;
                $source->setSummary($sourceList);
            }
        }
    }
    
    // 目标数据集写入到源数据集查询条件
    public function filterSourceSearch($mainSet, $num=1)
    {
        if($this->_relation_target){
            foreach($this->_relation_target as $key=>$item){
                list($relation, $source) = $item;
                $relation->joinWhere($source, $this, true);
                
                if($mainSet['id']!=$source['id'] && $num<10){
                    $source->filterSourceSearch($mainSet, ($num+1));
                }
            }
        }
        return $this;
    }
    
    // 复制数据集
    public function copySets()
    {
        $model = new DcSets();
        $attributes = $this->attributes;
        unset($attributes['id']);
        if($model->load($attributes, '') && $model->save()){
            $columnsMap = [];
            if($this->columns){
                // 复制字段
                foreach($this->columns as $col){
                    $attributes = $col->attributes;
                    unset($attributes['id']);
                    $attributes['set_id'] = $model['id'];
                    $colModel = new DcSetsColumns();
                    $colModel->load($attributes,'');
                    $colModel->save(false);
                    $columnsMap[$col['id']] = $colModel['id'];
                }
            }
            
            if($this->sourceRelation){
                // 复制主动关系
                foreach($this->sourceRelation as $item){
                    $attributes = $item->attributes;
                    unset($attributes['id']);
                    $attributes['source_sets'] = $model['id'];
                    $relModel = new DcSetsRelation();
                    $relModel->load($attributes,'');
                    $relModel->source_col = $this->_copySetsRel($relModel['v_source_col'],$columnsMap);
                    $relModel->target_col = $this->_copySetsRel($relModel['v_target_col'],$columnsMap,true);
                    $relModel->save(false);
                }
            }
            
            if($this->targetRelation){
                // 复制被动关系
                foreach($this->targetRelation as $item){
                    $attributes = $item->attributes;
                    unset($attributes['id']);
                    $attributes['target_sets'] = $model['id'];
                    $relModel = new DcSetsRelation();
                    $relModel->load($attributes,'');
                    $relModel->source_col = $this->_copySetsRel($relModel['v_source_col'],$columnsMap,true);
                    $relModel->target_col = $this->_copySetsRel($relModel['v_target_col'],$columnsMap);
                    //$relModel->group_col = $this->_copySetsRel($relModel['v_group_col'],$columnsMap);
                    //$relModel->group_label
                    $relModel->save(false);
                }
            }
            return true;
        }else{
            return false;
        }
    }
    
    // 复制数据集字段关系匹配
    private function _copySetsRel($rels, $maps, $reverse = false)
    {
        $newRels = [];
        foreach($rels as $k=>$v){
            if($reverse){
                $newRels[$k] = isset($maps[$v]) ? $maps[$v] : $v;
            }else{
                $newRels[isset($maps[$k]) ? $maps[$k] : $k] = $v;
            }
        }
        
        return json_encode($newRels);
    }
    
    /**
     * 返回数据集提供器
     */
    public function prepareDataProvider()
    {
        $class = self::dataProviderMap[$this->set_type]!==null ? self::dataProviderMap[$this->set_type] : null;
        if(!$class){
            throw new \yii\web\HttpException(200, Yii::t('datacenter','未知的数据集类型'));
        }
        
        return Yii::createObject([
            'class' => $class,
            'sets' => $this,
            'forReport' => $this->report,
        ]);
    }
    
}
