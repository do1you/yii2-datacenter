<?php
/**
 * 数据库表 "dc_model" 的模型对象.
 * @property int $id 流水号
 * @property string $tb_name 表名
 * @property string $tb_label 标签
 * @property int $paixu 排序
 * @property int $cat_id 分类
 * @property int $source_db 数据源
 * @property int $is_visible 是否可见
 * @property string $update_time 更新时间
 */

namespace datacenter\models;

use Yii;

class DcModel extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_model';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['tb_name'], 'required'],
            [['paixu', 'cat_id', 'source_db', 'is_visible'], 'integer'],
            [['update_time', 'tb_label'], 'safe'],
            [['tb_name', 'tb_label'], 'string', 'max' => 80],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'tb_name' => Yii::t('datacenter', '表名'),
            'tb_label' => Yii::t('datacenter', '标签'),
            'paixu' => Yii::t('datacenter', '排序'),
            'cat_id' => Yii::t('datacenter', '分类'),
            'source_db' => Yii::t('datacenter', '数据源'),
            'is_visible' => Yii::t('datacenter', '是否可见'),
            'update_time' => Yii::t('datacenter', '更新时间'),
        ];
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        $this->update_time = date('Y-m-d H:i:s');
        
        return parent::beforeSave($insert);
    }
    
    // 关联删除
    public function afterDelete()
    {
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
        
        return parent::afterDelete();
    }
    
    // 获取模型关系
    public function getSource(){
        return $this->hasOne(DcSource::className(), ['id'=>'source_db']);
    }
    
    // 获取分类关系
    public function getCat(){
        return $this->hasOne(DcCat::className(), ['id'=>'cat_id']);
    }
    
    // 获取字段属性关系
    public function getColumns(){
        return $this->hasMany(DcAttribute::className(), ['model_id' => 'id']);
    }
    
    // 获取源模型关系
    public function getSourceRelation(){
        return $this->hasMany(DcRelation::className(), ['source_model' => 'id']);
    }
    
    // 获取目标模型关系
    public function getTargetRelation(){
        return $this->hasMany(DcRelation::className(), ['target_model' => 'id']);
    }
    
    // 获取是否可见
    public function getV_is_visible($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_visible));
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
    
    // 标签格式化
    public function getV_tb_name()
    {
        return $this->source['dbname'].".{$this->tb_name}[{$this->id}]";
    }
    
    // 返回表名格式化
    public function getV_model()
    {
        return "{$this->tb_name}";
    }
    
    // 返回字段别名
    public function getV_alias()
    {
        return "mod_{$this->id}";
    } 
    
    // 查询所有字段信息
    public function selectColumns(\yii\db\Query $query)
    {
        foreach($this->columns as $col){
            $col->selectColumn($query);
        }
        
        return $this;
    }
    
    // 增加排序索引
    public function orderColumns(\yii\data\Sort $sort)
    {
        foreach($this->columns as $col){
            $col->orderColumn($sort);
        }
        return $this;
    }
    
    // 连接到指定模型
    public function joinModel(\yii\db\Query $query, &$models=[])
    {
        if(isset($models[$this->id])) unset($models[$this->id]);
        
        $relations = \yii\helpers\ArrayHelper::map($this->sourceRelation, 'target_model', 'v_self');
        $joinModels = [];
        foreach($models as $k=>$model){
            if(empty($model)){
                unset($models[$k]);
            }
            if(isset($relations[$model['id']])){
                $query->leftJoin("{$model['tb_name']} as mod_{$model['id']}", $relations[$model['id']]['v_source_col_on']);
                
                $joinModels[$model['id']] = $model;
                unset($models[$k]);
            }
        }
        
        if(!empty($models) && $joinModels){
            foreach($joinModels as $model){
                $model->joinModel($query, $models);
            }
        }
        
        return $this;
    }
    
    // 构建数据集
    public function createDataSet()
    {
        $setModel = new DcSets();
        if($setModel->load([
            'title' => $this->tb_label,
            'cat_id' => $this->cat_id,
            'set_type' => 'model',
            'main_model' => $this->id,
            'relation_models' => "{$this->id}",
        ], '') && $setModel->save()){
            foreach($this->columns as $col){
                $colModel = new DcSetsColumns();
                $colModel->load([
                    'set_id' => $setModel->id,
                    'model_id' => $this->id,
                    'name' => $col->name,
                    'label' => $col->label,
                ],'');
                $colModel->save(false);
            }
            return true;
        }else{
            return false;
        }
    }
    
    // 复制模型
    public function copyModel()
    {
        $model = new DcModel();
        if($model->load([
            'tb_name' => $this->tb_name,
            'tb_label' => $this->tb_label,
            'paixu' => $this->paixu,
            'cat_id' => $this->cat_id,
            'source_db' => $this->source_db,
            'is_visible' => $this->is_visible,
        ], '') && $model->save()){
            if($this->columns){
                // 复制字段
                foreach($this->columns as $col){
                    $colModel = new DcAttribute();
                    $colModel->load([
                        'name' => $col->name,
                        'label' => $col->label,
                        'type' => $col->type,
                        'length' => $col->length,
                        'default' => $col->default,
                        'model_id' => $model->id,
                        'is_visible' => $col->is_visible,
                    ],'');
                    $colModel->save(false);
                }
            }
            
            if($this->sourceRelation){
                // 复制主动关系
                foreach($this->sourceRelation as $item){
                    $relModel = new DcRelation();
                    $relModel->load([
                        'source_model' => $model->id,
                        'source_col' => $item->source_col,
                        'target_model' => $item->target_model,
                        'target_col' => $item->target_col,
                        'rel_type' => $item->rel_type,
                        'rel_where' => $item->rel_where,
                    ],'');
                    $relModel->save(false);
                }
            }
            
            if($this->targetRelation){
                // 复制被动关系
                foreach($this->targetRelation as $item){
                    $relModel = new DcRelation();
                    $relModel->load([
                        'source_model' => $item->source_model,
                        'source_col' => $item->source_col,
                        'target_model' => $model->id,
                        'target_col' => $item->target_col,
                        'rel_type' => $item->rel_type,
                        'rel_where' => $item->rel_where,
                    ],'');
                    $relModel->save(false);
                }
            }
            return true;
        }else{
            return false;
        }
    }
    
    
    
    
}
