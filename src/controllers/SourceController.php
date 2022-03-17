<?php
/**
 * 模型对象DcSource的增删改查控制器方法.
 */ 
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcSource;
use yii\data\ActiveDataProvider;

class SourceController extends \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据源');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
        return parent::beforeAction($action);
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new DcSource();
		$dataProvider = $model->search(Yii::$app->request->queryParams);
        
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
            'treeData' => DcSource::treeData(),
        ]);
    }
    */

    /**
     * 查看模型
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 添加模型
     */
    public function actionCreate()
    {
        $model = new DcSource();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
            return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 修改模型
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息修改成功'));
            return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 删除模型，支持批量删除
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
        if($id && ($models = DcSource::findAll($id))){
        	$transaction = DcSource::getDb()->beginTransaction(); // 使用事务关联
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
     * 初始化数据模型
     */
    public function actionInit($id)
    {
        $model = $this->findModel($id);
        
        if(($num = $model->initDataSet())) {
            Yii::$app->session->setFlash('success',Yii::t('datacenter', '初始化数据模型成功').$num);
        }else{
            Yii::$app->session->setFlash('error',Yii::t('datacenter', '初始化数据模型失败'));
        }
        
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }
    
    /**
     * 测试连接
     */
    public function actionTest()
    {
        $model = new DcSource();
        $model->loadDefaultValues();
        $result = [];
        
        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && ($db = $model->getSourceDb(true, false, true))) {
            $result['success'] = true;
        }elseif($model->hasErrors()){
            $result['msg'] = implode("；",$model->getErrorSummary(true));
        }else{
            $result['msg'] = Yii::t('datacenter','数据库连接失败');
        }
        
        return json_encode($result);
    }

    /**
     * 查找模型
     */
    protected function findModel($id)
    {
        if (($model = DcSource::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
