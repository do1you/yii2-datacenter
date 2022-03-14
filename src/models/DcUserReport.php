<?php
/**
 * 数据库表 "dc_user_report" 的模型对象.
 * @property int $id 流水号
 * @property int $user_id 用户
 * @property int $report_id 报表
 * @property int $paixu 排序
 * @property int $is_collection 是否收藏
 * @property string $alias_name 别名
 * @property string $create_time 授权时间
 * @property int $grant_user 授权用户
 */

namespace datacenter\models;

use Yii;

class DcUserReport extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_user_report';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['user_id', 'report_id'], 'required'],
            [['user_id', 'report_id', 'paixu', 'is_collection', 'grant_user'], 'integer'],
            [['alias_name', 'create_time'], 'safe'],
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
            'report_id' => Yii::t('datacenter', '报表'),
            'paixu' => Yii::t('datacenter', '排序'),
            'is_collection' => Yii::t('datacenter', '是否收藏'),
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
        }
        
        return parent::beforeSave($insert);
    }
    
    // 获取报表关系
    public function getReport(){
        return $this->hasOne(DcReport::className(), ['id'=>'report_id']);
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
    
    // 获取是否收藏
    public function getV_is_collection($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_collection));
    }
    
    // 返回报表名称
    public function getV_name()
    {
        return ($this->alias_name ? $this->alias_name : $this->report['title']);
    }
    
    // 获取用户包含权限的报表
    public function allReport($userId='0',$where=[],$group=false)
    {
        $query = DcReport::find()->with(['cat.parent.parent.parent.parent','user'])->alias('t')->where(['t.state'=>'0'])->orderBy("t.paixu desc,t.id asc");
        
        if($userId!='1'){
            //取角色和用户包含的报表权限
            $roleIds = \yii\helpers\ArrayHelper::map(\webadmin\modules\authority\models\AuthUserRole::findAll(['user_id'=>$userId]), 'role_id', 'role_id');
            $query->joinWith(['userReport as u','roleReport as r']);
            $query->andWhere(['or',
                ['=','t.create_user',$userId],
                ['=','u.user_id',$userId],
                ['in','r.role_id',$roleIds],
            ]);
            $where && $query->andWhere($where);
        }elseif($where){
            $query->joinWith(['userReport as u']);
            $query->andWhere($where);
        }
        
        $list = $query->all();
        if($group){
            $list = \yii\helpers\ArrayHelper::map($list, 'id', 'v_self', 'cat_id');
        }else{
            $list = \yii\helpers\ArrayHelper::map($list, 'id', 'v_self');
        }
        return $list;
    }
}
