<?php
/**
 * 模型对象DcModel的增删改查控制器方法.
 */ 
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcModel;
use yii\data\ActiveDataProvider;

class ModelController extends \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据模型');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
        return parent::beforeAction($action);
    }
    
    /**
     * 继承
     */
    public function actions()
    {
        return [
            // 数据源查询
            'source' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcSource',
                'col_id' => 'id',
                'col_text' => 'name',
                'col_v_text' => 'name',
            ],
            // 数据模型查询
            'model' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcModel',
                'col_id' => 'id',
                'col_text' => ['tb_name','tb_label'],
                'col_v_text' => 'v_tb_name',
				'model_withs' => ['source'],
            ],
            
        ];
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new DcModel();
		$dataProvider = $model->search(Yii::$app->request->queryParams,null,['source','cat.parent.parent.parent']);
        
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
            'treeData' => DcModel::treeData(),
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
    public function actionCreate($sId='')
    {
        $model = new DcModel();
        $model->loadDefaultValues();
        
        if($sId){
            $model->source_db = $sId;
        }

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
        if($id && ($models = DcModel::findAll($id))){
        	$transaction = DcModel::getDb()->beginTransaction(); // 使用事务关联
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
     * 移动分类
     */
    public function actionMove()
    {
        $cat_id = Yii::$app->request->getBodyParam('move_cat_id',Yii::$app->getRequest()->getQueryParam('move_cat_id'));
        if($cat_id){
            $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
            if($id && ($models = DcModel::findAll($id))){
                $transaction = DcModel::getDb()->beginTransaction(); // 使用事务关联
                foreach($models as $model){
                    $model->cat_id = $cat_id;
                    $model->save(false);
                }
                $transaction->commit(); // 提交事务
                Yii::$app->session->setFlash('success',Yii::t('common', '对象信息删除成功'));
            }else{
                Yii::$app->session->setFlash('error',Yii::t('common', '需要删除的对象信息不存在'));
            }
        }else{
            Yii::$app->session->setFlash('error',Yii::t('datacenter', '请选择需要移动到的分类'));
        }
        
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }
    
    /**
     * 复制模型
     */
    public function actionCopy($id)
    {
        $model = $this->findModel($id);
        
        $transaction = DcModel::getDb()->beginTransaction(); // 使用事务关联
        if(($num = $model->copyModel())) {
            $transaction->commit(); // 提交事务
            Yii::$app->session->setFlash('success',Yii::t('datacenter', '复制模型成功'));
        }else{
            Yii::$app->session->setFlash('error',Yii::t('datacenter', '复制模型失败'));
        }
        
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }
    
    /**
     * 构建数据集
     */
    public function actionInit($id)
    {
        $model = $this->findModel($id);
        
        if(($num = $model->createDataSet())) {
            Yii::$app->session->setFlash('success',Yii::t('datacenter', '构建数据集成功'));
        }else{
            Yii::$app->session->setFlash('error',Yii::t('datacenter', '构建数据集失败'));
        }
        
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }

    /**
     * 查找模型
     */
    protected function findModel($id)
    {
        if (($model = DcModel::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
