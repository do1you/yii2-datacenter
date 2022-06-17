<?php
/**
 * 数据报表ORM集合动作
 */

namespace datacenter\models;

use Yii;
use datacenter\models\DcSets;
use datacenter\models\DcReport;

trait ReportOrmTrait
{
    /**
     * 所有的三级字段
     */
    private $_setThreeColumns;
    
    /**
     * 所有的二级字段
     */
    private $_setTwoColumns;
    
    /**
     * 所有的一级字段
     */
    private $_setOneColumns;
    
    /**
     * 冻结列数
     */
    private $_frozen_num;
    
    /**
     * 数据源信息
     */
    private $_cache_source;
    
    /**
     * 根据数据源设置缓存主键
     */
    private $_cache_key;
    
    /**
     * 数据源选择
     */
    public $set_source;
    
    /**
     * 用户数据关系
     */
    public $forUserModel;
    
    /**
     * 返回报表显示标题
     */
    public function getV_report_title()
    {
        if($this->forUserModel){
            $title = $this->forUserModel['v_name']."【".$this->title."】";
        }else{
            $title = $this->title;
        }
        return $title;
    }
    
    /**
     * 返回标题显示内容
     */
    public function getV_report_body()
    {
        if($this instanceof DcReport){ // 报表
            $body = $this->getV_sets_str();
        }else{
            $body = $this->getV_report_title();
        }
        return $body;
    }
    
    /**
     * 返回缓存主键值
     */
    public function getV_cache_key()
    {
        if($this->_cache_key === null){
            $sources = $this->getV_source();
            $keys = [];
            foreach($sources as $source){
                if($source['is_dynamic']=='1'){
                    $keys[$source['id']] = Yii::$app->session[$source['v_sessionName']];
                }
            }
            $this->_cache_key = md5(serialize($keys));
        }
        
        return $this->_cache_key;
    }
    
    /**
     * 返回包含的数据源
     */
    public function getV_source()
    {
        if($this->_cache_source === null){
            if($this instanceof DcReport){ // 报表
                $this->initJoinSet();
                $setLists = $this->getV_sets();
            }else{
                $setLists = [$this];
            }
            
            $this->_cache_source = [];
            foreach($setLists as $set){
                if($set['columns'] && is_array($set['columns'])){
                    foreach($set['columns'] as $col){
                        if($col['model'] && $col['model']['source']){
                            $this->_cache_source[$col['model']['source']['id']] = $col['model']['source'];
                        }
                    }
                }
            }
        }
        
        return $this->_cache_source;
    }
    
    /**
     * 返回一级字段
     */
    public function getV_oneCols()
    {
        $this->getV_columns();
        return $this->_setOneColumns;
    }
    
    /**
     * 返回二级字段
     */
    public function getV_twoCols()
    {
        $this->getV_columns();
        return $this->_setTwoColumns;
    }
    
    /**
     * 返回格式化所有三级字段
     */
    public function getV_columns()
    {
        if($this->_setThreeColumns===null){
            if($this instanceof DcReport){ // 报表
                $this->initJoinSet();
                $setLists = $this->getV_sets();
            }
            $this->_setOneColumns = $this->_setTwoColumns = $skipIds = $list = $labelList = [];
            $setColumns = \yii\helpers\ArrayHelper::map($this->columns, 'id', 'v_self', 'set_id');
            foreach($this->columns as $col){
                if(in_array($col['id'], $skipIds)) continue;
                if(($this instanceof DcReport)){
                    if($col['user_set_id'] && $col['userSets']){
                        $set = isset($setLists['-'.$col['user_set_id']]) ? $setLists['-'.$col['user_set_id']] : null;
                    }else{
                        $set = isset($setLists[$col['set_id']]) ? $setLists[$col['set_id']] : null;
                    }
                }else{
                    $set = null;
                }
                $relation = $set ? $set['v_relation'] : null;
                if($set && $relation && $relation['rel_type']=='union'){ // 合并
                    if(!isset($labelList[$col['v_label']])){
                        $data = [
                            'id' => $col['id'],
                            'name' => (string)$col['v_alias'],
                            'label' => (string)$col['v_label'],
                            'order' => $col['v_order'],
                        ];
                        $list[] = $data;
                        $labelList[$col['v_label']] = $data;
                    }
                }elseif($set && $relation && $relation['rel_type']=='group'){ // 分组
                    $groupCols = $relation->getV_group_list($set);
                    if($groupCols && is_array($groupCols)){
                        $colspan = 0;
                        foreach($groupCols as $k=>$v){
                            // 同一数据集的其他字段一并拉取
                            if(isset($setColumns[$col['set_id']]) && is_array($setColumns[$col['set_id']])){
                                $count = count($setColumns[$col['set_id']]);
                                $colspan2 = 0;
                                foreach($setColumns[$col['set_id']] as $c){
                                    $data = [
                                        'id' => $c['id'],
                                        'name' => $c['v_alias'].'_'.$k,
                                        'label' => (string)($count>1 ? $c['v_label'] : $v),
                                        'order' => false,
                                    ];
                                    $list[] = $data;
                                    $labelList[$col['v_label']] = $data;
                                    $skipIds[] = $c['id'];
                                    $colspan++;
                                    if($count>1) $colspan2++;
                                    if($colspan==1) $beginCol = $c['v_alias'].'_'.$k;
                                    if($colspan2==1) $beginCol2 = $c['v_alias'].'_'.$k;
                                }
                                if($count>1 && $colspan2>=1){
                                    $this->_setTwoColumns[$beginCol2] = [
                                        'begin' => $beginCol2,
                                        'colspan' => $colspan2,
                                        'label' => $v,
                                    ];
                                }
                            }
                        }
                        if($colspan>=1){
                            $this->_setOneColumns[$beginCol] = [
                                'begin' => $beginCol,
                                'colspan' => $colspan,
                                'label' => $set['title'],
                            ];
                        }
                    }
                }else{
                    $data = [
                        'id' => $col['id'],
                        'name' => (string)$col['v_alias'],
                        'label' => (string)$col['v_label'],
                        'order' => $col['v_order'],
                    ];
                    $list[] = $data;
                    $labelList[$col['v_label']] = $data;
                }
            }
            $this->_setThreeColumns = $list;
        }
        
        return $this->_setThreeColumns;
    }
    
    /**
     * 返回冻结的列数
     */
    public function getV_frozen()
    {
        if($this->_frozen_num === null){
            $num = 0;
            foreach($this->columns as $colItem){
                if($colItem['is_frozen']) $num++;
            }
            $this->_frozen_num = $num;
        }
        return $this->_frozen_num;
    }
    
    /**
     * 返回构建EXCEL数据模型
     */
    public function getV_excelData()
    {
        $columns = $this->getV_columns();
        if($columns && is_array($columns)){
            foreach($columns as $key=>$item){
                $columns[$key] = [
                    'attribute' => $item['label'],
                    'value' => $item['name'],
                ];
            }
        }
        
        return $columns;
    }
    
    /**
     * 返回树型的数据结构
     */
    public static function treeData($selectCatIds=[],$selectIds=[],$catList=null,&$list=null,$level=1)
    {
        $catList = $catList === null ? \datacenter\models\DcCat::treeData("0",[],$selectCatIds) : $catList;
        $list = $list === null ? self::find()->where(['state'=>'0'])->all() : $list;
        
        if($catList && is_array($catList)){
            foreach($catList as $key=>$cat){
                if(!empty($cat['children'])){
                    $catList[$key]['children'] = self::treeData($selectCatIds,$selectIds,$cat['children'],$list,($level+1));
                }
                
                self::_treeAppend($list,$catList,$key,$cat,$selectIds);
            }
            
            if($level==1 && $list){
                self::_treeAppend($list,$catList,null,null,$selectIds);
            }
        }
        
        return $catList;
    }
    
    /**
     * 报表/数据集数据并入树
     */
    private static function _treeAppend(&$list,&$catList,$key,$cat,$selectIds)
    {
        foreach($list as $k=>$item){
            if($cat===null || $item['cat_id']==$cat['id']){
                if($cat && !isset($catList[$key]['children'])) $catList[$key]['children'] = [];
                
                $_ = [
                    'id' => -$item['id'],
                    'name' => $item['title'],
                    'type' => 'item',
                ];
                if($selectIds && is_array($selectIds) && in_array($item['id'],$selectIds)){
                    $_['selected'] = true;
                }
                
                if($cat){
                    $catList[$key]['children'][] = $_;
                }else{
                    $catList[] = $_;
                }
                
                unset($list[$k]);
            }
        }
    }
    
    
    
    
    
    
    
    
    
    
    
}
