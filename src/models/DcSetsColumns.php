<?php
/**
 * 数据库表 "dc_sets_columns" 的模型对象.
 * @property int $id 流水号
 * @property int $set_id 数据集
 * @property int $model_id 归属模型
 * @property string $name 名称
 * @property string $label 标签
 * @property int $is_search 是否可查
 * @property string $type 类型
 * @property string $formula 计算公式
 * @property string $fun 处理函数
 */

namespace datacenter\models;

use Yii;

class DcSetsColumns extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_sets_columns';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name', 'label', 'set_id'], 'required'],
            [['set_id', 'is_search', 'paixu'], 'integer'],
            [['name', 'label', 'type', 'model_id', 'formula', 'fun', 'is_frozen', 'search_params'], 'safe'],
            [['name', 'label', 'type', 'fun'], 'string', 'max' => 50],
            [['formula'], 'string', 'max' => 150],
            [['label'], 'unique', 'filter' => "set_id='{$this->set_id}'"],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'set_id' => Yii::t('datacenter', '数据集'),
            'model_id' => Yii::t('datacenter', '归属模型'),
            'name' => Yii::t('datacenter', '名称'),
            'label' => Yii::t('datacenter', '标签'),
            'is_search' => Yii::t('datacenter', '是否可查'),
            'type' => Yii::t('datacenter', '查询类型'),
            'search_params' => Yii::t('datacenter', '查询参数'),
            'formula' => Yii::t('datacenter', '计算公式'),
            'fun' => Yii::t('datacenter', '处理函数'),
            'paixu' => Yii::t('datacenter', '排序'),
            'is_frozen' => Yii::t('datacenter', '是否冻结'),
        ];
    }
    
    // 获取数据集关系
    public function getSets(){
        return $this->hasOne(DcSets::className(), ['id'=>'set_id']);
    }
    
    // 获取数据模型关系
    public function getModel(){
        return $this->hasOne(DcModel::className(), ['id'=>'model_id']);
    }
    
    // 获取数据模型字段关系
    public function getColumn(){
        return $this->hasOne(DcAttribute::className(), ['model_id'=>'model_id', 'name'=>'name']);
    }
    
    // 获取数据集类型
    public function getV_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('config_type', ($val !== null ? $val : $this->type));
    }
    
    // 获取数据集类型
    public function getV_fun($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_db_fun', ($val !== null ? $val : $this->fun));
    }
    
    // 获取是否冻结
    public function getV_is_frozen($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_frozen));
    }
    
    // 获取数据集类型
    public function getV_is_search($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_search));
    }
    
    // 返回格式化名称
    public function getV_name(){
        return $this->sets['title'].'.'.$this->label.($this->name ? "[{$this->name}]" : "");
    }
    
    // 返回字段格式化名称
    public function getV_column()
    {
        return "{$this->model['v_alias']}.{$this->v_field}";
    }
    
    // 返回字段格式化别名
    public function getV_column_alias()
    {
        return "{$this->model['v_alias']}.{$this->v_field} as {$this->v_alias}";
    }
    
    // 返回字段别名
    public function getV_alias()
    {
        return "s_{$this->id}";
    }
    
    // 返回字段原名称
    public function getV_field()
    {
        return $this->name;
    }
    
    // 返回计算公式替换内容
    public function getV_label()
    {
        return $this->label;
    }
    
    // 是否允许排序
    public function getV_order()
    {
        return ($this->model_id>0&&$this->column&&!$this->formula&&!$this->fun ? true : false);
    }
    
    // 返回计算公式替换内容
    public function getV_format_label()
    {
        return "{{$this->v_label}}";
    }
    
    // 配置参数数组
    public function getV_search_params()
    {
        $search_params = $this->search_params ? $this->search_params : "";
        $result = [];
        $list = explode("\n", $search_params);
        if($list){
            foreach($list as $val){
                if(stripos($val,'|')===false){
                    $result[$val] = $val;
                }else{
                    list($k,$v) = explode('|',$val);
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }
    
    // 配置参数网络
    public function getV_search_ajax()
    {
        $search_params = $this->search_params ? $this->search_params : "";
        if(stripos($search_params, '.')!==false){
            return \yii\helpers\Url::to(['/config/sys-config/select2','key'=>$this->key]);
        }else{
            return \yii\helpers\Url::to($search_params);
        }
    }
    
    // 保存前动作
    public static $_updateModelIds = [];
    public function beforeSave($insert)
    {
        if($this->model_id && !in_array($this->model_id, DcSetsColumns::$_updateModelIds) 
            && $this->sets && $this->sets['set_type']=='model' && $this->sets->mainModel
        ){
            //$models = \yii\helpers\ArrayHelper::map($this->sets['columns'], 'model_id', 'model');
            $models = $this->sets->getV_relation_models();
            $models[$this->model_id] = $this->model;
            unset($models[$this->sets['mainModel']['id']]);
            $query = new \yii\db\Query();
            $query->from("{$this->sets['mainModel']['v_model']} as {$this->sets['mainModel']['v_alias']}");
            $this->sets->mainModel->joinModel($query, $models);
            if($models){
                $model = reset($models);
                throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的模型关系')."{$model['tb_label']}({$model['id']}.{$model['tb_name']})");
            }else{
                self::$_updateModelIds[] = $this->model_id; // 记录已存在模型关系，不用多次更新
            }
        }
        
        return parent::beforeSave($insert);
    }
    
    // 查询器增加查询字段
    public function selectColumn(\yii\db\Query $query)
    {
        if(!$this->formula){
            if($this->fun){
                return $query->addSelect(["{$this->fun}({$this->v_column}) as {$this->v_alias}"]);
            }else{
                return $query->addSelect(["{$this->v_column_alias}"]);
            }
        }
        return $this;
    }
    
    // 查询器增加字段过滤
    public function whereColumn(\yii\db\Query $query, $values)
    {
        if(is_array($values) || in_array($this->column['type'], [
            'tinyint', 'bit', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'float', 'double',
            'real', 'decimal', 'numeric',
        ])){
            $query->andFilterWhere([$this->v_column => $values]);
        }else{
            if($values && strpos($values, '至') && in_array($this->column['type'], [
                'datetime', 'year', 'date', 'time', 'timestamp',
            ])){ // 时间
                list($startTime, $endTime) = explode('至', $values);
                $query->andFilterWhere(['>=', $this->v_column, trim($startTime)]);
                $query->andFilterWhere(['<=', $this->v_column, trim($endTime)]);
            }else{
                $db = $this->model['source'] ? $this->model['source']->getSourceDb() : null;
                $likeKeyword = ($db && $db->driverName === 'pgsql') ? 'ilike' : 'like';
                $query->andFilterWhere([$likeKeyword, $this->v_column, $values]);
            }
        }
        
        return $this;
    }
    
    // 增加排序索引
    public function orderColumn(\yii\data\Sort $sort)
    {
        $sort->attributes[$this->v_alias] = [
            'asc' => [$this->v_column => SORT_ASC],
            'desc' => [$this->v_column => SORT_DESC],
            'label' => $this->label,
        ];
        return $this;
    }
    
    // 保存后动作
    public function afterSave($insert, $changedAttributes)
    {
        // 更新数据集的relation_models
        if($this->model_id && $this->sets && $this->sets['set_type']=='model'){
            self::$updateSetRelationModelIds[$this->set_id] = $this->set_id;
            Yii::$app->controller->off('afterAction', ['\datacenter\models\DcSetsColumns', 'saveSetRelationModel']);
            Yii::$app->controller->on('afterAction', ['\datacenter\models\DcSetsColumns', 'saveSetRelationModel']);
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    // 更新数据集关联模型
    public static $updateSetRelationModelIds = [];
    public static function saveSetRelationModel()
    {
        // 更新数据集的relation_models信息
        if(self::$updateSetRelationModelIds){
            $setList = DcSets::find()->where(['id'=>self::$updateSetRelationModelIds])->with(['columns'])->all();
            foreach($setList as $set){
                $modelIds = $set['columns'] ? \yii\helpers\ArrayHelper::map($set['columns'], 'model_id', 'model_id') : [];
                $set['relation_models'] = implode(',', $modelIds);
                $set->save(false);
            }
        }
        
    }
}
