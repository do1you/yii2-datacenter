<?php
/**
 * 模型对象DcSetsColumns的增删改查控制器方法.
 */ 
namespace datacenter\controllers;

use Yii;
use datacenter\models\DcSetsColumns;
use datacenter\models\DcRoleAuthority;
use yii\data\ActiveDataProvider;

class SetsColController extends \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据集属性');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		$this->currUrl = $this->module->id.'/'.$this->id.'/index';
		
        return parent::beforeAction($action);
    }
    
    /**
     * 继承
     */
    public function actions()
    {
        return [
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
                'col_where' => (Yii::$app->user->id=='1' ? [] : [
                    'id'=>\datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4']),
                ]),
            ],
            // 归属数据集查询
            'forsets' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\datacenter\models\DcSets',
                'col_id' => 'id',
                'col_text' => 'title',
                'col_v_text' => 'v_title',
                'col_where' => (Yii::$app->user->id=='1' ? [
                    'set_type' => 'model',
                    'id' => \yii\helpers\ArrayHelper::map(\datacenter\models\DcSetsRelation::findAll(['source_sets'=>Yii::$app->request->get('fId','-999')]), 'target_sets', 'target_sets'),
                ] : [
                    'id'=>\datacenter\models\DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4']),
                    'set_type' => 'model',
                    'id' => \yii\helpers\ArrayHelper::map(\datacenter\models\DcSetsRelation::findAll(['source_sets'=>Yii::$app->request->get('fId','-999')]), 'target_sets', 'target_sets'),
                ]),
            ],
            // 数据字典
            'dd' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\webadmin\modules\config\models\SysLdItem',
                'col_id' => 'ident',
                'col_text' => ['name','ident'],
                'col_v_text' => 'name',
                'col_where' => ['parent_id'=>'0'],
                //'model_withs' => [],
            ],
        ];
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new DcSetsColumns();
        $dataProvider = $model->search(Yii::$app->request->queryParams,(
            Yii::$app->user->id=='1' ? null : [
                'set_id'=>DcRoleAuthority::model()->getCache('getAuthorityIds', [Yii::$app->user->id,'4']),
            ]
        ),['sets','model.source','forSets']);
        
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
            'treeData' => DcSetsColumns::treeData(),
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
    public function actionCreate($sId='',$mId='',$fId='',$cName='')
    {
        $model = new DcSetsColumns();
        $model->loadDefaultValues();
        $model->setScenario('insertForm');
        
        if($sId) $model->set_id = $sId;
        if($mId) $model->model_id = $mId;
        if($fId) $model->for_set_id = $fId;
        if($cName) $model->name = $cName;

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
            return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    
    /**
     * 批量添加模型
     */
    public function actionBatchCreate($sId='',$mId='',$fId='')
    {
        $model = new DcSetsColumns();
        $model->loadDefaultValues();
        $model->setScenario('batchInsertForm');
        
        if($sId) $model->set_id = $sId;
        if($mId) $model->model_id = $mId;
        if($fId) $model->for_set_id = $fId;
        
        if($model['sets']['set_type']!='model'){
            throw new \yii\web\HttpException(200, Yii::t('datacenter','只有数据集类型为模型的数据集才允许批量添加字段'));
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->name && is_array($model->name)) {
            $transaction = DcSetsColumns::getDb()->beginTransaction(); // 使用事务关联
            
            $fModel = $model['switch_type']==2 ? $model['forSets'] : $model['model'];
            $labels = $fModel ? \yii\helpers\ArrayHelper::map($fModel['columns'], 'name', 'label') : [];
            foreach($model->name as $name){
                $newModel = clone $model;
                $newModel->name = $name;
                $newModel->label = isset($labels[$name]) ? $labels[$name] : $name;
                if($newModel->sql_formula){
                    $newModel->sql_formula = str_replace('{name}',$name,$newModel->sql_formula);
                }
                $newModel->save(false);
            }
            
            $transaction->commit(); // 提交事务
            
            Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
            return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
        }
        
        return $this->render('batch-create', [
            'model' => $model,
        ]);
    }

    /**
     * 修改模型
     */
    public function actionUpdate($id,$sId='',$mId='',$fId='',$cName='')
    {
        $model = $this->findModel($id);
        $model->setScenario('updateForm');
        
        if($sId) $model->set_id = $sId;
        if($mId) $model->model_id = $mId;
        if($fId) $model->for_set_id = $fId;
        if($cName) $model->name = $cName;

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
        if($id && ($models = DcSetsColumns::findAll($id))){
        	$transaction = DcSetsColumns::getDb()->beginTransaction(); // 使用事务关联
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
        if (($model = DcSetsColumns::find()->where(['id'=>$id])->with(['model','model.columns.model.source'])->one()) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
