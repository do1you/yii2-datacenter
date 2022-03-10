<?php
/**
 * 报表显示视图
 */
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcReport;
use datacenter\models\DcSets;
use datacenter\models\DcUserReport;

class ReportViewController extends \webadmin\BController
{
    // 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据报表');
        Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
        
        return parent::beforeAction($action);
    }
    
    // 设置数据源
    public function actionSetSource($id, $sid)
    {
        $source = $sid ? \datacenter\models\DcSource::findOne($sid) : null;
        if($source && $id){
            Yii::$app->session[$source['v_sessionName']] = $id;
        }
        
        if(!empty($_SERVER['HTTP_REFERER'])){
            $this->redirect($_SERVER['HTTP_REFERER']);
        }else{
            $this->redirect(['index']);
        }
    }
    
    /**
     * 报表首页
     */
    public function actionIndex()
    {
        $treeData = \datacenter\models\DcCat::treeData(); // 获取所有分类
        $reportList = \datacenter\models\DcUserReport::model()->getCache('allReport',[Yii::$app->user->id,null,1],7200);
        $myreportList = \datacenter\models\DcUserReport::model()->getCache('allReport',[Yii::$app->user->id,['is_collection'=>'1']],7200);
        $haveCatIds = [];
        foreach($reportList as $catId=>$reports){
            if(is_array($reports)){
                foreach($reports as $report){
                    if(($parentIds = $report['cat']['parentIds'])){
                        $haveCatIds = array_merge($haveCatIds,$parentIds);                        
                    }
                }
            }
        }
        
        return $this->render('index', [
            'treeData' => $treeData,
            'reportList' => $reportList,
            'haveCatIds' => $haveCatIds,
            'myreportList' => $myreportList,
        ]);
    }
    
    /**
     * 收藏报表
     */
    public function actionCollection($id,$show='')
    {
        $result = [];
        $model = DcUserReport::find()->where(['report_id'=>$id,'user_id'=>Yii::$app->user->id])->one();
        if(empty($show)){
            $report = DcReport::findOne($id);
            if(empty($model) && $report['create_user']==Yii::$app->user->id){
                $model = new DcUserReport;
                $model->loadDefaultValues();
            }
            
            if($model){
                $model->load([
                    'report_id'     =>  $id,
                    'user_id'       =>  Yii::$app->user->id,
                    'is_collection' =>  ($model['is_collection']=='0' ? '1' : '0'),
                ],'');
                $model->save(false);
                $result['success'] = true;
                
                // 清除缓存
                \datacenter\models\DcUserReport::model()->getCache('allReport',[Yii::$app->user->id,['is_collection'=>'1']],7200,true);                
            }else{
                $result['msg'] = "您没有权限收藏该报表";
            }
        }else{
            $result['success'] = true;
        }
        $result['state'] = $model['is_collection'];
        return $result;
    }
    
    /**
     * 报表显示
     */
    public function actionView($id)
    {
        return $this->render('display', [
            'list' => $this->findModel($id ? explode(',',$id) : []),
        ]);
    }
    
    /**
     * 数据集显示
     */
    public function actionSetView($id)
    {
        return $this->render('display', [
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
