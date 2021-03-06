<?php
/**
 * 数据库表 "dc_attribute" 的模型对象.
 * @property int $id 流水号
 * @property string $name 名称
 * @property string $label 标签
 * @property string $type 类型
 * @property int $length 长度
 * @property string $default 默认值
 * @property int $model_id 模型
 * @property int $is_visible 是否可见
 */

namespace datacenter\models;

use Yii;
use datacenter\attributes\Attribute;

class DcAttribute extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_attribute';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['label', 'length', 'default'], 'safe'],
            [['length', 'model_id', 'is_visible'], 'integer'],
            [['name', 'label', 'type'], 'string', 'max' => 50],
            [['default'], 'string', 'max' => 30],
            [['name'], 'unique', 'filter' => "model_id='{$this->model_id}'"],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'name' => Yii::t('datacenter', '名称'),
            'label' => Yii::t('datacenter', '标签'),
            'type' => Yii::t('datacenter', '类型'),
            'length' => Yii::t('datacenter', '长度'),
            'default' => Yii::t('datacenter', '默认值'),
            'model_id' => Yii::t('datacenter', '模型'),
            'is_visible' => Yii::t('datacenter', '是否可见'),
        ];
    }
    
    // 获取模型关系
    public function getModel(){
        return $this->hasOne(DcModel::className(), ['id'=>'model_id']);
    }
    
    // 获取数据集字段关系
    public function getSetsColumns(){
        return $this->hasMany(DcSetsColumns::className(), ['model_id'=>'model_id', 'name'=>'name']);
    }
    
    // 获取是否可见
    public function getV_is_visible($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_visible));
    }
    
    // 获取数据类型
    public function getV_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_column_type', ($val !== null ? $val : $this->type));
    }
    
    // 返回格式化名称
    public function getV_name()
    {
        return $this->model['tb_name'].'.'.$this->name.($this->label ? "【{$this->label}】" : "");
    }
    
    // 返回字段格式化名称
    public function getV_column()
    {
        return "{$this->model['v_alias']}.{$this->v_field}";
    }
    
    // 返回字段格式化别名
    public function getV_column_alias()
    {
        return "{$this->v_column} as {$this->v_alias}";
    }
    
    // 返回字段别名
    public function getV_alias()
    {
        return "c_{$this->id}";
    }
    
    // 返回字段原名称
    public function getV_field()
    {
        return $this->name;
    }
    
    // 返回字段默认值
    public function getV_default_value()
    {
        if(strlen($this->default)){
            return $this->default;
        }else{
            return '';
        }
    }
    
    // 删除判断
    public function delete()
    {
        if($this->setsColumns){
            foreach($this->setsColumns as $item){
                $item->delete();
            }
        }
        
        return parent::delete();
    }
    
   
}
