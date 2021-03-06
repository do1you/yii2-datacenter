<?php 
use yii\helpers\Html;
use yii\helpers\Url;
use webadmin\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>
	<div class="row">
	
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dbtype')->dropDownList($model->getV_dbtype(false),[]) ?>
    
            <?= $form->field($model, 'dbhost')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dbport')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dbname')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dbuser')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dbpass')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'is_dynamic')->dropDownList($model->getV_is_dynamic(false),[]) // 'prompt'=>'请选择'?>
            
            <?= $form->field($model, 'dctable')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'dcwhere')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'dchost')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dcport')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dcname')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dcuser')->textInput(['maxlength' => true]) ?>
    
            <?= $form->field($model, 'dcpass')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'dcident')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'dcselect')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'dcsession')->textInput(['maxlength' => true]) ?>

            <?php if(Yii::$app->controller->action->id!='view'):?>
                <div class="form-group">
                	<div class="col-sm-offset-2 col-sm-10">
                    	<?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
                    	
                    	<?= Html::button(Yii::t('datacenter', '测试连接'), ['class' => 'btn btn-primary shiny', 'id' => "test_btn"]) ?>
                    </div>
                </div>
            <?php endif;?>
        
		</div>
	</div>
<?php  
$testUrl = Url::to(['test']);
$this->registerJs("
// 选择是否动态库
$('#dcsource-is_dynamic').on('change',function(){
    var is_dynamic = $(this).val();
    $('#dcsource-dctable,#dcsource-dchost,#dcsource-dcport,#dcsource-dcname,#dcsource-dcuser,#dcsource-dcpass,#dcsource-dcwhere,#dcsource-dcident,#dcsource-dcselect,#dcsource-dcsession').parents('.form-group')[is_dynamic=='1' ? 'slideDown' : 'slideUp']();
}).triggerHandler('change');
// 测试连接
$('#test_btn').on('click',function(){
	var params = $(this).closest('form').serializeArray();
	$.post('{$testUrl}',params,function(json){
    	if(json.success){
    		Notify('连接成功！', 'top-right', '5000', 'success', 'fa-check', true);
    	}else{
    		Notify((json.msg || '连接失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
    	}
	},'json');
    return false;
});
");
?>
<?php ActiveForm::end(); ?>