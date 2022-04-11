<?php
/**
 * Excel文档数据提供器扩展
 */
namespace datacenter\base;

use Yii;

class ExcelDataProvider extends ScriptDataProvider implements ReportDataInterface
{
    
    /**
     * 初始化
     */
    public function init()
    {
        if(!($sets = $this->sets)){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '数据集尚未配置正确的数据集.'));
        }
        
        if(!$this->sets->excel_file || !file_exists($this->sets->excel_file)){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '数据集的EXCEL文档不存在.'));
        }
        
        // 默认数据
        $allModel = \webadmin\ext\PhpExcel::readfile($this->sets->excel_file);
        $allModel && array_shift($allModel);
        Yii::configure($this,[
            'allModels' => $allModel,
            'pagination' => ['pageSizeLimit' => [1, 500]],
        ]);
        
        // 增加基础排序
        $sort = $this->getSort();
        $columns = $this->report ? $this->report->columns : $sets->columns;
        if($columns && is_array($columns)){
            foreach($columns as $item){
                $col = $this->report ? ($item->col_id>0 ? $item->setsCol : null) : $item;
                if($col){
                    // 添加排序
                    $sort->attributes[$item->v_alias] = [
                        'asc' => [$col['name'] => SORT_ASC],
                        'desc' => [$col['name'] => SORT_DESC],
                        'label' => $item->v_label,
                    ];
                }
            }
        }
        
        // 应用过滤条件
        $this->applySearchModels(false);
    }
    
    
}

