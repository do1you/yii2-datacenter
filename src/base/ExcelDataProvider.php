<?php
/**
 * Excel文档数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class ExcelDataProvider extends \yii\data\ArrayDataProvider implements ReportDataInterface
{
    use \datacenter\base\ReportDataProviderTrait;
    
    /**
     * 数据集
     */
    public $sets;
    
    /**
     * 数据报表
     */
    public $report;
    
    /**
     * 初始化
     */
    public function init()
    {
        parent::init();
        
        throw new \yii\web\HttpException(200, Yii::t('datacenter','Excel数据提供功能完善中，敬请期待'));
    }
    
    /**
     * 添加查询字段
     */
    public function select($columns)
    {
        
        return $this;
    }
    
    /**
     * 添加查询条件
     */
    public function where($columns, $values, $op = false)
    {
        
        return $this;
    }
    
    /**
     * 添加过滤值查询条件
     */
    public function filterWhere($columns, $values, $op = false)
    {
        
        return $this;
    }
    
    /**
     * 添加分组条件
     */
    public function having($columns, $values, $op = false)
    {
        
        return $this;
    }
    
    /**
     * 添加过滤值分组查询条件
     */
    public function filterHaving($columns, $values, $op = false)
    {
        
        return $this;
    }
    
    /**
     * 添加分组
     */
    public function group($columns)
    {
        return $this;
    }
    
    /**
     * 添加排序
     */
    public function order($columns)
    {
        return $this;
    }
    
}

