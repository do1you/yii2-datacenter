<?php
/**
 * 脚本基础模型
 */
namespace datacenter\base\script;

use Yii;

class BaseScriptModel extends \yii\base\Component implements ReportScriptInterface
{
    /**
     * 数据提供器
     */
    public $dataProvider;
    
    /**
     * 返回数据源
     */
    public function getModels($wheres=[])
    {
        return [];
    }
}

