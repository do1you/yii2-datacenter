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
            [['share_user', 'invalid_time'], 'required'],
            [['report_id'], 'required', 'when'=>function($model){
                return empty($model->set_id);
            }],
            [['set_id'], 'required', 'when'=>function($model){
                return empty($model->report_id);
            }],
            [['hash_key'], 'unique'],
            [['share_user', 'report_id', 'set_id'], 'integer'],
            [['alias_name', 'hash_key', 'search_values', 'user_ids', 'invalid_time', 'create_time', 'switch_type'], 'safe'],
            [['search_values'], 'string'],
            [['invalid_time', 'create_time'], 'safe'],
            [['alias_name'], 'string', 'max' => 150],
            [['hash_key'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 32],
            [['hash_key'], 'unique', 'filter' => "invalid_time>='".date('Y-m-d H:i:s')."'"],
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
            'password' => Yii::t('datacenter', '访问密码'),
            'v_url' => Yii::t('datacenter', '分享链接'),
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
        
        if(!$this->hash_key){
            //$this->hash_key = substr(md5(microtime(true)),-8);
            $str = substr(md5(microtime(true)),-5);
            $str1 = substr(base64_encode($str),0,5);
            $this->hash_key = str_shuffle($str.$str1);
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
    
    // 返回分享用户
    public function getV_user_ids()
    {
        return (is_array($this->user_ids) ? $this->user_ids : ($this->user_ids ? explode(',',$this->user_ids) : []));
    }
    
    // 获取归属类型
    public function getV_switch_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_switch_report', ($val !== null ? $val : $this->switch_type));
    }
    
    //  返回分享链接
    public function getV_url()
    {
        return \yii\helpers\Url::to(['share-view/view','h'=>$this->hash_key], true);
    }
    
    //  返回API数据链接
    public function getV_dataurl()
    {
        return \yii\helpers\Url::to(['share-view/data','h'=>$this->hash_key], true);
    }
    
    // 预处理数据
    public function findModel($condition, $muli = false)
    {
        $list = parent::findByCondition($condition)->all();
        $reportIds = $setIds = [];
        foreach($list as $item){
            if($item['report_id']){
                $reportIds[] = $item['report_id'];
            }elseif($item['set_id']){
                $setIds[] = $item['set_id'];
            }
        }
        $setList = $setIds ? DcSets::model()->getCache('findModel',[['id'=>$setIds], true]) : [];
        $setList = \yii\helpers\ArrayHelper::map($setList, 'id', 'v_self');
        $reportList = $reportIds ? DcReport::model()->getCache('findModel',[['id'=>$reportIds], true]) : [];
        $reportList = \yii\helpers\ArrayHelper::map($reportList, 'id', 'v_self');
        
        $result = [];
        foreach($list as $key=>$item){
            if($item['report_id'] && isset($reportList[$item['report_id']])){
                $model = $reportList[$item['report_id']]->forUserModel ? (clone $reportList[$item['report_id']]) : $reportList[$item['report_id']];
            }elseif($item['set_id'] && isset($setList[$item['set_id']])){
                $model = $setList[$item['set_id']]->forUserModel ? (clone $setList[$item['set_id']]) : $setList[$item['set_id']];
            }else{
                $model = null;
            }
            if($model){
                $model->forUserModel = $item;
                $result[] = $model;
            }
        }
        
        return ($muli ? $result : reset($result));
    }
    
    // 设置数据源
    public function setSource()
    {
        $search_values = $this->getV_search_values();
        if(!empty($search_values['source']) && is_array($search_values['source'])){
            $sIds = array_keys($search_values['source']);
            $models = $sIds ? \datacenter\models\DcSource::findAll($sIds) : [];
            $models = \yii\helpers\ArrayHelper::map($models, 'id', 'v_self');
            foreach($search_values['source'] as $sid=>$id){
                if(isset($models[$sid]) && $id){
                    Yii::$app->session[$models[$sid]['v_sessionName']] = $id;
                }
            }
        }
    }
}
