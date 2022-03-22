<?php
/**
 * 网格列表
 */
namespace datacenter\widgets;

use Yii;

class Grid extends Widget
{    
    /**
     * 加载资源
     */
    public function renderAsset()
    {
        $files = [
            'css/datatables.bootstrap.css',
            'js/datatable/buttons/css/buttons.dataTables.min.css',
            'js/datatable/buttons/css/buttons.bootstrap.min.css',
            'js/datatable/jquery.dataTables.js',
            'js/datatable/datatables.bootstrap.min.js',
            'js/datatable/buttons/js/dataTables.buttons.min.js',
            'js/datatable/buttons/js/buttons.bootstrap.min.js',
            'js/datatable/buttons/js/buttons.colVis.min.js', // 显示隐藏字段
            'js/datatable/colReorder/js/dataTables.colReorder.js', // 拖动列
        ];
        $view = $this->getView();
        foreach($files as $file){
            if(substr($file,-2)=='js'){
                $view->registerJsFile("@assetUrl/{$file}",['depends' => \webadmin\WebAdminAsset::className()]);
            }else{
                $view->registerCssFile("@assetUrl/{$file}",['depends' => \webadmin\WebAdminAsset::className()]);
            }
        }        
        
        if($this->reportModel && $this->reportModel['v_frozen']>0){ // 冻结列资源
            $view->registerJsFile('@assetUrl/js/datatable/fixedColumns/js/dataTables.fixedColumns.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
            $view->registerCssFile('@assetUrl/js/datatable/fixedColumns/css/fixedColumns.bootstrap.min.css',['depends' => \webadmin\WebAdminAsset::className()]);
        }
    }
    
    /**
     * 显示grid
     */
    public function renderContent()
    {
        return $this->render('grid',[
            'model' => $this->reportModel,
            'apiUrl' => $this->apiUrl,
            'id' => $this->getId(),
            'cache' => $this->isCache,
        ]);
    }
}