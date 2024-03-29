<?php
use yii\helpers\Html;
use yii\helpers\Url;
use webadmin\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>
	<div class="row dc-sets-form">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'state')->textInput()->dropDownList($model->getV_state(false), []) ?>
            
            <?= $form->field($model, 'is_index_show')->textInput()->dropDownList($model->getV_is_index_show(false), []) ?>
            
            <?= $form->field($model, 'cat_id')->textInput()->select2($model->getV_cat_id(false), []) ?>

            <?= $form->field($model, 'set_type')->textInput(['maxlength' => true])->dropDownList($model->getV_set_type(false), (
                (Yii::$app->request->get('type') || Yii::$app->controller->action->id!='create') 
                ? ['readonly'=>'readonly', 'disabled'=>'disabled'] 
                : [])) ?>

            <?= $form->field($model, 'main_model',['options'=>['class'=>'form-group box_form box_model']])->textInput()->selectajax(\yii\helpers\Url::toRoute('model'),[]) ?>

            <?= $form->field($model, 'rel_where',['options'=>['class'=>'form-group box_form box_model']])->textarea(['maxlength' => true])
            ->hint('支持的动态标签示例(下同)：{字段标签}=输入的查询条件[范围条件多了_0和_1字段]; {模型标签}=对应的数据模型表; 表名=对应的数据模型表')?>

            <?= $form->field($model, 'rel_group',['options'=>['class'=>'form-group box_form box_model']])->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'rel_having',['options'=>['class'=>'form-group box_form box_model']])->textarea(['maxlength' => true]) ?>

            <?= $form->field($model, 'rel_order',['options'=>['class'=>'form-group box_form box_model']])->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'run_script',['options'=>['class'=>'form-group box_form box_script']])->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'source_id',['options'=>['class'=>'form-group box_form box_sql']])->textInput()->selectajax(\yii\helpers\Url::toRoute('source'),[]) ?>

            <?= $form->field($model, 'run_sql',['options'=>['class'=>'form-group box_form box_sql']])->textarea(['rows' => 6]) ?>

            <?= $form->field($model, 'excel_file',['options'=>['class'=>'form-group box_form box_excel']])->textInput(['maxlength' => true])->oneFile(".xls,.xlsx") ?>

			<?php if(Yii::$app->controller->action->id!='create'):?>
            	<?= $form->field($model, 'update_time')->textInput(['disable'=>'disable','readonly'=>'readonly']) ?>
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

<?php
$this->registerJs("
$('#dcsets-set_type').on('change',function(){
    var value = $(this).val();
    $('div.box_form').slideUp();
    $('div.box_' + value).slideDown();
}).triggerHandler('change');
");
?>