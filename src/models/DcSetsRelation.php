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
use yii\base\ModelEvent;

class DcSetsRelation extends \webadmin\ModelCAR
{
    /**
     * 是否保存反向关系
     */
    public $is_reverse_save;
    
    /**
     * 分组的数据列缓存
     */
    private $_cache_group_columns;
    
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
            [['source_col', 'target_col', 'rel_type', 'is_reverse_save', 'group_col', 'group_label'], 'safe'],
            [['rel_type'], 'string', 'max' => 30],
            [['source_sets', 'target_sets'], 'required'],
            [['group_col', 'group_label'], 'required', 'when' => function ($model) {
                return ($model->rel_type=='group');
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
            'source_sets' => Yii::t('datacenter', '源数据集'),
            'source_col' => Yii::t('datacenter', '源属性'),
            'target_sets' => Yii::t('datacenter', '目标数据集'),
            'target_col' => Yii::t('datacenter', '目标属性'),
            'rel_type' => Yii::t('datacenter', '关系类型'),
            'group_col' => Yii::t('datacenter', '分组字段'),
            'group_label' => Yii::t('datacenter', '分组标签'),            
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
    
    // 获取分组标签关系
    public function getGroupLabel(){
        return $this->hasOne(DcSetsColumns::className(), ['id'=>'group_label']);
    }
    
    // 获取分组字段关系
    public function getGroupCol()
    {
        return (($cols = $this->getV_group_col()) ? DcSetsColumns::findAll(['id'=>$cols,'set_id'=>$this->target_sets]) : []);
    }
    
    // 返回关联关系类型
    public function getV_rel_type($val=null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_rel_type', ($val !== null ? $val : $this->rel_type));
    }
    
    // 返回分组字段
    public function getV_group_col()
    {
        return (is_array($this->group_col) ? $this->group_col : ($this->group_col ? explode(',',$this->group_col) : []));
    }
    
    // 返回属性关系数据所有字段集合
    public function getV_col_models()
    {
        $source_col = $this->getV_source_col();
        $ids = array_merge(array_keys($source_col), array_values($source_col));
        if($this->group_label){
            $ids[] = $this->group_label;
        }
        if($this->group_col){
            $ids = array_merge($ids, $this->getV_group_col());
        }
        $cModels = $ids ? DcSetsColumns::find()->where(['id'=>$ids])->with(['sets'])->all() : [];
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
        if(is_array($this->source_col) || is_array($this->target_col)){
            if(!is_array($this->source_col) || !is_array($this->target_col) || count($this->source_col)!=count($this->target_col)){
                throw new \yii\web\HttpException(200, Yii::t('datacenter','源属性和目标属性必须一一对应！'));
            }
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
        if(is_array($isTarget) || is_int($isTarget)){
            $columns = $isTarget;
        }else{
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
    
    // 返回被分组字段的全部属性
    public function getV_group_list($target = null, $f5 = false)
    {
        if($this->_cache_group_columns === null){
            $target = ($target && ($target instanceof DcSets) )? $target : $this->targetSets;
            $cacheKey = "datacenter/dynamicGroupColumns/{$this->id}/".md5(serialize($target));
            if(($list = Yii::$app->cache->get($cacheKey))===false || $f5){
                $list = [];
                if($this->rel_type=='group' && $this->group_label && $this->groupLabel['v_alias']){
                    $target = clone $target;
                    $target->off(DcSets::$EVENT_AFTER_MODEL, [$target, 'targetAfterFindModels']); // 关闭事件
                    $target->group($this->group_label)->setPagination(false);
                    $query = $target->getDataProvider()->query;
                    $query->select([]);
                    $this->groupLabel->selectColumn($query);
                    $list = $target->getModels(true);
                    $list = \yii\helpers\ArrayHelper::map($list, $this->groupLabel['v_alias'], $this->groupLabel['v_alias']);
                }
                
                Yii::$app->cache->set($cacheKey, $list, 3600);
            }
            
            $this->_cache_group_columns = $list;
        }
        
        return $this->_cache_group_columns;
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        $this->arrangement_col();
        
        if(is_array($this->group_col)){
            $this->group_col = implode(',', $this->group_col);
        }
        
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
                'rel_type' => ($model['rel_type'] ? $model['rel_type'] : 'one'),
            ],'');
            $model->is_reverse_save = false;
            $model->save(false);
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    // 从源数据集写入目标数据集关联条件
    public function joinWhere(DcSets $source, DcSets $target, $reverse = false)
    {
        $source = ($source && ($source instanceof DcSets) )? $source : $this->sourceSets;
        $target = ($target && ($target instanceof DcSets) )? $target : $this->targetSets;
        
        $target->setPagination(false); // 被关联的数据集不分页
        
        if($reverse){
            $target = clone $target;
            $columns = $this->getV_source_columns($source, false);
            $keys = $this->getV_target_columns($target);
            
            // 反向需要关闭事件处理
            $target->off(DcSets::$EVENT_AFTER_MODEL, [$target, 'targetAfterFindModels']);
        }else{
            $columns = $this->getV_target_columns($target, false);
            $keys = $this->getV_source_columns($source);
        }
        
        $values = [];
        $list = $reverse ? $target->getModels() : $source->getModels();
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
            $reverse ? $source->where($columns, $values) : $target->where($columns, $values);
        }else{
            $reverse ? $source->where($columns, []) : $target->where($columns, []);
        }
        
        // 分组写入
        if($this['rel_type']=='group'){
            $groupCols = $this->getV_source_columns($target, false, $this['v_group_col']);
            $groupCols && $target->group($groupCols);
        }
        
        return $this;
    }
}
