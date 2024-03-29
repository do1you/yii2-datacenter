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
use datacenter\models\DcUserAuthority;
use datacenter\models\DcUserSets;
use datacenter\models\DcShare;

class ReportViewController extends \webadmin\BController
{
    /**
     * 当前控制器中需要缓存查询条件的方法
     */
    public $searchCacheActions = ['index', 'list', 'tree', 'view', 'set-view']; // , 'collection', 'set-collection'
        
    // 初始化
    public function init()
    {
        parent::init();
        
        $this->_tokenLogin();
    }
    
    // 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据报表');
        Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
        if($this->id=='report-view'){
            Yii::$app->controller->currUrl = $this->module->id.'/report-view/index';
        }
        
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
        
        // 多表查看
        if(($reid = Yii::$app->request->get('reid')) && ($id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id')))){
            $this->redirect([$this->action->id,$reid=>$id]);
            return false;
        }
        
        return parent::beforeAction($action);
    }
    
    // 设置场所过滤器
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // 禁止异步下载EXCEL，没开后台队列
        unset($behaviors['excelBehaviors']); 
        
        // 缓存查询条件
        $behaviors['searchBehaviors'] = [
            'class' => \webadmin\behaviors\SearchBehaviors::className(),
            'searchCacheActions' => $this->searchCacheActions,
            'cacheKey' => Yii::$app->session->id.'/datacenter/report-api/'.
                ($this->action 
                    ? (in_array($this->action->id,['view','set-view','collection','set-collection']) ? str_replace(['collection','view'],'data',$this->action->id)
                        .'/'.Yii::$app->request->get('id','').'_'.Yii::$app->request->get('vid','') : $this->action->id)
                    : 'index'),
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
        
        // 权限判断放到token之后处理
        if(!empty($behaviors['webAuthFilter'])){
            $webAuthFilter = $behaviors['webAuthFilter'];
            unset($behaviors['webAuthFilter']);
            $behaviors['webAuthFilter'] = $webAuthFilter;
        }
        
        return $behaviors;
    }
    
    /**
     * 继承
     */
    public function actions()
    {
        return [
            // 系统用户
            'user' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\webadmin\modules\authority\models\AuthUser',
                'col_id' => 'id',
                'col_text' => 'name',
                'col_v_text' => 'name',
                //'col_where' => [],
            ],
        ];
    }
    
    /**
     * API同步登录
     */
    protected function _tokenLogin($isToo = false)
    {
        // 签名效验
        if(($token = Yii::$app->request->get('access-token')) || Yii::$app->user->isGuest){
            $params = Yii::$app->request->get();
            $sign = isset($params['sign']) ? $params['sign'] : '';
            $dev = isset($params['dev']) ? $params['dev'] : '';
            $t = isset($params['t']) ? intval($params['t']) : '';
            unset($params['sign'],$params['dev']);
            $user = ($token && !Yii::$app->user->isGuest) ? \webadmin\modules\authority\models\AuthUser::findOne(['access_token' => $token, 'state' => '0']) : null;
            
            // 匹配不上或者首次登录的时候进行一次同步
            if($token && (empty($user) || Yii::$app->user->isGuest)){
                $app = Yii::$app;
                \webadmin\modules\config\models\SysCrontab::getConsoleApp()->runAction('datacenter/yezhi-administrator/rsync-admin');
                Yii::$app = $app;
                
                $user = $token ? \webadmin\modules\authority\models\AuthUser::findOne(['access_token' => $token, 'state' => '0']) : null;
            }
            
            // 判断链接是否失效
            if(empty($t) || time()-$t>3600){
                throw new \yii\web\HttpException(200, Yii::t('datacenter','该链接已经失效,请重新进入~'));
            }
            
            if($token || $sign) { //  && !YII_DEBUG
                if(empty($user) || $this->signKey($params, $user['password']) != $sign){
                    if(!empty($dev) && $dev=='ylltdev') { // 调试模式输出串码
                        print_r(empty($user) ? 'NOT USER' : $this->signKey($params, $user['password'], true));
                        exit;
                    } else {
                        // 刚修改过密码
                        if($user){
                            $app = Yii::$app;
                            \webadmin\modules\config\models\SysCrontab::getConsoleApp()->runAction('datacenter/yezhi-administrator/rsync-admin',[trim($user['login_name'], 'YLLT_')]);
                            Yii::$app = $app;
                            
                            return $this->_tokenLogin(true);
                        }
                        
                        throw new \yii\web\HttpException(200, Yii::t('datacenter','签名效验失败'));
                    }
                }else{
                    // 从接口进来的
                    Yii::$app->session['API_TOKEN'] = $token;
                }
            }
        }
        
        // 接口进来的默认不显示导航菜单
        if(Yii::$app->session['API_TOKEN']){
            // $this->layout = '@webadmin/views/html5';
            $this->layout = 'html5';
        }
    }
    
    /**
     * 设置数据源
     */
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
        
        $defSets = \datacenter\models\DcSets::model()->getCache('allDefSets',[Yii::$app->user->id,['=','is_index_show','1'],1]);
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
     * 收藏/保存/分享报表
     */
    public function actionCollection()
    {
        $share = Yii::$app->request->getBodyParam('share',Yii::$app->getRequest()->getQueryParam('share'));
        $reportId = Yii::$app->request->getBodyParam('reportId',Yii::$app->getRequest()->getQueryParam('reportId'));
        $setId = Yii::$app->request->getBodyParam('setId',Yii::$app->getRequest()->getQueryParam('setId'));
        $userReportId = Yii::$app->request->getBodyParam('userReportId',Yii::$app->getRequest()->getQueryParam('userReportId'));
        $userSetId = Yii::$app->request->getBodyParam('userSetId',Yii::$app->getRequest()->getQueryParam('userSetId'));
        $searchValues = Yii::$app->request->getBodyParam('SysConfig',Yii::$app->getRequest()->getQueryParam('SysConfig',''));
        $modelParams = Yii::$app->request->getBodyParam('DcUserReport',Yii::$app->getRequest()->getQueryParam('DcUserReport',[]));
        $shareParams = Yii::$app->request->getBodyParam('DcShare',Yii::$app->getRequest()->getQueryParam('DcShare',[]));
        $is_new = isset($modelParams['is_new']) ? $modelParams['is_new'] : '0';
        
        if($searchValues && is_array($searchValues)){
            foreach($searchValues as $k=>$v){
                if(!is_array($v) && strlen($v)<=0){
                    unset($searchValues[$k]);
                }
            }
        }
        
        // 动态数据源
        if(($reportModel = $reportId ? DcReport::findOne($reportId) : ($setId ? DcSets::findOne($setId) : null)) && ($sourceList = $reportModel ? $reportModel['v_source'] : [])){
            if(!is_array($searchValues)) $searchValues = [];
            foreach($sourceList as $source){
                if($source['is_dynamic']=='1'){
                    $searchValues['source'][$source['id']] = Yii::$app->session[$source['v_sessionName']];
                }
            }
        }
        
        $searchValues = is_array($searchValues) ? json_encode($searchValues,302) : $searchValues;
        
        $result = [];
        if($share=='9'){
            $model = new DcShare;
            $model->loadDefaultValues();
            $model->load($shareParams,'');
            if($model->load([
                'share_user' => Yii::$app->user->id,
                'report_id' => ($reportId ? $reportId : 0),
                'set_id' => ($setId ? $setId : 0),
                'search_values' => $searchValues,
            ],'') && $model->save()){
                $result['success'] = true;
                $result['url'] = $model['v_url'];
                $result['password'] = htmlspecialchars_decode($model['password']);
            }else{
                $result['msg'] = implode("；",$model->getErrorSummary(true));
            }
        }elseif($reportId){
            if($is_new && $userReportId) unset($userReportId);
            if($userReportId) $model = DcUserReport::findOne($userReportId);
            if(empty($model)){
                $model = new DcUserReport;
                $model->loadDefaultValues();
            }            
            $model->load($modelParams,'');
            if($model->load([
                'report_id' => $reportId,
                'search_values' => $searchValues,
            ],'') && $model->save()){
                $result['success'] = true;
                \datacenter\models\DcReport::model()->getCache('allUserReport',[Yii::$app->user->id,null,1],86400,true);
            }else{
                $result['msg'] = implode("；",$model->getErrorSummary(true));
            }
        }elseif($setId){
            if($is_new && $userSetId) unset($userSetId);
            if($userSetId) $model = DcUserSets::findOne($userSetId);
            if(empty($model)){
                $model = new DcUserSets;
                $model->loadDefaultValues();
            }
            $model->load($modelParams,'');
            if($model->load([
                'set_id' => $setId,
                'search_values' => $searchValues,
            ],'') && $model->save()){
                $result['success'] = true;
                \datacenter\models\DcSets::model()->getCache('allUserSets',[Yii::$app->user->id,null,1],86400,true);
            }else{
                $result['msg'] = implode("；",$model->getErrorSummary(true));
            }
        }elseif($userReportId){
            $model = DcUserReport::findOne($userReportId);
            if($model){
                $model->delete();
                $result['success'] = true;
                \datacenter\models\DcReport::model()->getCache('allUserReport',[Yii::$app->user->id,null,1],86400,true);
            }else{
                $result['msg'] = "参数有误";
            }
        }elseif($userSetId){
            $model = DcUserSets::findOne($userSetId);
            if($model){
                $model->delete();
                $result['success'] = true;
                \datacenter\models\DcSets::model()->getCache('allUserSets',[Yii::$app->user->id,null,1],86400,true);
            }else{
                $result['msg'] = "参数有误";
            }
        }
        return $result;
    }
    
    /**
     * 收藏数据集
     */
    public function actionSetCollection()
    {
        return $this->actionCollection();
    }
    
    /**
     * 报表显示
     */
    public function actionView($id='')
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
        $vid = Yii::$app->request->getBodyParam('vid',Yii::$app->getRequest()->getQueryParam('vid'));
        $list = $this->findReportModel((is_array($id) ? $id : ($id ? explode(',',$id) : [])),(is_array($vid) ? $vid : ($vid ? explode(',',$vid) : [])));
        if(!empty(Yii::$app->request->get('is_export'))) return $this->exportExcel($list);
        
        if(($titles = $list ? \yii\helpers\ArrayHelper::map($list,'id','title') : [])){
            Yii::$app->controller->pageTitle = implode(" ", $titles);
        }
        
        return $this->render('display', [
            'list' => $list,
        ]);
    }
    
    /**
     * 数据集显示
     */
    public function actionSetView($id='')
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
        $vid = Yii::$app->request->getBodyParam('vid',Yii::$app->getRequest()->getQueryParam('vid'));
        $list = $this->findSetModel((is_array($id) ? $id : ($id ? explode(',',$id) : [])),(is_array($vid) ? $vid : ($vid ? explode(',',$vid) : [])));
        if(!empty(Yii::$app->request->get('is_export'))) return $this->exportExcel($list);
        
        if(($titles = $list ? \yii\helpers\ArrayHelper::map($list,'id','title') : [])){
            Yii::$app->controller->pageTitle = implode(" ", $titles);
        }
        
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
                $titles = $item['v_excelData'];
                $colspans = $item['v_excelTwoData'];
                if(($searchStr = $item->getV_excelSearchStr())){
                    if($colspans){
                        $titles[] = $searchStr;
                    }else{
                        $first = reset($excelData);
                        $last = end($excelData);
                        $colspans[$first['value']] = [
                            'attribute' => $last['value'],
                            'label' => $searchStr,
                        ];
                    }
                }
                // 拼装EXCEL
                if($key==0){
                    $dataProvider = $item->getDataProvider();
                    $titles = $titles;
                    $filename = $item['title'];
                    $options = ['title'=>$item['title'],'colspans'=>$colspans,];
                }else{
                    $options['sheets'][$item['id']] = [
                        'dataProvider' => $item->getDataProvider(),
                        'titles' => $titles,
                        'title' => $item['title'],
                        'colspans' => $colspans,
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
    protected function findReportModel($id,$vid=[])
    {
        if($vid){
            if(is_array($vid)){
                if(($list = DcUserReport::model()->getCache('findModel',[['id'=>$vid], true]))){
                    return $list;
                }
            }elseif(($model = DcUserReport::model()->getCache('findModel',[$vid])) !== null) {
                return $model;
            }
        }else{
            if(is_array($id)){
                if(($list = DcReport::model()->getCache('findModel',[['id'=>$id], true]))){
                    return $list;
                }
            }elseif(($model = DcReport::model()->getCache('findModel',[$id])) !== null) {
                return $model;
            }
        }
        
        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
    
    /**
     * 查找数据集模型
     */
    protected function findSetModel($id,$vid=[])
    {
        if($vid){
            if(is_array($vid)){
                if(($list = DcUserSets::model()->getCache('findModel',[['id'=>$vid], true]))){
                    return $list;
                }
            }elseif(($model = DcUserSets::model()->getCache('findModel',[$vid])) !== null) {
                return $model;
            }
        }else{
            if(is_array($id)){
                if(($list = DcSets::model()->getCache('findModel',[['id'=>$id], true]))){
                    return $list;
                }
            }elseif(($model = DcSets::model()->getCache('findModel',[$id])) !== null) {
                return $model;
            }
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
                'signStr' => $signStr . '{私钥}'.$signKey,
            ];
        }else{
            return $sign;
        }
    }
    
    /**
     * 输出前处理
     */
    public function beforeSend($event)
    {
        parent::beforeSend($event);
        
        // iframe cookie处理
        if(
            Yii::$app->request->isSecureConnection 
            && ($userAgent = strtolower(Yii::$app->request->userAgent))
            && strpos($userAgent, 'chrome')!==false
            && preg_match('/\schrome\/((\d+)\.[\d\.]+)\s/', $userAgent, $matches)
            && !empty($matches[2])
            && intval($matches[2]) >= 80 // chrome大于80的版本设置SameSite属性
        ){
            $response = Yii::$app->getResponse();
            $cookies = $response->getCookies();
            foreach($cookies as $cookie){
                $cookie->path = '/; SameSite=None';
                $cookie->secure = true;
            }
        }
    }
}
