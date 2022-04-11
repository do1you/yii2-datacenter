<?php
/**
 * 月份
 */
namespace datacenter\base\script;

use Yii;

class Month extends BaseScriptModel
{
    /**
     * 返回数据源
     */
    public function getModels($wheres=[])
    {
        $months = [];
        if($wheres){
            foreach($wheres as $arr){
                list($op,$col,$val) = $arr;
                if(in_array($op, ['=','==','in']) && $col=='year'){
                    $val = is_array($val) ? $val : [$val];
                    foreach($val as $year){
                        for($m=12;$m>=1;$m--){
                            $months[] = ['year'=>$year,'month'=>$m,'year_month'=>$year.'-'.str_pad($m,2,'0',STR_PAD_LEFT)];
                        }
                    }
                    break;
                }
            }
        }
        
        if(empty($months)){
            for($i=date('Y');$i>=(date('Y')-30);$i--){
                $max = date('Y')==$i ? date('n') : 12;
                for($m=$max;$m>=1;$m--){
                    $months[] = ['year'=>$i,'month'=>$m,'year_month'=>$i.'-'.str_pad($m,2,'0',STR_PAD_LEFT)];
                }
            }
        }
        
        return $months;
    }
}
