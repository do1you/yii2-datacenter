<?php
/**
 * 报表显示视图
 */
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcReport;
use datacenter\models\DcSets;
use datacenter\models\DcUserReport;
use datacenter\models\DcRoleAuthority;

class ReportViewController extends \webadmin\BController
{
    /**
     * 当前控制器中需要缓存查询条件的方法
     */
    public $searchCacheActions = ['index', 'list', 'tree', 'view', 'set-view'];
    
    // 初始化
    public function init()
    {
        parent::init();
        
        // 签名效验
        if(Yii::$app->user->isGuest){
            $token = Yii::$app->request->get('access-token');
            $params = Yii::$app->request->get();
            $sign = isset($params['sign']) ? $params['sign'] : '';
            $dev = isset($params['dev']) ? $params['dev'] : '';
            unset($params['sign'],$params['dev']);
            $user = $token ? \webadmin\modules\authority\models\AuthUser::findOne(['access_token' => $token, 'state' => '0']) : null;

            if(($token || $sign) && $this->signKey($params, $user['password']) != $sign) { //  && !YII_DEBUG
                if(!empty($dev) && $dev=='ylltdev') { // 调试模式输出串码
                    print_r($this->signKey($params, $user['password'], true));
                    exit;
                } else {
                    throw new \yii\web\HttpException(200, Yii::t('datacenter','签名效验失败'));
                }
            }
        }
    }
    
    // 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据报表');
        Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
        Yii::$app->controller->currUrl = $this->module->id.'/report-view/index';
        
        // 选择动态数据源
        if(($source = Yii::$app->request->get('source')) && is_array($source)){
            $sIds = array_keys($source);
            $models = $sIds ? \datacenter\models\DcSource::findAll($sIds) : [];
            $models = \yii\helpers\ArrayHelper::map($models, 'id', 'v_self');
            foreach($source as $sid=>$id){
                if(isset($models[$sid]) && $id){
                    Yii::$app->session[$models[$sid]['v_sessionName']] = $id;
                }
            }
        }
        
        return parent::beforeAction($action);
    }
    
    // 设置场所过滤器
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // 缓存查询条件
        $behaviors['searchBehaviors'] = [
            'class' => \webadmin\behaviors\SearchBehaviors::className(),
            'searchCacheActions' => $this->searchCacheActions,
            'cacheKey' => Yii::$app->session->id.'/datacenter/report-api/'.
                ($this->action ? (in_array($this->action->id,['view','set-view']) ? str_replace('view','data',$this->action->id) : $this->action->id) : 'index'),
        ];
        
        // 已登录用户免判断
        if(!Yii::$app->user->isGuest || !Yii::$app->request->get('access-token')){
            return $behaviors;
        }
        
        // 通过token直接登录
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\CompositeAuth::className(),
            'authMethods' => [
                \yii\filters\auth\HttpBearerAuth::className(),
                \yii\filters\auth\QueryParamAuth::className(),
            ],
            'optional' => ['token'],
        ];
        return $behaviors;
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
        /*
        $defSets = \datacenter\models\DcSets::model()->allDefSets(Yii::$app->user->id,null,1);
        $userSets = \datacenter\models\DcSets::model()->allUserSets(Yii::$app->user->id,null,1);
        $defReport = \datacenter\models\DcReport::model()->allDefReport(Yii::$app->user->id,null,1);
        $userReport = \datacenter\models\DcReport::model()->allUserReport(Yii::$app->user->id,null,1);
        */
        
        $defSets = \datacenter\models\DcSets::model()->getCache('allDefSets',[Yii::$app->user->id,null,1]);
        $userSets = \datacenter\models\DcSets::model()->getCache('allUserSets',[Yii::$app->user->id,null,1]);
        $defReport = \datacenter\models\DcReport::model()->getCache('allDefReport',[Yii::$app->user->id,null,1]);
        $userReport = \datacenter\models\DcReport::model()->getCache('allUserReport',[Yii::$app->user->id,null,1]);
        
        $catIds = array_merge(array_keys($defSets),array_keys($userSets),array_keys($defReport),array_keys($userReport));
        $catIds = array_unique($catIds);
        //$catList = $catIds ? \datacenter\models\DcCat::model()->findModel(['id'=>$catIds],1) : [];
        $catList = $catIds ? \datacenter\models\DcCat::model()->getCache('findModel',[['id'=>$catIds],1]) : [];

        return $this->render('index', [
            'defSets' => $defSets,
            'userSets' => $userSets,
            'defReport' => $defReport,
            'userReport' => $userReport,
            'catIds' => $catIds,
            'catList' => $catList,
        ]);
    }
    
    /**
     * 收藏报表
     */
    public function actionCollection($id,$show='')
    {
        /*
        $result = [];
        $model = DcUserReport::find()->where(['report_id'=>$id,'user_id'=>Yii::$app->user->id])->one();
        if(empty($show)){
            $report = DcReport::findOne($id);
            if(empty($model)){
                if($report['create_user']==Yii::$app->user->id 
                    || ($roleIds = \yii\helpers\ArrayHelper::map(\webadmin\modules\authority\models\AuthUserRole::findAll(['user_id'=>Yii::$app->user->id]), 'role_id', 'role_id')
                        && DcRoleAuthority::findOne(['source_type'=>'5','role_id'=>$roleIds,'source_id'=>$id]))
                ){
                    $model = new DcUserReport;
                    $model->loadDefaultValues();
                }
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
        */
    }
    
    /**
     * 报表显示
     */
    public function actionView()
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
        $list = $this->findModel(is_array($id) ? $id : ($id ? explode(',',$id) : []));
        if(!empty(Yii::$app->request->get('is_export'))) return $this->exportExcel($list);
        
        return $this->render('display', [
            'list' => $list,
        ]);
    }
    
    /**
     * 数据集显示
     */
    public function actionSetView()
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
        $list = $this->findSetModel(is_array($id) ? $id : ($id ? explode(',',$id) : []));
        if(!empty(Yii::$app->request->get('is_export'))) return $this->exportExcel($list);
        
        return $this->render('display', [
            'list' => $list,
        ]);
    }
    
    /**
     * 导出报表
     */
    public function exportExcel($list)
    {
        if($list && is_array($list)){
            foreach($list as $key=>$item){
                if($key==0){
                    $dataProvider = $item->getDataProvider();
                    $titles= $item['v_excelData'];
                    $filename = $item['title'];
                    $options = ['title'=>$item['title']];
                }else{
                    $options['sheets'][$item['id']] = [
                        'dataProvider' => $item->getDataProvider(),
                        'titles' => $item['v_excelData'],
                        'title' => $item['title'],
                    ];
                }
            }
        }
        
        if(empty($dataProvider) || empty($titles)){
            throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
        }

        return \webadmin\ext\PhpExcel::export(null, $dataProvider, $titles, $filename, $options);
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
    
    // 生成签名
    private function signKey($params = [], $signKey = 'test', $return = false)
    {
        ksort($params);
        $signStr = http_build_query($params, '', '|');
        $sign = hash("sha256", ($signStr . $signKey));
        if($return) {
            return [
                'sign' => $sign,
                'signStr' => $signStr . '{私钥}',
            ];
        }else{
            return $sign;
        }
    }
}
