<?php

use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

?>

<div class="row dc-sets-search">
	<div class="col-xs-12">
		<div class="widget margin-bottom-20">
			<div class="widget-body bordered-left bordered-themeprimary">

                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'enableClientScript' => false,
                    'enableClientValidation' => false,
                    'enableAjaxValidation' => false,
                    'validationStateOn' => false,
                    'options' => [
                        'data-pjax' => 1,
                        'class' => 'form-inline'
                    ],
                ]); ?>
                
                <?= $form->field($model, 'cat_id')->searchInput()->select2($model->getV_cat_id(false),['prompt'=>'请选择','multiple'=>'multiple']) ?>

				<?= $form->field($model, 'title')->searchInput() ?>
				
				<?= $form->field($model, 'main_model')->searchInput()->selectajaxmult(\yii\helpers\Url::toRoute('model'),['style'=>'width:200px;']) ?>

				<?= $form->field($model, 'set_type')->searchInput()->select2($model->getV_set_type(false),['prompt'=>'请选择','multiple'=>'multiple']) ?>

				<?= $form->field($model, 'state')->searchInput()->dropDownList($model->getV_state(false),['prompt'=>'请选择']) ?>

				<?= $form->field($model, 'is_index_show')->searchInput()->dropDownList($model->getV_is_index_show(false),['prompt'=>'请选择']) ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('common','查询'), ['class' => 'btn btn-primary', 'id'=>'search_btn']) ?>
                    <?//= Html::submitButton(Yii::t('common','导出'), ['class' => 'btn btn-primary', 'id'=>'export_btn']) ?>
                    <?//= Html::hiddenInput('is_export',''); ?>
                    <?php  
                    //$this->registerJs("$('#search_btn,#export_btn').on('click',function(){
                    //    $(this).closest('form').find('input[name=is_export]').val($(this).attr('id')=='export_btn' ? '1' : '');
                    //});");
                    ?>
                </div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
