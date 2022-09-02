<?php
/**
 * 报表显示控制器方法.
 */
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcReport;
use yii\data\ActiveDataProvider;
use datacenter\models\DcSets;
use datacenter\models\DcUserReport;
use datacenter\models\DcUserSets;

class ReportApiController extends ReportViewController // \webadmin\BController  \webadmin\restful\AController
{
    /**
     * 当前控制器中需要缓存查询条件的方法
     */
    public $searchCacheActions = ['index', 'list', 'tree', 'data', 'set-data'];
    
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
    
    /**
     * 自定义内置方法
     */
    public function actions()
    {
        return [
            // 多接口请求
            'multi' => [
                'class' => '\webadmin\restful\MultiApiAction',
                'module' => $this->module->id,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }
    
    /**
     * 定义默认行为
     * {@inheritDoc}
     * @see \yii\rest\Controller::behaviors()
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 缓存查询条件
        $behaviors['searchBehaviors'] = [
            'class' => \webadmin\behaviors\SearchBehaviors::className(),
            'searchCacheActions' => $this->searchCacheActions,
            'cacheKey' => Yii::$app->session->id.'/datacenter/'.$this->id.'/'.
                ($this->action 
                    ? (in_array($this->action->id,['data','set-data']) ? $this->action->id
                        .'/'.Yii::$app->request->get('id','').'_'.Yii::$app->request->get('vid','') : $this->action->id) 
                    : 'index'),
        ];
        
        return $behaviors;
    }
        
    /**
     * 获取报表字段
     */
    public function actionField($id)
    {
        $model = $this->findReportModel($id);
        return $model['v_columns'];
    }
    
    /**
     * 获取报表数据
     */
    public function actionData($id,$vid='',$cache='1')
    {
        $model = $this->findReportModel($id,$vid,$cache);
        if(Yii::$app->request->get('debug')){
            return $this->render('/report-view/api', [
                'dataProvider' => $model->getDataProvider(),
            ]);
        }
        return $model->getDataProvider();
    }
    
    /**
     * 获取数据集字段
     */
    public function actionSetField($id)
    {
        $model = $this->findSetModel($id);
        return $model['v_columns'];
    }
    
    /**
     * 获取数据集数据
     */
    public function actionSetData($id,$vid='',$cache='1')
    {
        $model = $this->findSetModel($id,$vid,$cache);
        if(Yii::$app->request->get('debug')){
            return $this->render('/report-view/api', [
                'dataProvider' => $model->getDataProvider(),
            ]);
        }
        return $model->getDataProvider();
    }
    
    /**
     * 查找报表模型
     */
    protected function findReportModel($id,$vid='',$cache='1')
    {
        if($vid){
            if (($model = ($cache ? DcUserReport::model()->getCache('findModel',[$vid]) : DcUserReport::model()->findModel($vid)))) {
                return $model;
            }
        }else{
            if (($model = ($cache ? DcReport::model()->getCache('findModel',[$id]) : DcReport::model()->findModel($id)))) {
                return $model;
            }
        }
        
        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
    
    /**
     * 查找数据集模型
     */
    protected function findSetModel($id,$vid='',$cache='1')
    {
        if($vid){
            if (($model = ($cache ? DcUserSets::model()->getCache('findModel',[$vid]) : DcUserSets::model()->findModel($vid)))) {
                return $model;
            }
        }else{
            if (($model = ($cache ? DcSets::model()->getCache('findModel',[$id]) : DcSets::model()->findModel($id)))) {
                return $model;
            }
        }
        
        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}