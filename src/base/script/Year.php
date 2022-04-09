<?php
/**
 * 年份
 */
namespace datacenter\base\script;

use Yii;

class Year extends \yii\base\Component implements ReportScriptInterface
{
    /**
     * 返回数据源
     */
    public function getModels()
    {
        $years = [];
        for($i=date('Y');$i>=2016;$i--){
            $years[] = ['year'=>$i];
        }
        return $years;
    }
}