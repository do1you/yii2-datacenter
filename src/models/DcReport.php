<?php
/**
 * 数据库表 "dc_report" 的模型对象.
 * @property int $id 流水号
 * @property string $title 报表名称
 * @property int $cat_id 报表分类
 * @property int $state 状态
 * @property string $set_ids 数据集合
 * @property int $paixu 排序
 * @property int $create_user 创建用户
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */

namespace datacenter\models;

use Yii;

class DcReport extends \webadmin\ModelCAR
{
    use \datacenter\models\ReportOrmTrait;
    
    /**
     * 关联的所有数据集
     */
    private $_setsList;
    
    /**
     * 关联的所有用户数据集
     */
    private $_userSetsList;
    
    /**
     * 关联的主数据集
     */
    private $_mainSet;
    
    /**
     * 数据集关联状态
     */
    private $_joinSetState;
     
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_report';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['title', 'create_time', 'update_time'], 'required'],
            [['cat_id', 'state', 'paixu', 'create_user'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['set_ids'], 'string', 'max' => 200],
            [['show_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'title' => Yii::t('datacenter', '报表名称'),
            'cat_id' => Yii::t('datacenter', '报表分类'),
            'state' => Yii::t('datacenter', '状态'),
            'set_ids' => Yii::t('datacenter', '数据集合'),
            'show_type' => Yii::t('datacenter', '显示方式'),
            'paixu' => Yii::t('datacenter', '排序'),
            'create_user' => Yii::t('datacenter', '创建用户'),
            'create_time' => Yii::t('datacenter', '创建时间'),
            'update_time' => Yii::t('datacenter', '更新时间'),
        ];
    }
    
    /**
     * 定义数据行为
     */
    public function behaviors()
    {
        return [
            // 可以直接调用数据输出提供器的方法
            'reportDataBehaviors' => [
                'class' => \datacenter\behaviors\ReportDataBehaviors::className(),
                'report' => $this,
            ],
        ];
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        $this->update_time = date('Y-m-d H:i:s');
        
        if($insert){
            $this->create_time = date('Y-m-d H:i:s');
            $this->create_user = Yii::$app->user->id;
        }
        
        if(!$this->show_type){
            $this->show_type = 'grid';
        }
        
        return parent::beforeSave($insert);
    }
    
    // 获取分类关系
    public function getCat(){
        return $this->hasOne(DcCat::className(), ['id'=>'cat_id']);
    }
    
    // 获取用户关系
    public function getUser(){
        return $this->hasOne(\webadmin\modules\authority\models\AuthUser::className(), ['id'=>'create_user']);
    }
    
    // 获取字段关系
    public function getColumns(){
        return $this->hasMany(DcReportColumns::className(), ['report_id' => 'id'])->addOrderBy("dc_report_columns.is_frozen desc,dc_report_columns.paixu desc,dc_report_columns.id asc");
    }
    
    // 获取用户报表关系
    public function getUserReport(){
        return $this->hasMany(DcUserReport::className(), ['report_id' => 'id'])->addOrderBy("dc_user_report.paixu desc,dc_user_report.id asc");
    }
    
    // 获取角色报表关系
    public function getRoleReport(){
        return $this->hasMany(DcRoleAuthority::className(), ['source_id' => 'id'])->onCondition("dc_role_authority.source_type=5");
    }
    
    // 获取数据集类型
    public function getV_state($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_report_status', ($val !== null ? $val : $this->state));
    }
    
    // 获取数据显示方式
    public function getV_show_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_show_type', ($val !== null ? $val : $this->show_type));
    }
    
    // 返回格式化标题
    public function getV_title()
    {
        return "{$this->title}"; // [{$this->user['name']}]
    }
    
    // 获取显示模板
    public function getV_show_type_tp()
    {
        $params = $this->getV_show_type();
        if(isset($params[$this->show_type])){
            return $this->show_type;
        }else{
            return 'grid';
        }
    }
    
    // 获取分类
    public function getV_cat_id($val = null)
    {
        if($val===false){
            return DcCat::authorityTreeOptions(Yii::$app->user->id);
        }else{
            if($val!== null){
                $model = DcCat::findOne($val);
                return ($model ? $model['name'] : '');
            }else{
                return ($this->cat ? $this->cat['name'] : '');
            }
        }
    }
    
    // 数据集合显示
    public function getV_sets_str()
    {
        $names = \yii\helpers\ArrayHelper::map($this->v_userSets, 'id', 'v_name');
        $names += \yii\helpers\ArrayHelper::map($this->v_sets, 'id', 'v_title');
        return implode(",",$names);
    }
    
    // 返回数据集合
    public function getV_sets()
    {
        if($this->_setsList === null){
            $this->_setsList = $this->_userSetsList = [];
            foreach($this->columns as $col){
                if($col['set_id'] && $col['sets']){
                    $col['sets']['report'] = $this;
                    if($col['user_set_id'] && $col['userSets']){
                        $col['userSets']['report'] = $this;
                        $set = clone $col['sets'];
                        $set['forUserModel'] = $col['userSets'];
                        $this->_userSetsList[$col['user_set_id']] = $col['userSets'];
                        $this->_setsList['-'.$col['user_set_id']] = $set;
                    }elseif($col['sets']){
                        $this->_setsList[$col['set_id']] = $col['sets'];
                    }
                }
            }
        }
        
        return $this->_setsList;
    }
    
    // 返回用户数据集合
    public function getV_userSets()
    {
        if($this->_userSetsList === null){
            $this->getV_sets();
        }
        
        return $this->_userSetsList;
    }
    
    // 返回主数据集
    public function getV_mainSet()
    {
        if($this->_mainSet === null){
            $sets = $this->v_sets;
            if($sets){
                $setIds = \yii\helpers\ArrayHelper::map($sets, 'id', 'id');
                foreach($sets as $set){
                    if($set['sourceRelation']){
                        foreach($set['sourceRelation'] as $rel){
                            if(in_array($rel['rel_type'],['multiple','group']) && in_array($rel['target_sets'], $setIds)){
                                $this->_mainSet = $set;
                                break 2;
                            }
                        }
                    }
                }
            }
            
            // 没有多记录关系的取第一个
            if(!$this->_mainSet && $sets){
                $this->_mainSet = reset($sets);
            }
            
            if(!$this->_mainSet){
                throw new \yii\web\HttpException(200, Yii::t('datacenter','缺少主数据集关系')."{$this['title']}({$this['id']})");
            }
        }
        
        return $this->_mainSet;
    }
    
    // 返回API请求地址
    public function getV_apiurl($cache='1')
    {
        $params = Yii::$app->request->post("SysConfig",Yii::$app->request->get("SysConfig",[]));
        $arr = [
            'report-api/data',
            'cache'=>$cache,
            'id'=>$this['id'],
            'vid'=>($this['forUserModel']?$this['forUserModel']['id']:''),
        ];
        $params && ($arr['SysConfig'] = $params);
        return \yii\helpers\Url::to($arr);
    }
    
    // 预处理数据
    public function findModel($condition, $muli = false)
    {
        $query = parent::findByCondition($condition)->with([
            'columns.sets.sourceRelation',
            'columns.sets.columns.column',
            'columns.userSets',
            'columns.sets.columns.sets.columns.forSets',
            'columns.sets.columns.forSets',
            'columns.sets.columns.setColumn',
            'columns.sets.sourceRelation',
            'columns.sets.columns.model.sourceRelation.sourceModel',
            'columns.sets.columns.model.sourceRelation.targetModel',
            'columns.sets.sourceRelation.groupLabel.model',
            'columns.sets.columns.model.columns.model',
            'columns.sets.columns.model.source',
            'columns.sets.mainModel.source',
            'columns.sets.mainModel.sourceRelation.sourceModel',
            'columns.sets.mainModel.sourceRelation.targetModel',
            'columns.setsCol.column',
            'columns.setsCol.model',
            'columns.setsCol.sets.mainModel',
            'columns.setsCol.sets.columns.model',
            'columns.sets.source',
            'columns.sets.columns.sets.columns.model',
            'columns.sets.columns.sets.mainModel',            
        ]);
        
        return ($muli ? $query->all() : $query->one());
    }
    
    // 删除判断
    public function delete()
    {
        if($this->columns){
            foreach($this->columns as $item){
                $item->delete();
            }
        }
        
        if($this->userReport){
            foreach($this->userReport as $item){
                $item->delete();
            }
        }
        
        if($this->roleReport){
            foreach($this->roleReport as $item){
                $item->delete();
            }
        }
        
        return parent::delete();
    }
    
    // 获取用户包含权限的默认报表
    public function allDefReport($userId='0',$where=[],$group=false)
    {
        $query = self::find();
        $query->where($userId=='1' ? [] : [
            'or',
            ['in', 'dc_report.id', \datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [$userId,'5'])],
            ['=', 'dc_report.create_user', $userId],
        ])->andWhere(['dc_report.state'=>'0']);
        if($where){
            $query->andWhere($where);
        }
        
        $query->orderBy("dc_report.paixu desc,dc_report.id")->with(['cat']);
        $query->limit = 1000;
        $list = $query->all();
        if($group){
            $list = \yii\helpers\ArrayHelper::map($list, 'id', 'v_self', 'cat_id');
        }
        
        return $list;
    }
    
    // 获取用户保存的条件报表
    public function allUserReport($userId='0',$where=[],$group=false)
    {
        $query = DcUserReport::find()->joinWith(['report']);
        $query->where([
            'dc_report.state'=>'0',
            'dc_user_report.user_id'=>$userId,
        ]);
        if($where){
            $query->andWhere($where);
        }
        
        $query->orderBy("dc_user_report.paixu desc,dc_user_report.id desc")->with(['report.cat']);
        $query->limit = 1000;
        $list = $query->all();
        if($group){
            $list = \yii\helpers\ArrayHelper::map($list, 'id', 'v_self', 'report.cat_id');
        }
        
        return $list;
    }
    
    /**
     * 初始化数据集关联
     */
    public function initJoinSet()
    {
        if($this->_joinSetState === null){
            $mainSet = $this->getV_mainSet();
            $setLists = $this->v_sets;
            $this->_joinSetState = true;
            $mainSet && $mainSet->joinSets($setLists);
            if($setLists){
                $set = reset($setLists);
                throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的数据集')."{$set['title']}({$set['id']})");
            }
        }
        
        return $this->_joinSetState;
    }
    
    /**
     * 复制报表
     */
    public function copyReport()
    {
        $model = new DcReport();
        $attributes = $this->attributes;
        unset($attributes['id']);
        if($model->load($attributes, '') && $model->save()){
            if($this->columns){
                // 复制字段
                foreach($this->columns as $col){
                    $attributes = $col->attributes;
                    unset($attributes['id']);
                    $attributes['report_id'] = $model['id'];
                    $colModel = new DcReportColumns();
                    $colModel->load($attributes,'');
                    $colModel->save(false);
                }
            }
            return $model;
        }else{
            return false;
        }
    }
    
    /**
     * 返回报表数据提供器
     */
    public function prepareDataProvider()
    {
        $mainSet = $this->getV_mainSet();
        $class = DcSets::dataProviderMap[$mainSet->set_type]!==null ? DcSets::dataProviderMap[$mainSet->set_type] : null;
        if(!$class){
            throw new \yii\web\HttpException(200, Yii::t('datacenter','未知的数据集类型'));
        }
        
        return Yii::createObject([
            'class' => $class,
            'sets' => $mainSet,
            'report' => $this,
        ]);
    }
}
