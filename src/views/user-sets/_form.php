<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common','树型数据')?></a> */?>
			<a class="btn btn-primary" href="<?php echo Url::to(['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','列表')?></a>
			<?php if(Yii::$app->controller->action->id!='create'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['create'])?>"><i class="ace-icon fa fa-plus bigger-110"></i> <?php echo Yii::t('common','添加')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['update','id'=>$model->primaryKey])?>"><i class="ace-icon fa fa-edit bigger-110"></i> <?php echo Yii::t('common','编辑')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view' || Yii::$app->controller->action->id=='update'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['delete','id'=>$model->primaryKey])?>" data-pjax="0"><i class="ace-icon fa fa-trash-o bigger-110"></i> <?php echo Yii::t('common','删除')?></a>
			<?php endif;?>
		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row dc-user-sets-form">
	<?php $form = ActiveForm::begin(); ?>
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'user_id')->textInput()->selectajax(\yii\helpers\Url::toRoute('user'),[]) ?>
            
            <?= $form->field($model, 'set_id')->textInput()->selectajax(\yii\helpers\Url::toRoute('sets'),[]) ?>

            <?= $form->field($model, 'alias_name')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'paixu')->textInput() ?>
            
			<?php if(Yii::$app->controller->action->id!='create'):?>
				<?= $form->field($model, 'grant_user')->textInput()->selectajax(\yii\helpers\Url::toRoute('user'),['disabled'=>'disabled','readonly'=>'readonly']) ?>
				
            	<?= $form->field($model, 'create_time')->textInput(['disabled'=>'disabled','readonly'=>'readonly']) ?>
            <?php endif;?>
            
            <?= $form->field($model, 'search_values')->textarea(['rows'=>20]) ?>

            <?php if(Yii::$app->controller->action->id!='view'):?>
                <div class="form-group">
                	<div class="col-sm-offset-2 col-sm-10">
                    	<?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
                    </div>
                </div>
            <?php endif;?>
        
		</div>
	<?php ActiveForm::end(); ?>
</div>
<?php Pjax::end(); ?>

