<?php
/**
 * 分享报表显示视图
 */
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcReport;
use datacenter\models\DcSets;
use datacenter\models\DcShare;

class ShareViewController extends \webadmin\BController
{
    public $layout = 'html5'; // 布局文件
    
    // 初始化
    public function init()
    {
        parent::init();
    }
    
    // 执行前
    public function beforeAction($action)
    {
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '分享报表');
        Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
                
        return parent::beforeAction($action);
    }
    
    // 过滤器
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        unset($behaviors['webAuthFilter']);
        
        return $behaviors;
    }

    /**
     * 报表显示
     */
    public function actionView($h)
    {
        $model = DcShare::model()->getCache('findModel',[['hash_key'=>$h], false]);
        
        if(empty($model)){
            throw new \yii\web\HttpException(200, Yii::t('datacenter','分享的数据报表不存在！'));
        }
        
        if($model->forUserModel){
            $model->forUserModel->setSource();
        }
        
        return $this->render('/report-view/view', [
            'list' => [$model],
        ]);
    }
}