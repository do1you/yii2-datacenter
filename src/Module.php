<?php

namespace datacenter;

use Yii;

/**
 * 数据中心模块定义类
 */
class Module extends \yii\base\Module
{
    /**
     * 默认的控制器方法命名空间
     */
    public $controllerNamespace = 'datacenter\controllers';
    
    /**
     * 模块入口初始化
     */
    public function init()
    {
        parent::init();
        
        // 控制台命令
        if(Yii::$app instanceof \yii\console\Application){
            $this->controllerNamespace = 'datacenter\console';
        }
    }
}
