<?php 
/**
 * 数据报表数据提供器集合动作
 */

namespace datacenter\models;

use Yii;
use yii\base\ModelEvent;

trait ReportDataTrait
{
    // 查询数据前事件名称
    public static $EVENT_BEFORE_MODEL = 'beforeModel';
    
    // 查询数据后事件名称
    public static $EVENT_AFTER_MODEL = 'afterModel';
    
    // 数据模型
    private $_models;
    
    // 数据处理器
    private $_dataProvider;
    
    // 获取数据处理器
    public function getDataProvider()
    {
        if($this->_dataProvider === null){
            $dataProvider = $this->prepareDataProvider();
            // $this->_dataProvider = clone $dataProvider;
            $this->_dataProvider = $dataProvider;
        }
        
        return $this->_dataProvider;
    }
    
    // 处理数据处理器
    protected function prepareDataProvider()
    {
        $dataProvider = new \yii\data\ArrayDataProvider();
        return $dataProvider;
    }
    
    // 处理数据前触发事件
    public function beforeModel()
    {
        $event = new ModelEvent();
        $this->trigger(self::$EVENT_BEFORE_MODEL, $event);
        
        return $event->isValid;
    }
    
    // 处理数据后触发事件
    public function afterModel()
    {
        $this->trigger(self::$EVENT_AFTER_MODEL, new ModelEvent());
    }
    
    // 处理数据
    public function prepare($forcePrepare = false)
    {
        if ($forcePrepare || $this->_models === null) {
            if (!$this->beforeModel()) {
                return false;
            }
            $this->_models = $this->prepareModels();
            $this->getDataProvider()->setModels($this->_models);
            $this->afterModel();
        }
        
        return $this->getDataProvider()->getModels();
    }
    
    // 组装报表数据
    protected function prepareModels()
    {
        return [];
    }
    
    // 返回数据模型
    public function getModels()
    {
        return $this->prepare();
    }
    
    // 设置数据模型
    public function setModels($models)
    {
        return $this->getDataProvider()->setModels($models);
    }
    
    // 返回主键值
    public function getKeys()
    {
        return $this->getDataProvider()->getKeys();
    }
    
    // 返回记录数
    public function getCount()
    {
        return $this->getDataProvider()->getCount();
    }
    
    // 返回记录总数
    public function getTotalCount()
    {
        return $this->getDataProvider()->getTotalCount();
    }
    
    // 返回分页处理器
    public function getPagination()
    {
        return $this->getDataProvider()->getPagination();
    }
    
    // 定义分页处理器
    public function setPagination($value)
    {
        return $this->getDataProvider()->setPagination($value);
    }
    
    // 返回排序处理器
    public function getSort()
    {
        return $this->getDataProvider()->getSort();
    }
    
    // 定义排序处理器
    public function setSort($value)
    {
        return $this->getDataProvider()->setSort($value);
    }
}
    