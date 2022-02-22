<?php
/**
 * 数据库表 "dc_sets_relation" 的模型对象.
 * @property int $id 流水号
 * @property int $source_sets 源数据集
 * @property string $source_col 源属性
 * @property int $target_sets 目标数据集
 * @property string $target_col 目标属性
 * @property string $rel_type 关系类型
 */

namespace datacenter\models;

use Yii;

class DcSetsRelation extends \webadmin\ModelCAR
{
    /**
     * 是否保存反向关系
     */
    public $is_reverse_save;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_sets_relation';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['source_sets', 'target_sets'], 'integer'],
            [['source_col', 'target_col', 'rel_type', 'is_reverse_save'], 'safe'],
            [['rel_type'], 'string', 'max' => 30],
            [['source_sets', 'target_sets'], 'required'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'source_sets' => Yii::t('datacenter', '源数据集'),
            'source_col' => Yii::t('datacenter', '源属性'),
            'target_sets' => Yii::t('datacenter', '目标数据集'),
            'target_col' => Yii::t('datacenter', '目标属性'),
            'rel_type' => Yii::t('datacenter', '关系类型'),
            'is_reverse_save' => Yii::t('datacenter', '同步反向关系'),
        ];
    }
    
    // 获取源数据集关系
    public function getSourceSets(){
        return $this->hasOne(DcSets::className(), ['id'=>'source_sets']);
    }
    
    // 获取目标数据集关系
    public function getTargetSets(){
        return $this->hasOne(DcSets::className(), ['id'=>'target_sets']);
    }
    
    // 返回关联关系类型
    public function getV_rel_type($val=null){
        return \webadmin\modules\config\models\SysLdItem::dd('dc_rel_type', ($val !== null ? $val : $this->rel_type));
    }
    
    // 返回属性关系数据字段集合
    public function getV_col_models()
    {
        $source_col = $this->getV_source_col();
        $ids = array_merge(array_keys($source_col), array_values($source_col));
        $cModels = $ids ? DcSetsColumns::findAll($ids) : [];
        return \yii\helpers\ArrayHelper::map($cModels, 'id', 'v_self');
    }
    
    // 返回源属性关系
    public function getV_source_col()
    {
        $this->arrangement_col();
        return (is_array($this->source_col) ? $this->source_col : ($this->source_col ? json_decode($this->source_col,true) : []));
    }
    
    // 返回目标属性关系
    public function getV_target_col()
    {
        $this->arrangement_col();
        return (is_array($this->target_col) ? $this->target_col : ($this->target_col ? json_decode($this->target_col,true) : []));
    }
    
    // 返回源属性关系文字表示
    public function getV_source_col_str($arr=null)
    {
        $arr = is_array($arr) ? $arr : $this->getV_source_col();
        $arr1 = [];
        if(is_array($arr)){
            $colModels = $this->getV_col_models();
            foreach($arr as $key=>$val){
                $arr1[] = (isset($colModels[$key]) ? $colModels[$key]['v_name'] : $key).'=>'.(isset($colModels[$val]) ? $colModels[$val]['v_name'] : $val);
            }
        }
        return implode("<br>",$arr1);
    }
    
    // 返回目标属性关系文字表示
    public function getV_target_col_str()
    {
        return $this->getV_source_col_str($this->getV_target_col());
    }
    
    // 处理源和目标字段关系数据
    public function arrangement_col()
    {
        // 格式化字段关系
        if(is_array($this->source_col) && is_array($this->target_col)){
            $params = $params1 = [];
            foreach($this->source_col as $k=>$v){
                if($v && !empty($this->target_col[$k])){
                    $params[$v] = $this->target_col[$k];
                    $params1[$this->target_col[$k]] = $v;
                }
            }
            
            $this->source_col = json_encode($params);
            $this->target_col = json_encode($params1);
        }
    }
    
    // 返回格式化源键名
    public function getV_source_columns($source = null, $isAlias = true, $isTarget = false)
    {
        $source = ($source && ($source instanceof DcSets) )? $source : $this->sourceSets;
        $rels = $this->getV_source_col();
        if(count($rels)>1){
            foreach($rels as $k=>$v){
                $columns[] = $isTarget ? $v : $k;
            }
        }elseif(count($rels) === 1){
            $columns = $isTarget ? reset($rels) : array_key_first($rels);
        }else{
            throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的源数据集合')."{$source['title']}({$source['id']})");
        }
        
        if($isAlias){
            $colModels = \yii\helpers\ArrayHelper::map($source['columns'], 'id', 'v_self');
            if(is_array($columns)){
                foreach($columns as $k=>$col){
                    if(isset($colModels[$col])){
                        $columns[$k] = $colModels[$col]['v_alias'];
                    }
                }
            }else{
                if(isset($colModels[$columns])){
                    $columns = $colModels[$columns]['v_alias'];
                }
            }
        }
        
        return $columns;
    }
    
    // 返回格式化目标键名
    public function getV_target_columns($target = null, $isAlias = true)
    {
        return $this->getV_source_columns($target, $isAlias, true);
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        $this->arrangement_col();
        
        return parent::beforeSave($insert);
    }
    
    // 保存后动作
    public function afterSave($insert, $changedAttributes)
    {
        // 保存反向的关系
        if($this->is_reverse_save){
            $model = DcSetsRelation::find()->where([
                'source_sets' => $this->target_sets,
                'target_sets' => $this->source_sets,
            ])->andWhere("id!='{$this->id}'")->one();
            $model = $model ? $model : (new DcSetsRelation);
            $model->load([
                'source_sets' => $this->target_sets,
                'target_sets' => $this->source_sets,
                'source_col' => $this->target_col,
                'target_col' => $this->source_col,
                'rel_type' => ($model['rel_type'] ? $model['rel_type'] : $this->rel_type),
            ],'');
            $model->is_reverse_save = false;
            $model->save(false);
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    // 从源数据集写入目标数据集关联条件
    public function joinWhere(DcSets $source, DcSets $target)
    {
        $source = ($source && ($source instanceof DcSets) )? $source : $this->sourceSets;
        $target = ($target && ($target instanceof DcSets) )? $target : $this->targetSets;
        
        $columns = $this->getV_target_columns($target, false);
        $keys = $this->getV_source_columns($source);
        
        $values = [];
        $list = $source->getModels();
        foreach($list as $item){
            if(is_array($keys)){
                $data = [];
                foreach($keys as $k){
                    $data[] = isset($item[$k]) ? $item[$k] : '';
                }
            }else{
                $data = isset($item[$keys]) ? $item[$keys] : '';
            }
            
            if($data && !in_array($data,$values)){
                $values[] = $data;
            }
        }

        // 写入条件
        if($values){
            $target->where($columns, $values);
        }else{
            $target->where($columns, []);
        }
        
        $target->setPagination(false); // 被关联的数据集不分页
        
        return $this;
    }
}
