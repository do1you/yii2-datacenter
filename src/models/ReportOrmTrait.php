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
     * 关联所有的字段
     */
    private $_setColumns;
    
    /**
     * 冻结列数
     */
    private $_frozen_num;
    
    /**
     * 数据源信息
     */
    private $_cache_source;
    
    /**
     * 数据源选择
     */
    public $set_source;
    
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
     * 返回格式化所有列字段
     */
    public function getV_columns()
    {
        if($this->_setColumns===null){
            if($this instanceof DcReport){ // 报表
                $this->initJoinSet();
                $setLists = $this->getV_sets();
            }
            $skipIds = $list = [];
            $setColumns = \yii\helpers\ArrayHelper::map($this->columns, 'id', 'v_self', 'set_id');
            foreach($this->columns as $col){
                $set = (($this instanceof DcReport) && isset($setLists[$col['set_id']])) ? $setLists[$col['set_id']] : null;
                $relation = $set ? $set['v_relation'] : null;
                if(!in_array($col['id'], $skipIds) && $set && $relation && $relation['rel_type']=='group'){
                    $groupCols = $relation->getV_group_list($set);
                    if($groupCols && is_array($groupCols)){
                        foreach($groupCols as $k=>$v){
                            // 同一数据集的其他字段一并拉取
                            if(isset($setColumns[$col['set_id']]) && is_array($setColumns[$col['set_id']])){
                                foreach($setColumns[$col['set_id']] as $c){
                                    $list[] = [
                                        'id' => $c['id'],
                                        'name' => $c['v_alias'].'_'.$k,
                                        'label' => "[{$v}]{$c['v_label']}",
                                        'order' => false,
                                        ];
                                    $skipIds[] = $c['id'];
                                }
                            }
                        }
                    }
                }elseif(!in_array($col['id'], $skipIds)){
                    $list[] = [
                        'id' => $col['id'],
                        'name' => $col['v_alias'],
                        'label' => $col['v_label'],
                        'order' => $col['v_order'],
                    ];
                }
            }
            $this->_setColumns = $list;
        }
        
        return $this->_setColumns;
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
