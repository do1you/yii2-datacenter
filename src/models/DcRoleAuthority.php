<?php
/**
 * 数据库表 "dc_role_authority" 的模型对象.
 * @property int $id 流水号
 * @property int $role_id 角色ID
 * @property int $source_id 源ID
 * @property int $source_type 源类型
 */

namespace datacenter\models;

use Yii;

class DcRoleAuthority extends \webadmin\ModelCAR
{
    /**
     * 自定义的权限保存参数
     */
    public $sourceList,$catList,$reportList,$setsList,$dynamicSourceList;
    
    /**
     * 自定义参数属性
     */
    public $parameterAuthority = [
        '1' => 'catList',
        '2' => 'sourceList',
        '3' => 'dynamicSourceList',
        '4' => 'setsList',
        '5' => 'reportList',
    ];
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_role_authority';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['role_id', 'source_type'], 'integer'],
            [['source_id'], 'integer', 'when' => function ($model) {
                return ($model->source_type!='3');
            }],
            [['sourceList', 'catList', 'reportList', 'setsList', 'dynamicSourceList'], 'safe'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'role_id' => Yii::t('datacenter', '角色ID'),
            'source_id' => Yii::t('datacenter', '源ID'),
            'source_type' => Yii::t('datacenter', '源类型'),
            'sourceList' => Yii::t('datacenter', '数据源权限'),
            'catList' => Yii::t('datacenter', '分类权限'),
            'reportList' => Yii::t('datacenter', '报表权限'),
            'setsList' => Yii::t('datacenter', '数据集权限'),
            'dynamicSourceList' => Yii::t('datacenter', '动态数据源'),
        ];
    }
    
    // 获取角色关系
    public function getRole()
    {
        return $this->hasOne(\webadmin\modules\authority\models\AuthRole::className(), ['id'=>'role_id']);
    }
    
    // 获取分类关系
    public function getCat()
    {
        return $this->hasOne(DcCat::className(), ['id'=>'source_id'])->andWhere("source_type=1");
    }
    
    // 获取数据源关系
    public function getSource()
    {
        return $this->hasOne(DcSource::className(), ['id'=>'source_id'])->andWhere("source_type=2");
    }
    
    // 获取动态数据源关系
    public function getDynamicSource()
    {
        // source_type=3
    }
    
    // 获取数据集关系
    public function getSets()
    {
        return $this->hasOne(DcSets::className(), ['id'=>'source_id'])->andWhere("source_type=4");
    }
    
    // 获取数据报表关系
    public function getReport()
    {
        return $this->hasOne(DcReport::className(), ['id'=>'source_id'])->andWhere("source_type=5");
    }
    
    // 获取数据权限源类型
    public function getV_source_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_authority_type', ($val !== null ? $val : $this->source_type));
    }
    
    // 返回包含的权限数组
    public function getAuthorityIds($userId,$type)
    {
        $roleIds = \yii\helpers\ArrayHelper::map(\webadmin\modules\authority\models\AuthUserRole::findAll(['user_id'=>$userId]), 'role_id', 'role_id');
        $ids = self::find()->where([
            'role_id'=>$roleIds,
            'source_type'=>$type,
        ])->select('source_id')->asArray()->column();
        $ids = $ids ? $ids : [];
        return $ids;
    }
    
    // 保存角色权限
    public function saveAuthority($roleId)
    {
        $list = self::find()->where(['role_id'=>$roleId])->all();
        $list = \yii\helpers\ArrayHelper::map($list, 'source_id', 'v_self', 'source_type');
        
        // 保存动态数据源
        $this->_saveAuthority('3', $roleId, $list);
        
        // 保存数据源
        $this->_saveAuthority('2', $roleId, $list);
        
        // 保存数据集
        $this->_saveAuthority('4', $roleId, $list);
        
        // 保存数据报表
        $this->_saveAuthority('5', $roleId, $list);
        
        // 保存数据分类
        $this->_saveAuthority('1', $roleId, $list);
        
        return true;
    }
    
    // 保存权限
    private function _saveAuthority($type,$roleId,$list)
    {
        $param = $this->parameterAuthority[$type];

        if($param && $this->$param){
            if($this->$param && is_array($this->$param)){
                foreach($this->$param as $dbId=>$dynamicIds){
                    $dynamicIds = $type=='3' ? $dynamicIds : ($dynamicIds ? [$dynamicIds] : []);
                    if($dynamicIds && is_array($dynamicIds)){
                        if($type=='3') $this->sourceList[] = $dbId;
                        foreach($dynamicIds as $subDbId){
                            if($subDbId){
                                if($type=='4' || $type=='5'){
                                    if($subDbId>0){
                                        if(!is_array($this->catList)) $this->catList = [];
                                        $this->catList[$subDbId] = $subDbId;
                                        continue;
                                    }else{
                                        $subDbId = abs($subDbId);
                                    }
                                    
                                }elseif($type=='3'){
                                    $subDbId = "{$dbId}_{$subDbId}";
                                }
                                $model = isset($list[$type][$subDbId]) ? $list[$type][$subDbId] : (new DcRoleAuthority());
                                $model->load([
                                    'role_id' => $roleId,
                                    'source_id' => $subDbId,
                                    'source_type' => $type,
                                ],'');
                                $model->save(false);
                                if(isset($list[$type][$subDbId])){
                                    unset($list[$type][$subDbId]);
                                }
                            }
                        }
                    }
                }
            }
            
            if(!empty($list[$type]) && is_array($list[$type])){
                foreach($list[$type] as $item){
                    $item->delete();
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    
    
    
}
