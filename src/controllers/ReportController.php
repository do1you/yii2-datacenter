<?php
/**
 * 模型对象DcReport的增删改查控制器方法.
 */ 
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcReport;
use yii\data\ActiveDataProvider;
use datacenter\models\DcSets;
use datacenter\models\DcSetsColumns;
use datacenter\models\DcReportColumns;
use datacenter\models\DcRoleAuthority;

class ReportController extends \webadmin\BController
{
    public $enableCsrfValidation = false;
    
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据报表');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
        return parent::beforeAction($action);
    }
    
    /**
     * 继承
     */
    public function actions()
    {
        $mId = Yii::$app->request->post('mId',Yii::$app->request->get('mId'));
        if($mId){
            $_GET['per-page'] = 2000; // 树菜单的时候，最大记录数
        }
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
            // 数据模型查询
            'model' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcModel',
                'col_id' => 'id',
                'col_text' => ['tb_name','tb_label'],
                'col_v_text' => 'v_tb_name',
                'col_where' => (Yii::$app->user->id=='1' ? [] : [
                    'source_db'=>\datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'2']),
                ]),
				'model_withs' => ['source'],
            ],
            // 数据集查询
            'sets' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcSets',
                'col_id' => 'id',
                'col_text' => 'title',
                'col_v_text' => 'v_title',
                'col_where' => (Yii::$app->user->id=='1' ? ["cat_id"=>$mId] : [
					'and',
                    ['=', "dc_sets.cat_id", $mId],
                    [
						'or',
						['in', 'dc_sets.id', \datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'])],
						['=', 'dc_sets.create_user', Yii::$app->user->id],
					]
                ]),
            ],
            // 数据集字段查询
            'column' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcSetsColumns',
                'col_id' => 'id',
                'col_text' => ['name','label'],
                'col_v_text' => 'v_name',
                'col_where' => ["set_id"=>$mId],
                'model_withs' => ['sets'],
            ],
        ];
    }
    
    /**
     * 构建报表
     */
    public function actionBuild($id=null)
    {
        $this->is_open_nav = false; // 隐藏左侧菜单
        return $this->render('build', [
            'id' => $id,
        ]);
    }
    
    // 异步加载报表
    public function actionReport($id='',$act='')
    {
        $reportList = \datacenter\models\DcReport::model()->findModel((in_array($act,['copy','update'])
            ? [ 'id' => ($id ? $id : '-999') ]
            : [
                'create_user' => Yii::$app->user->id,
                'state' => '9',
            ]),true);
        return $this->render('report', [
            'reportList' => $reportList,
        ]);
    }
    
    // 异步加载报表字段
    public function actionReportCol($id='')
    {
        $model = $id ? \datacenter\models\DcReportColumns::findOne($id) : null;
        $model = $model ? $model : new \datacenter\models\DcReportColumns();
        $model->isNewRecord && $model->loadDefaultValues();
        return $this->render('report-col', [
            'model' => $model,
        ]);
    }
    
    /**
     * 保存报表
     */
    public function actionSave($id='',$rid='',$type='',$nid='',$act='')
    {
        $result = [];
        $model = $rid ? DcReport::findOne($rid) : null;
        switch($type){
            case '1': // 数据集
            case '2': // 数据集字段
                $result = $this->_addReport($result,$model,$id,$rid,$type,$nid,$act);
                break;
            case '3': // 删除报表
                if($model && $model->state=='9' && $model->delete()){
                    $result['success'] = true;
                }else{
                    $result['msg'] = "编辑报表时不允许删除操作，请返回报表管理界面删除！";
                }
                break;
            case '4': // 保存报表
                $result = $this->_saveReport($result,$model,$id,$rid,$type,$nid,$act);
                break;
            case '5': // 字段顺序
                $result = $this->_orderReport($result,$model,$id,$rid,$type,$nid,$act);
                break;
            case '6': // 冻结/取消列
                $result = $this->_frozenReport($result,$model,$id,$rid,$type,$nid,$act);
                break;
            case '7': // 删除列
                $result = $this->_removeReport($result,$model,$id,$rid,$type,$nid,$act);
                break;
            case '8': // 添加编辑列
                $result = $this->_colReport($result,$model,$id,$rid,$type,$nid,$act);
                break;
            default:
                $result['msg'] = "参数有误";
                break;
        }
        
        return json_encode($result);
    }
    
    // 添加编辑列
    private function _colReport($result,$model,$id,$rid,$type,$nid,$act)
    {
        if($model && ($cModel = $id ? DcReportColumns::findOne($id) : (new DcReportColumns))){
            $cModel->report_id = $cModel->report_id ? $cModel->report_id : $rid;
            if($cModel->load(Yii::$app->request->post()) && $cModel->save()){
                $result['success'] = true;
            }else{
                $result['msg'] = implode("；",$cModel->getErrorSummary(true));
            }
        }else{
            $result['msg'] = "参数有误";
        }
        return $result;
    }
    
    // 删除数据表
    private function _removeReport($result,$model,$id,$rid,$type,$nid,$act)
    {
        if($model && ($cModel = $id ? DcReportColumns::findOne($id) : null)){
            if(count($model['columns'])<=1){
                $model->delete();
            }else{
                $cModel->delete();
            }
            $result['success'] = true;
        }else{
            $result['msg'] = "参数有误";
        }
        return $result;
    }
    
    // 冻结/取消数据表
    private function _frozenReport($result,$model,$id,$rid,$type,$nid,$act)
    {
        if($model && ($cModel = $id ? DcReportColumns::findOne($id) : null)){
            $result['success'] = true;
            $cModel->is_frozen = $cModel->is_frozen=='1' ? '0' : '1';
            $cModel->save(false);
        }else{
            $result['msg'] = "参数有误";
        }
        return $result;
    }
    
    // 保存数据表
    private function _saveReport($result,$model,$id,$rid,$type,$nid,$act)
    {
        if($model){
            $model->state = '0';
            if($model->load(Yii::$app->request->post()) && $model->save()){
                $result['success'] = true;
            }else{
                $result['msg'] = implode("；",$model->getErrorSummary(true));
            }
        }else{
            $result['msg'] = "参数有误";
        }
        return $result;
    }
    
    // 排序数据表
    private function _orderReport($result,$model,$id,$rid,$type,$nid,$act)
    {
        if($model && ($cModel = $id ? DcReportColumns::findOne($id) : null)){
            // 匹配出需要调整顺序的字段
            $columns = $model['columns'];
            $nModel = $nid ? DcReportColumns::findOne($nid) : null;
            $paixu = $nModel ? $nModel['paixu'] + 2 : 2; // 计算排序初始值
            if($nModel){
                foreach($columns as $key=>$item){
                    if($item['id']==$nModel['id']){
                        $skip = true;
                        unset($columns[$key]);
                    }elseif(!empty($skip)){
                        unset($columns[$key]);
                    }
                }
            }
            
            // 更新排序
            $paixu += count($columns);
            foreach($columns as $item){
                $item['paixu'] = $paixu--;
                $item->save(false);
            }
            $cModel['paixu'] = $paixu;
            $cModel['is_frozen'] = $nModel&&$nModel['is_frozen'] ? '1' : '0';
            $cModel->save(false);
            
            $result['success'] = true;
        }else{
            $result['msg'] = "参数有误";
        }
        return $result;
    }
    
    // 新增数据表
    private function _addReport($result,$model,$id,$rid,$type,$nid,$act)
    {
        if(empty($id) || !($cModel = $type=='1' ? DcSets::find()->where(['id'=>$id])->with(['columns.sets'])->one() : DcSetsColumns::findOne($id))){
            $result['msg'] = "数据集信息不存在！";
            return $result;
        }
        
        $columns = $type=='1' ? $cModel['columns'] : [$cModel];
        $reportIds = [];
        
        // 判断数据集
        $setLists = \yii\helpers\ArrayHelper::map($columns, 'set_id', 'sets');
        $mainSet = $model&&$model['v_sets'] ? $model['v_mainSet'] : null;
        $mainSet && $mainSet->joinSets($setLists);
        if((!$model || $setLists) && $columns){
            if(in_array($act,['copy','update'])){
                $result['msg'] = "当前正在编辑数据报表，无法创建新数据表！";
                return $result;
            }
            $model = new DcReport;
            $model->state = 9;
            $model->title = "新报表".date('YmdHis');
            $model->save(false);
        }
        
        // 写入报表字段
        if($model){
            foreach($columns as $column){
                $colModel = new DcReportColumns();
                $colModel->load([
                    'label' => $column->label,
                    'report_id' => $model->id,
                    'set_id' => $column->set_id,
                    'col_id' => $column->id,
                ],'');
                $colModel->save(false);
            }
            $result['success'] = true;
        }else{
            $result['msg'] = "数据集未配置任何字段";
        }
        return $result;
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new DcReport();
		$dataProvider = $model->search(Yii::$app->request->queryParams,(Yii::$app->user->id=='1' ? null : [
		    'or',
		    ['in', 'dc_report.id', \datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'5'])],
		    ['=', 'dc_report.create_user', Yii::$app->user->id],
		]),['columns.sets','user','cat.parent.parent.parent']);
        
        if(!empty(Yii::$app->request->get('is_export'))) return $this->export($model, $dataProvider);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }
    
    /**
     * 导出列表
     */
    /*public function export($model, \yii\data\ActiveDataProvider $dataProvider, $titles = [], $filename = null, $options = [])
    {
        $titles = [
            // 自定义输出字段
        ];
        
        return parent::export($model, $dataProvider, $titles, $this->pageTitle.date('_YmdHis'), []);
    }*/
    
    /**
     * 树型数据
     */
    /*
    public function actionTree()
    {
    	Yii::$app->session[$this->id] = [$this->action->id];
        return $this->render('tree', [
            'treeData' => DcReport::treeData(),
        ]);
    }
    */

    /**
     * 查看模型
     */
   /* public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }*/

    /**
     * 添加模型
     */
    /*public function actionCreate()
    {
        $model = new DcReport();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
            return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }*/

    /**
     * 修改报表
     */
    public function actionUpdate($id)
    {
        return $this->actionBuild($id);
    }
    
    /**
     * 复制报表
     */
    public function actionCopy($id)
    {
        $model = $this->findModel($id);
        $newModel = $model->copyReport();
        return $this->actionBuild($newModel['id']);
    }

    /**
     * 删除模型，支持批量删除
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
        if($id && ($models = DcReport::findAll($id))){
        	$transaction = DcReport::getDb()->beginTransaction(); // 使用事务关联
        	foreach($models as $model){
        		$model->delete();
        	}
            $transaction->commit(); // 提交事务
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息删除成功'));
        }else{
        	Yii::$app->session->setFlash('error',Yii::t('common', '需要删除的对象信息不存在'));
        }
        
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }

    /**
     * 查找模型
     */
    protected function findModel($id)
    {
        if (($model = DcReport::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
