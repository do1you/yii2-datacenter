<?php
/**
 * 数据提供器输出结果行为
 */
namespace datacenter\behaviors;

use Yii;
use yii\base\ModelEvent;


class ReportDataBehaviors extends \yii\base\Behavior
{
    /**
     * 数据集
     */
    public $sets;
    
    /**
     * 数据报表
     */
    public $report;
    
    /**
     * 数据提供器
     */
    private $_dataProvider;
    
    /**
     * 获取数据处理器
     */
    public function getDataProvider($params=null)
    {
        if($this->_dataProvider === null){
            if($this->report){
                $this->report->initJoinSet();
            }
            $this->_dataProvider = $this->report ? $this->report->prepareDataProvider() : $this->sets->prepareDataProvider();
            $this->_dataProvider->initSearchParams($params);
            $this->_dataProvider->initData();
        }else{
            $this->_dataProvider->initSearchParams($params);
        }
        
        return $this->_dataProvider;
    }
    
    /**
     * 设置数据处理器
     */
    public function setDataProvider($value)
    {
        $this->_dataProvider = $value;
    }
    
    /**
     * 初始化数据
     */
    public function initData()
    {
        return $this->getDataProvider()->initData();
    }
    
    /**
     * 获取汇总数据
     */
    public function getSummary()
    {
        return $this->getDataProvider()->getSummary();
    }
    
    /**
     * 设置汇总数据
     */
    public function setSummary($value)
    {
        return $this->getDataProvider()->setSummary($value);
    }
    
    /**
     * 获取数据
     */
    public function getModels()
    {
        return $this->getDataProvider()->getModels();
    }
    
    /**
     * 设置数据
     */
    public function setModels($models)
    {
        return $this->getDataProvider()->setModels($models);
    }
    
    /**
     * 获取主键
     */
    public function getKeys()
    {
        return $this->getDataProvider()->getKeys();
    }
    
    /**
     * 设置主键
     */
    public function setKeys($keys)
    {
        return $this->getDataProvider()->setKeys($keys);
    }
    
    /**
     * 设置记录数
     */
    public function getCount()
    {
        return $this->getDataProvider()->getCount();
    }
    
    /**
     * 获取总记录数
     */
    public function getTotalCount()
    {
        return $this->getDataProvider()->getTotalCount();
    }
    
    /**
     * 设置总记录数
     */
    public function setTotalCount($value)
    {
        return $this->getDataProvider()->setTotalCount($value);
    }
    
    /**
     * 获取分页
     */
    public function getPagination()
    {
        return $this->getDataProvider()->getPagination();
    }
    
    /**
     * 设置分页
     */
    public function setPagination($value)
    {
        return $this->getDataProvider()->setPagination($value);
    }
    
    /**
     * 获取排序
     */
    public function getSort()
    {
        return $this->getDataProvider()->getSort();
    }
    
    /**
     * 设置排序
     */
    public function setSort($value)
    {
        return $this->getDataProvider()->setSort($value);
    }
    
    /**
     * 刷新数据
     */
    public function refreshData()
    {
        return $this->getDataProvider()->refresh();
    }
    
    /**
     * 添加查询字段
     */
    public function select($columns)
    {
        return $this->getDataProvider()->select($columns);
    }
    
    /**
     * 添加查询条件
     */
    public function where($columns, $values, $op = false)
    {
        return $this->getDataProvider()->where($columns, $values, $op);
    }
    
    /**
     * 添加过滤值查询条件
     */
    public function filterWhere($columns, $values, $op = false)
    {
        return $this->getDataProvider()->filterWhere($columns, $values, $op);
    }
    
    /**
     * 添加分组条件
     */
    public function having($columns, $values, $op = false)
    {
        return $this->getDataProvider()->having($columns, $values, $op);
    }
    
    /**
     * 添加过滤值分组查询条件
     */
    public function filterHaving($columns, $values, $op = false)
    {
        return $this->getDataProvider()->filterHaving($columns, $values, $op);
    }
    
    /**
     * 添加分组
     */
    public function group($columns)
    {
        return $this->getDataProvider()->group($columns);
    }
    
    /**
     * 添加排序
     */
    public function order($columns)
    {
        return $this->getDataProvider()->order($columns);
    }
    
    /**
     * 获取字段
     */
    public function getColumns($columns, &$values = null)
    {
        return $this->getDataProvider()->getColumns($columns, $values);
    }
    
    /**
     * 返回数据查询条件的表单构建模型
     */
    public function getSearchModels()
    {
        return $this->getDataProvider()->getSearchModels();
    }
    
    /**
     * 返回数据查询条件的表单数值
     */
    public function getSearchValues()
    {
        return $this->getDataProvider()->getSearchValues();
    }
        
    /**
     * 应用过滤条件
     */
    public function applySearchModels($params = null)
    {
        return $this->getDataProvider($params)->applySearchModels($params);
    }
}


