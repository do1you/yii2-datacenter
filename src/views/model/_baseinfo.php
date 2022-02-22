<?php 
use yii\helpers\Html;
use yii\helpers\Url;
use webadmin\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>
	<div class="row dc-model-form">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'cat_id')->textInput()->select2($model->getV_cat_id(false), []) ?>

            <?= $form->field($model, 'source_db')->textInput()->selectajax(\yii\helpers\Url::toRoute('source'),[]) ?>
            
            <?= $form->field($model, 'tb_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'tb_label')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'is_visible')->textInput()->dropDownList($model->getV_is_visible(false), []) ?>
            
            <?= $form->field($model, 'paixu')->textInput() ?>

			<?php if(Yii::$app->controller->action->id!='create'):?>
            	<?= $form->field($model, 'update_time')->textInput(['disabled'=>'disabled','readonly'=>'readonly']) ?>
            <?php endif;?>


            <?php if(Yii::$app->controller->action->id!='view'):?>
                <div class="form-group">
                	<div class="col-sm-offset-2 col-sm-10">
                    	<?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
                    </div>
                </div>
            <?php endif;?>
        
		</div>
	
	</div>
<?php ActiveForm::end(); ?>