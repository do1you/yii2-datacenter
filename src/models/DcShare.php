<?php
/**
 * 数据库表 "dc_share" 的模型对象.
 * @property int $id 流水号
 * @property int $share_user 分享用户
 * @property int $report_id 报表
 * @property int $set_id 数据集
 * @property string $alias_name 别名
 * @property string $hash_key 哈希主键
 * @property string $search_values 查询参数
 * @property string $user_ids 授权用户
 * @property string $invalid_time 失效时间
 * @property string $create_time 创建时间
 */

namespace datacenter\models;

use Yii;

class DcShare extends \webadmin\ModelCAR
{
    public $switch_type;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_share';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['share_user'], 'required'],
            [['report_id'], 'required', 'when'=>function($model){
                return empty($model->set_id);
            }],
            [['set_id'], 'required', 'when'=>function($model){
                return empty($model->report_id);
            }],
            [['share_user', 'report_id', 'set_id'], 'integer'],
            [['alias_name', 'hash_key', 'search_values', 'user_ids', 'invalid_time', 'create_time', 'switch_type'], 'safe'],
            [['search_values'], 'string'],
            [['invalid_time', 'create_time'], 'safe'],
            [['alias_name'], 'string', 'max' => 150],
            [['hash_key'], 'string', 'max' => 50],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'share_user' => Yii::t('datacenter', '分享用户'),
            'report_id' => Yii::t('datacenter', '报表'),
            'set_id' => Yii::t('datacenter', '数据集'),
            'alias_name' => Yii::t('datacenter', '别名'),
            'hash_key' => Yii::t('datacenter', '哈希主键'),
            'search_values' => Yii::t('datacenter', '查询参数'),
            'user_ids' => Yii::t('datacenter', '访问用户'),
            'invalid_time' => Yii::t('datacenter', '失效时间'),
            'create_time' => Yii::t('datacenter', '创建时间'),
            'switch_type' => Yii::t('datacenter', '归属类型'),
        ];
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        if($insert){
            $this->create_time = date('Y-m-d H:i:s');
            $this->share_user = $this->share_user ? $this->share_user : Yii::$app->user->id;
        }
        
        if(is_array($this->user_ids)){
            $this->user_ids = implode(',',$this->user_ids);
        }
        
        return parent::beforeSave($insert);
    }
    
    // 查询后
    public function afterFind()
    {
        $this->switch_type = $this->report_id>0 ? 1 : 2;
        return parent::afterFind();
    }
    
    // 获取被分享用户关系
    public function getShareUser()
    {
        return $this->hasOne(\webadmin\modules\authority\models\AuthUser::className(), ['id'=>'share_user']);
    }
    
    // 获取报表关系
    public function getReport(){
        return $this->hasOne(DcReport::className(), ['id'=>'report_id']);
    }
    
    // 获取数据集关系
    public function getSet(){
        return $this->hasOne(DcSets::className(), ['id'=>'set_id']);
    }
    
    // 返回报表名称
    public function getV_name()
    {
        return ($this->alias_name ? $this->alias_name : ($this->report_id>0 ? $this->report['title'] : $this->set['title']));
    }
    
    // 返回过滤条件
    public function getV_search_values()
    {
        return (is_array($this->search_values) ? $this->search_values : ($this->search_values ? json_decode($this->search_values,1) : []));
    }
    
    // 获取归属类型
    public function getV_switch_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_switch_report', ($val !== null ? $val : $this->switch_type));
    }
}
