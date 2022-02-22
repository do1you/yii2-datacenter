<?php
/**
 * 继承系统默认的小部件，用于报表部件的全局
 */
namespace datacenter\widgets;

use Yii;

class Widget extends \yii\base\Widget
{
    /**
     * 数据报表模型
     */
    public $reportModel;
    
    /**
     * 布局视图
     */
    public $layout = 'layout';
    
    /**
     * 是否启用缓存数据
     */
    public $isCache = true;
    
    /**
     * 数据接口地址
     */
    public $apiUrl = '';
    
    /**
     * 初始化
     */
    public function init()
    {
        parent::init();
        
        $this->apiUrl = $this->isCache ? $this->reportModel['v_apiurl'] : $this->reportModel->getV_apiurl('');
    }
    
    /**
     * 触发报表部件
     */
    public function run()
    {
        $this->renderAsset();
        $content = $this->renderContent();
        return $this->renderLayout($content);
    }
    
    /**
     * 全局容器
     */
    public function renderLayout($content)
    {
        if($this->layout){
            return $this->render($this->layout,[
                'model' => $this->reportModel,
                'content' => $content,
                'cache' => $this->isCache,
            ]);
        }else{
            return $content;
        }
    }
    
    /**
     * 加载资源
     */
    public function renderAsset()
    {
    }
    
    /**
     * 报表内容
     */
    public function renderContent()
    {
    }
}