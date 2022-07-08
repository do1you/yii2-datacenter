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
    /**
     * 布局文件
     */
    public $layout = 'html5';
    
    /**
     * 当前控制器中需要缓存查询条件的方法
     */
    public $searchCacheActions = ['view', 'data']; // , 'collection', 'set-collection'
    
    /**
     * 列表数据格式化定义输出
     * @var array
     */
    public $serializer = [
        'class' => '\webadmin\restful\Serializer',
        'collectionEnvelope' => 'rows',
        'linksEnvelope' => 'links',
        'metaEnvelope' => 'pages',
    ];
    
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
        
        // 缓存查询条件
        $behaviors['searchBehaviors'] = [
            'class' => \webadmin\behaviors\SearchBehaviors::className(),
            'searchCacheActions' => $this->searchCacheActions,
            'cacheKey' => Yii::$app->session->id.'/datacenter/report-api/'.
            ($this->action
                ? (in_array($this->action->id,['view','data']) ? str_replace(['view','data'],'data',$this->action->id)
                    .'/'.Yii::$app->request->get('h','') : $this->action->id)
                : 'index'),
        ];   
        
        return $behaviors;
    }
    
    /**
     * 输入密码界面
     */
    public function actionPassword($h)
    {
        $model = $this->findModel($h);
        return $this->render('password', [
            'model' => $model,
        ]);
    }

    /**
     * 报表显示
     */
    public function actionView($h)
    {
        $model = $this->findModel($h);
        
        return $this->render('/report-view/view', [
            'list' => [$model],
        ]);
    }
    
    /**
     * 报表数据
     */
    public function actionData($h)
    {
        $model = $this->findModel($h);
        
        if(Yii::$app->request->get('debug')){
            return $this->render('/report-view/api', [
                'dataProvider' => $model->getDataProvider(),
            ]);
        }
        
        return $model->getDataProvider();
    }
    
    /**
     * 获取数据
     */
    private function findModel($h)
    {
        $model = DcShare::model()->getCache('findModel',[['hash_key'=>$h], false]);
        
        // 判断分享是否存在
        if(empty($model) || !$model->forUserModel || $model->forUserModel['hash_key']!=$h){
            throw new \yii\web\HttpException(200, Yii::t('datacenter','分享的数据报表不存在！'));
        }
        
        // 定义分享时效
        if($model->forUserModel['invalid_time'] && $model->forUserModel['invalid_time']!='0000-00-00 00:00:00'
            && time()>strtotime($model->forUserModel['invalid_time'])
        ){
            throw new \yii\web\HttpException(200, Yii::t('datacenter','分享的数据报表已过期！'));
        }
        
        // 定义访问用户
        if($model->forUserModel['user_ids']){
            if(Yii::$app->user->id){
                if(!in_array(Yii::$app->user->id, $model->forUserModel['v_user_ids'])){
                    throw new \yii\web\HttpException(200, Yii::t('datacenter','您没有权限查看该分享的数据报表！'));
                }
            }else{
                // 未登录跳转登录
                Yii::$app->user->loginRequired();
            }
        }
        
        if($this->action->id!='password'){
            // 定义分享密码
            if($model->forUserModel['password']){
                $sessKey = "share_report_{$model->forUserModel['id']}_password";
                $password = Yii::$app->request->post('password',Yii::$app->request->get('password',Yii::$app->session[$sessKey]));
                if(strlen($password)>0){
                    if($password!=$model->forUserModel['password'] && md5($password)!=$model->forUserModel['password']){
                        throw new \yii\web\HttpException(200, Yii::t('datacenter','查看数据报表的密码不正确！'));
                    }else{
                        Yii::$app->session[$sessKey] = $password;
                    }
                }else{
                    return $this->redirect(['password','h'=>$h]);
                }
            }
            
            // 定义数据源
            $model->forUserModel->setSource();
        }
        
        return $model;
    }
}