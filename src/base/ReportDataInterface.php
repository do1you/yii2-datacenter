<?php

namespace datacenter\base;

/**
 * 数据提供器扩展接口
 * @author 统一
 *
 */
interface ReportDataInterface
{
    /**
     * 添加查询字段
     */
    public function select($columns);
    
    /**
     * 添加查询条件
     */
    public function where($columns, $values, $op = false);
    
    /**
     * 添加过滤值查询条件
     */
    public function filterWhere($columns, $values, $op = false);
    
    /**
     * 添加分组条件
     */
    public function having($columns, $values, $op = false);
    
    /**
     * 添加过滤值分组查询条件
     */
    public function filterHaving($columns, $values, $op = false);
    
    
    /**
     * 添加分组
     */
    public function group($columns);
    
    /**
     * 添加排序
     */
    public function order($columns);
    
    /**
     * 汇总数据
     */
    public function getSummary();
}