<?php
/**
 * 数据报表ORM集合动作
 */

namespace datacenter\models;

use Yii;

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
    
    // 返回格式化所有字段
    public function getV_columns()
    {
        if($this->_setColumns===null){
            $this->_setColumns = \yii\helpers\ArrayHelper::map($this->columns, 'v_alias', 'v_label');
        }
        
        return $this->_setColumns;
    }
    
    // 返回冻结列数
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
    
    // 增加排序索引
    public function orderColumns(\yii\data\Sort $sort)
    {
        foreach($this->columns as $col){
            $col->orderColumn($sort);
        }
        return $this;
    }
}
