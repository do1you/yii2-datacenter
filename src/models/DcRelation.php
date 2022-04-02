<?php
/**
 * 数据库表 "dc_relation" 的模型对象.
 * @property int $id 流水号
 * @property int $source_model 源模型
 * @property string $source_col 源属性
 * @property int $target_model 目标模型
 * @property string $target_col 目标属性
 * @property int $rel_type 关系类型
 * @property string $rel_where 关系条件
 */

namespace datacenter\models;

use Yii;

class DcRelation extends \webadmin\ModelCAR
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
        return 'dc_relation';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['source_model', 'target_model'], 'integer'],
            [['source_model', 'target_model'], 'required'],
            [['source_col', 'target_col', 'is_reverse_save'], 'safe'],
            [['target_model'], 'compare', 'compareAttribute'=>'source_model', 'operator'=>'!='],
            [['source_model', 'target_model'], 'unique', 'targetAttribute' => ['source_model', 'target_model']],
            [['rel_type'], 'string', 'max' => 30],
            [['rel_where'], 'string', 'max' => 255],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'source_model' => Yii::t('datacenter', '源模型'),
            'source_col' => Yii::t('datacenter', '源属性关系'),
            'target_model' => Yii::t('datacenter', '目标模型'),
            'target_col' => Yii::t('datacenter', '目标属性关系'),
            'rel_type' => Yii::t('datacenter', '关系类型'),
            'rel_where' => Yii::t('datacenter', '关系条件'),
            'relation_col' => Yii::t('datacenter', '属性关系'),
            'is_reverse_save' => Yii::t('datacenter', '同步反向关系'),
        ];
    }
    
    // 获取源模型关系
    public function getSourceModel()
    {
        return $this->hasOne(DcModel::className(), ['id'=>'source_model']);
    }
    
    // 获取目标模型关系
    public function getTargetModel()
    {
        return $this->hasOne(DcModel::className(), ['id'=>'target_model']);
    }
    
    // 返回关联类型
    public function getV_rel_type($val=null)
    {
        $list = \webadmin\modules\config\models\SysLdItem::dd('dc_rel_type', ($val !== null ? $val : $this->rel_type));
        if($val===false && is_array($list)){
            unset($list['group'], $list['union']); // 数据集所特有的
        }
        return $list;
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
            foreach($arr as $key=>$val){
                $arr1[] = $key.'=>'.$val;
            }
        }
        return implode("<br>",$arr1);
    }
    
    // 返回目标属性关系文字表示
    public function getV_target_col_str()
    {
        return $this->getV_source_col_str($this->getV_target_col());
    }
    
    // 返回源属性关系on
    public function getV_source_col_on()
    {
        $cols = $this->getV_source_col();
        $ons = [];
        foreach($cols as $key=>$val){
            $ons[] = "{$this->sourceModel['v_alias']}.{$key} = {$this->targetModel['v_alias']}.{$val}";
        }
        
        return implode(" and ", $ons) . ($this->rel_where ? " and {$this->v_rel_where}" : "");
    }
    
    // 返回目标属性关系on
    public function getV_target_col_on()
    {
        $cols = $this->getV_target_col();
        $ons = [];
        foreach($cols as $key=>$val){
            $ons[] = "{$this->targetModel['v_alias']}.{$key} = {$this->sourceModel['v_alias']}.{$val}";
        }
        
        return implode(" and ", $ons) . ($this->rel_where ? " and {$this->v_rel_where}" : "");
    }
    
    // 替换rel_where里的条件
    public function getV_rel_where()
    {
        return str_replace([
            "{$this->targetModel['tb_name']}.",
            "{{$this->targetModel['tb_label']}}.",
        ],"{$this->targetModel['v_alias']}.",str_replace([
            "{$this->sourceModel['tb_name']}.",
            "{{$this->sourceModel['tb_label']}}.",
        ],"{$this->sourceModel['v_alias']}.",$this->rel_where));
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
            $model = DcRelation::find()->where([
                'source_model' => $this->target_model,
                'target_model' => $this->source_model,
            ])->andWhere("id!='{$this->id}'")->one();
            $model = $model ? $model : (new DcRelation);
            $model->load([
                'source_model' => $this->target_model,
                'target_model' => $this->source_model,
                'source_col' => $this->target_col,
                'target_col' => $this->source_col,
                'rel_type' => ($model['rel_type'] ? $model['rel_type'] : 'one'),
                'rel_where' => ($model['rel_where'] ? $model['rel_where'] : $this->rel_where),
            ],'');
            $model->is_reverse_save = false;
            $model->save(false);
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    
    
    
    
    
    
    
    
    
    
}
