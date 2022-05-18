<?php

use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

?>

<div class="row dc-sets-columns-search">
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

				<?= $form->field($model, 'set_id')->searchInput()->selectajax(\yii\helpers\Url::toRoute('sets'),['style'=>'width:200px;']) ?>

				<?= $form->field($model, 'model_id')->searchInput()->selectajax(\yii\helpers\Url::toRoute('model'),['style'=>'width:200px;']) ?>
				
				<?= $form->field($model, 'for_set_id')->searchInput()->selectajax(\yii\helpers\Url::toRoute('sets'),['style'=>'width:200px;']) ?>

				<?= $form->field($model, 'name')->searchInput() ?>

				<?= $form->field($model, 'label')->searchInput() ?>

				<?= $form->field($model, 'is_search')->searchInput()->dropDownList($model->getV_is_search(false),['prompt'=>'请选择']) ?>
				
				<?= $form->field($model, 'is_back_search')->searchInput()->dropDownList($model->getV_is_back_search(false),['prompt'=>'请选择']) ?>

				<?= $form->field($model, 'is_summary')->searchInput()->dropDownList($model->getV_is_summary(false),['prompt'=>'请选择']) ?>

				<?= $form->field($model, 'type')->searchInput()->dropDownList($model->getV_type(false),['prompt'=>'请选择']) ?>

				<?= $form->field($model, 'fun')->searchInput()->dropDownList($model->getV_fun(false),['prompt'=>'请选择']) ?>

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
