<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

$aModel = new \datacenter\models\DcRoleAuthority();
$list = \datacenter\models\DcRoleAuthority::find()->where(['role_id'=>$model['id']])->all();
$list = \yii\helpers\ArrayHelper::map($list, 'source_id', 'source_id', 'source_type');
foreach($list as $type=>$value){
    $param = $aModel->parameterAuthority[$type];
    if($param){
        if($type=='3'){
            if(is_array($value)){
                foreach($value as $val){
                    list($dbId,$dynamicId) = explode('_', $val);
                    if($dbId && $dynamicId){
                        if(empty($aModel->dynamicSourceList)) $aModel->dynamicSourceList = [];
                        if(empty($aModel->dynamicSourceList[$dbId])) $aModel->dynamicSourceList[$dbId] = [];
                        $aModel->dynamicSourceList[$dbId][$dynamicId] = $dynamicId;
                    }
                }
            }
        }else{
            $aModel->$param = $value;
        }        
    }
}
?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<a class="btn btn-primary" href="<?php echo Url::to(['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','列表')?></a>
			<?php if(Yii::$app->controller->action->id=='view'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['update','id'=>$model->primaryKey])?>"><i class="ace-icon fa fa-edit bigger-110"></i> <?php echo Yii::t('common','编辑')?></a>
			<?php endif;?>

		</div>
	</div>
</div>

<div class="form-title"></div>

<?php $form = ActiveForm::begin(); ?>
	<!-- widget start -->
	<div class="widget flat radius-bordered">
		<div class="widget-header bg-themeprimary">
		    <span class="widget-caption"><?php echo $model['name']?>权限范围配置</span>
		</div>
		<div class="widget-body">
		    <div class="widget-main ">
		    	<!-- tab start -->
		    	<div class="tabbable">
    			    <ul class="nav nav-tabs tabs-flat">
    		    		<li class="active"><a data-toggle="tab" href="#tab_source" aria-expanded="true">数据源</a></li>
    		    		<li><a data-toggle="tab" href="#tab_dynamic" aria-expanded="false">动态数据源</a></li>
    		    		<li><a data-toggle="tab" href="#tab_sets" aria-expanded="false">数据集</a></li>
    		    		<li><a data-toggle="tab" href="#tab_report" aria-expanded="false">数据报表</a></li>
    			    </ul>
    			    <div class="tab-content tabs-flat">
    		    		<div id="tab_source" class="tab-pane active">
    		    			<?= $this->render('_source', [
    		    			    'model' => $model,
    		    			    'aModel' => $aModel,
    		    			    'form' => $form,
                            ]) ?>
    		    		</div>
    		    		<div id="tab_dynamic" class="tab-pane">
    		    			<?= $this->render('_dynamic', [
    		    			    'model' => $model,
    		    			    'aModel' => $aModel,
    		    			    'form' => $form,
                            ]) ?>
    		    		</div>
    		    		<div id="tab_sets" class="tab-pane">
    		    			<?= $this->render('_sets', [
    		    			    'model' => $model,
    		    			    'aModel' => $aModel,
    		    			    'form' => $form,
                            ]) ?>
    		    		</div>
    		    		<div id="tab_report" class="tab-pane">
    		    			<?= $this->render('_report', [
    		    			    'model' => $model,
    		    			    'aModel' => $aModel,
    		    			    'form' => $form,
                            ]) ?>
    		    		</div>
    		    	</div>
    		    </div>
    		    <!-- tab end -->
                <?php if(Yii::$app->controller->action->id!='view'):?>
                    <div class="form-group">
                    	<div class="col-sm-12 text-align-center">
                        	<?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
                        </div>
                    </div>
                <?php endif;?>
        	</div>
        </div>    
    </div> 
    <!-- widget end -->

<?php ActiveForm::end(); ?>

<?php Pjax::end(); ?>

