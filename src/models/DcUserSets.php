<?php
/**
 * 数据库表 "dc_user_sets" 的模型对象.
 * @property int $id 流水号
 * @property int $user_id 用户
 * @property int $set_id 数据集
 * @property string $search_values 查询条件
 * @property int $paixu 排序
 * @property string $alias_name 别名
 * @property string $create_time 授权时间
 * @property int $grant_user 授权用户
 */

namespace datacenter\models;

use Yii;

class DcUserSets extends \webadmin\ModelCAR
{
    /**
     * 数据集所属的报表实例
     */
    public $report;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_user_sets';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['user_id', 'set_id'], 'required'],
            [['user_id', 'set_id', 'paixu', 'grant_user'], 'integer'],
            [['search_values', 'alias_name', 'create_time'], 'safe'],
            [['search_values'], 'string'],
            [['create_time'], 'safe'],
            [['alias_name'], 'string', 'max' => 70],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'user_id' => Yii::t('datacenter', '用户'),
            'set_id' => Yii::t('datacenter', '数据集'),
            'search_values' => Yii::t('datacenter', '查询条件'),
            'paixu' => Yii::t('datacenter', '排序'),
            'alias_name' => Yii::t('datacenter', '别名'),
            'create_time' => Yii::t('datacenter', '授权时间'),
            'grant_user' => Yii::t('datacenter', '授权用户'),
        ];
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        if($insert){
            $this->create_time = date('Y-m-d H:i:s');
            $this->user_id = $this->user_id ? $this->user_id : Yii::$app->user->id;
            $this->grant_user = $this->grant_user ? $this->grant_user : Yii::$app->user->id;
        }
        
        return parent::beforeSave($insert);
    }
    
    // 获取数据集关系
    public function getSet(){
        return $this->hasOne(DcSets::className(), ['id'=>'set_id']);
    }
    
    // 获取被授权用户关系
    public function getUser()
    {
        return $this->hasOne(\webadmin\modules\authority\models\AuthUser::className(), ['id'=>'user_id']);
    }
    
    // 获取授权用户关系
    public function getGrantUser()
    {
        return $this->hasOne(\webadmin\modules\authority\models\AuthUser::className(), ['id'=>'grant_user']);
    }
    
    // 返回报表名称
    public function getV_name()
    {
        return ($this->alias_name ? $this->alias_name : $this->set['v_title']);
    }
    
    // 返回过滤条件
    public function getV_search_values()
    {
        return (is_array($this->search_values) ? $this->search_values : ($this->search_values ? json_decode($this->search_values,1) : []));
    }
    
    // 预处理数据
    public function findModel($condition, $muli = false)
    {
        $list = parent::findByCondition($condition)->all();
        $ids = \yii\helpers\ArrayHelper::map($list, 'set_id', 'set_id');
        $setList = DcSets::model()->getCache('findModel',[['id'=>$ids], true]);
        $setList = \yii\helpers\ArrayHelper::map($setList, 'id', 'v_self');
        $result = [];
        foreach($list as $key=>$item){
            if(isset($setList[$item['set_id']])){
                if($setList[$item['set_id']]->forUserModel){
                    $model = clone $setList[$item['set_id']];
                }else{
                    $model = $setList[$item['set_id']];
                }
                $model->forUserModel = $item;
                $result[] = $model;
            }
        }
        
        return ($muli ? $result : reset($result));
    }
}
