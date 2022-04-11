<?php
/**
 * 年份
 */
namespace datacenter\base\script;

use Yii;

class Year extends BaseScriptModel
{
    /**
     * 返回数据源
     */
    public function getModels($wheres=[])
    {
        $years = [];
        if($wheres){
            foreach($wheres as $arr){
                list($op,$col,$val) = $arr;
                if(in_array($op, ['=','==','in'])){
                    $val = is_array($val) ? $val : [$val];
                    foreach($val as $year){
                        $years[] = ['year'=>$year];
                    }
                    break;
                }
            }
        }
        
        if(empty($years)){
            for($i=date('Y');$i>=(date('Y')-50);$i--){
                $years[] = ['year'=>$i];
            }
        }
        
        return $years;
    }
}