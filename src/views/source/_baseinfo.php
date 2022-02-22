<?php 
use yii\helpers\Html;
use yii\helpers\Url;
use webadmin\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>
	<div class="row">
	
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dbtype')->textInput(['maxlength' => true])->dropDownList($model->getV_dbtype(false), []) ?>

            <?= $form->field($model, 'dbhost')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dbport')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dbname')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dbuser')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dbpass')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'is_dynamic')->textInput()->dropDownList($model->getV_is_dynamic(false), []) ?>

            <?= $form->field($model, 'dchost')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dcport')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dcname')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dcuser')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dcpass')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dctable')->textInput(['maxlength' => true]) ?>


            <?php if(Yii::$app->controller->action->id!='view'):?>
                <div class="form-group">
                	<div class="col-sm-offset-2 col-sm-10">
                    	<?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
                    </div>
                </div>
            <?php endif;?>
        
		</div>
	</div>
<?php
$this->registerJs("
$('#dcsource-is_dynamic').on('change',function(){
    var el = $('#dcsource-dchost,#dcsource-dcport,#dcsource-dcname,#dcsource-dcuser,#dcsource-dcpass,#dcsource-dctable').closest('.form-group');
	if($(this).val()=='1'){
		el.slideDown();
	}else{
		el.slideUp();
	}	
}).triggerHandler('change');
");
?>
<?php ActiveForm::end(); ?>