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

class DcReport extends \webadmin\ModelCAR implements \yii\data\DataProviderInterface
{
    use ReportDataTrait;
    use ReportOrmTrait;
    
    /**
     * 关联的所有数据集
     */
    private $_setsList;
    
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
        return $this->hasMany(DcReportColumns::className(), ['report_id' => 'id'])->addOrderBy("is_frozen desc,paixu desc,id asc");
    }
    
    // 获取用户报表关系
    public function getUserReport(){
        return $this->hasMany(DcUserReport::className(), ['report_id' => 'id'])->addOrderBy("paixu desc,id asc");
    }
    
    // 获取角色报表关系
    public function getRoleReport(){
        return $this->hasMany(DcRoleAuthority::className(), ['source_id' => 'id'])->onCondition("source_type=5");
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
        return "{$this->title}[{$this->user['name']}]";
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
    
    // 数据集合显示
    public function getV_sets_str()
    {
        $setList = $this->v_sets;
        $names = \yii\helpers\ArrayHelper::map($setList, 'id', 'title');
        return implode(",",$names);
    }
    
    // 返回数据集合
    public function getV_sets()
    {
        if($this->_setsList === null){
            $list = \yii\helpers\ArrayHelper::map($this->columns, 'set_id', 'sets');
            if($list){
                foreach($list as $k=>$model){
                    if(!$model) unset($list[$k]);
                }
            }
            $this->_setsList = $list;
        }
        
        return $this->_setsList;
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
    
    // 预处理数据
    public function findModel($condition, $muli = false)
    {
        $query = parent::findByCondition($condition)->with([
            'columns.sets.sourceRelation',
            'columns.sets.columns.model.sourceRelation.sourceModel',
            'columns.sets.columns.model.sourceRelation.targetModel',
            'columns.sets.sourceRelation.groupLabel.model',
            'columns.sets.columns.model.columns.model',
            'columns.sets.mainModel.source',
            'columns.sets.mainModel.sourceRelation.sourceModel',
            'columns.sets.mainModel.sourceRelation.targetModel',
            'columns.setsCol.column',
            'columns.setsCol.model',
        ]);
        
        return ($muli ? $query->all() : $query->one());
    }
    
    // 返回API请求地址
    public function getV_apiurl($cache='1')
    {
        $params = Yii::$app->request->get("SysConfig",[]);
        $arr = [
            'report-api/data',
            'cache'=>$cache,
            'id'=>$this['id'],
            'access-token'=>Yii::$app->user->identity['access_token'],
        ];
        $params && ($arr['SysConfig'] = $params);
        return \yii\helpers\Url::to($arr);
    }
    
    // 初始化数据集关联
    public function initJoinSet($isExit = true)
    {
        if($this->_joinSetState === null){
            // 关联数据集
            $this->orderColumns($this->getSort());
            $mainSet = $this->getV_mainSet();
            $allSets = $setLists = $this->v_sets;
            
            $mainSet && $mainSet->joinSets($setLists);
            if($setLists){
                $this->_joinSetState = false;
                if($isExit){
                    $set = reset($setLists);
                    throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的数据集')."{$set['title']}({$set['id']})");
                }
            }else{
                $this->_joinSetState = true;
            }
        }
        
        return $this->_joinSetState;
    }
    
    // 组装报表数据
    protected function prepareModels()
    {
        $this->initJoinSet();
        
        // 应用过滤条件
        $this->setSearchModels(false);
        
        // 分组字段优先提取
        $setLists = $this->getV_sets();
        foreach($setLists as $set){
            $relation = $set ? $set['v_relation'] : null;
            if($set && $relation && $relation['rel_type']=='group'){
                $relation->getV_group_list($set);
            }
        }
        
        // 提取数据集数据
        $mainSet = $this->getV_mainSet();
        $callModel = new DcSets();
        $data = $mainSet ? $mainSet->getModels() : [];
        foreach($data as $k=>$v){
            $data[$k] = call_user_func_array([$callModel, 'formatValue'], [$this->filterColumns($v), $this->columns]);
        }
        return $data;
    }
    
    // 查询出所有的字段信息
    private function filterColumns($values)
    {
        $data = [];
        $setLists = $this->v_sets;
        foreach($this->columns as $col){
            $set = isset($setLists[$col['set_id']]) ? $setLists[$col['set_id']] : null;
            $relation = $set ? $set['v_relation'] : null;
            if($set && $relation && $relation['rel_type']=='group'){
                $groupCols = $relation->getV_group_list($set);
                if($groupCols && is_array($groupCols)){
                    foreach($groupCols as $k=>$v){
                        $data[$col['v_alias'].'_'.$k] = isset($values['_'][$col['set_id']][$k][$col['setsCol']['v_alias']])
                        ? $values['_'][$col['set_id']][$k][$col['setsCol']['v_alias']]
                        : $col['v_default_value'];
                    }
                }
            }else{
                $data[$col['v_alias']] = isset($values[$col['setsCol']['v_alias']]) 
                    ? $values[$col['setsCol']['v_alias']] 
                    : (
                        isset($values['_'][$col['set_id']][$col['setsCol']['v_alias']]) 
                        ? $values['_'][$col['set_id']][$col['setsCol']['v_alias']] 
                        : $col['v_default_value']
                    );
            }
        }
        
        return $data;
    }
    
    // 返回数据集提供器
    protected function prepareDataProvider()
    {
        $mainSet = $this->getV_mainSet();
        return $mainSet->getDataProvider();
    }
}
