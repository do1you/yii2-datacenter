<?php
/**
 * 模型对象DcSetsRelation的增删改查控制器方法.
 */ 
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcSetsRelation;
use datacenter\models\DcRoleAuthority;
use datacenter\models\DcUserAuthority;
use yii\data\ActiveDataProvider;

class SetsRelController extends ReportViewController // \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据集关系');
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
                'col_where' => (Yii::$app->user->id=='1' ? [] : [
                    'source_db'=>\yii\helpers\ArrayHelper::merge(\datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'2']), 
                        \datacenter\models\DcUserAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'2'])),
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
                'col_where' => (Yii::$app->user->id=='1' ? [] : [
					'or',
                    ['in', 'dc_sets.id', \datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'])],
                    ['in', 'dc_sets.id', \datacenter\models\DcUserAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'])],
					['=', 'dc_sets.create_user', Yii::$app->user->id],
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
                'col_sort' => 'dc_sets_columns.is_frozen desc,dc_sets_columns.paixu desc,dc_sets_columns.id asc',
            ],
        ];
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new DcSetsRelation();
		$dataProvider = $model->search(Yii::$app->request->queryParams,(
		    Yii::$app->user->id=='1' ? null : [
		        'or',
		        ['in', 'source_sets', DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'])],
		        ['in', 'source_sets', DcUserAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'])],
		        ['in', 'target_sets', DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'])],
		        ['in', 'target_sets', DcUserAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4'])],
		        ['=', 'sourceSets.create_user', Yii::$app->user->id],
		        ['=', 'targetSets.create_user', Yii::$app->user->id],
		    ]
	    ),['sourceSets','targetSets'],(Yii::$app->user->id=='1' ? [] : ['sourceSets','targetSets']));
        
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
            'treeData' => DcSetsRelation::treeData(),
        ]);
    }
    */

    /**
     * 查看模型
     */
    public function actionView($id='')
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
        $model = new DcSetsRelation();
        $model->loadDefaultValues();
        
        if($sId){
            $model->source_sets = $sId;
        }

        if($model->load(Yii::$app->request->post()) && $model->ajaxValidation() &&
            ($transaction = DcSetsRelation::getDb()->beginTransaction()) && $model->save()
        ){
            $transaction->commit(); // 提交事务
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

        if($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && 
            ($transaction = DcSetsRelation::getDb()->beginTransaction()) && $model->save()
        ){
            $transaction->commit(); // 提交事务
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
        if($id && ($models = DcSetsRelation::findAll($id))){
        	$transaction = DcSetsRelation::getDb()->beginTransaction(); // 使用事务关联
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
        if (($model = DcSetsRelation::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
