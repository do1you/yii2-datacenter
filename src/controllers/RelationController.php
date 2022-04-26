<?php
/**
 * 模型对象DcRelation的增删改查控制器方法.
 */ 
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcRelation;
use yii\data\ActiveDataProvider;

class RelationController extends \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '模型关系');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
        return parent::beforeAction($action);
    }
    
    /**
     * 继承
     */
    public function actions()
    {
        $mId = Yii::$app->request->post('mId',Yii::$app->request->get('mId'));
        return [
            // 数据模型查询
            'model' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcModel',
                'col_id' => 'id',
                'col_text' => ['tb_name','tb_label'],
                'col_v_text' => 'v_tb_name',
                /*'col_where' => (Yii::$app->user->id=='1' ? [] : [
                    'source_db'=>\datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'2']),
                ]),*/
				'model_withs' => ['source'],
            ],
            // 数据字段查询
            'column' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcAttribute',
                'col_id' => 'name',
                'col_text' => ['name','label'],
                'col_v_text' => 'v_name',
                'col_where' => ["model_id"=>$mId],
            ],
        ];
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new DcRelation();
        $dataProvider = $model->search(Yii::$app->request->queryParams,null,['sourceModel.source','targetModel.source']);
        
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
            'treeData' => DcRelation::treeData(),
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
    public function actionCreate($mId='')
    {
        $model = new DcRelation();
        $model->loadDefaultValues();
        
        if($mId){
            $model->source_model = $mId;
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
        if($id && ($models = DcRelation::findAll($id))){
        	$transaction = DcRelation::getDb()->beginTransaction(); // 使用事务关联
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
        if (($model = DcRelation::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
