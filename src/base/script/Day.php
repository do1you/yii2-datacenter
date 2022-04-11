<?php
/**
 * 日期　
 */
namespace datacenter\base\script;

use Yii;

class Day extends BaseScriptModel
{
    /**
     * 返回数据源
     */
    public function getModels($wheres=[])
    {
        $days = [];
        if($wheres){
            foreach($wheres as $arr){
                list($op,$col,$val) = $arr;
                if(in_array($op, ['=','==','in']) && $col=='year'){
                    $val = is_array($val) ? $val : [$val];
                    foreach($val as $year){
                        for($m=12;$m>=1;$m--){
                            $days = $this->_getDays($days,$year,$m);
                        }
                    }
                    break;
                }elseif($col=='year_month_day'){
                    if(in_array($op, ['<','<='])){
                        $end = $val;
                        $endOp = $op;
                    }elseif(in_array($op, ['>','>='])){
                        $begin = $val;
                        $beginOp = $op;
                    }
                }
            }
            
            if(!empty($begin) && !empty($end)){
                for($d=strtotime($begin); $d<=strtotime($end); $d = strtotime("+1 day", $d)){
                    $days[] = ['year'=>date('Y',$d),'month'=>date('m',$d),'year_month_day'=>date('Y-m-d',$d)];
                }
            }
        }
        
        if(empty($days)){
            for($i=date('Y');$i>=(date('Y')-10);$i--){
                $max = date('Y')==$i ? date('n') : 12;
                for($m=$max;$m>=1;$m--){
                    $days = $this->_getDays($days,$i,$m);
                }
            }
        }
        
        if(count($days<=31) && $this->dataProvider){
            $this->dataProvider->setPagination(false);
        }
        
        return $days;
    }
    
    /**
     * 根据月分返回日期天数
     */
    private function _getDays($days,$i,$m)
    {
        $maxD = date('Y')==$i&&date('n')==$m ? date('j') : date('t',strtotime("{$i}-{$m}-01"));
        for($d=$maxD;$d>=1;$d--){
            $days[] = ['year'=>$i,'month'=>$m,'year_month_day'=>$i.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT)];
        }
        return $days;
    }
}
