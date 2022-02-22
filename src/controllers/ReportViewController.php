<?php
/**
 * 报表显示视图
 */
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcReport;
use datacenter\models\DcSets;

class ReportViewController extends \webadmin\BController
{
    // 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据报表');
        Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
        
        return parent::beforeAction($action);
    }
    
    /**
     * 报表显示
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'list' => $this->findModel($id ? explode(',',$id) : []),
        ]);
    }
    
    /**
     * 数据集显示
     */
    public function actionSetView($id)
    {
        return $this->render('view', [
            'list' => $this->findSetModel($id ? explode(',',$id) : []),
        ]);
    }
    
    /**
     * 查找模型
     */
    protected function findModel($id)
    {
        if(is_array($id)){
            if(($list = DcReport::model()->getCache('findModel',[['id'=>$id], true]))){
                return $list;
            }
        }elseif(($model = DcReport::model()->getCache('findModel',[$id])) !== null) {
            return $model;
        }
        
        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
    
    /**
     * 查找数据集模型
     */
    protected function findSetModel($id)
    {
        if(is_array($id)){
            if(($list = DcSets::model()->getCache('findModel',[['id'=>$id], true]))){
                return $list;
            }
        }elseif(($model = DcSets::model()->getCache('findModel',[$id])) !== null) {
            return $model;
        }
        
        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
