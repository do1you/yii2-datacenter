<?php

use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

?>

<div class="row dc-share-search">
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

				<?= $form->field($model, 'report_id')->searchInput()->selectajaxmult(\yii\helpers\Url::toRoute('report'),['style'=>'width:200px;']) ?>
				
				<?= $form->field($model, 'set_id')->searchInput()->selectajaxmult(\yii\helpers\Url::toRoute('sets'),['style'=>'width:200px;']) ?>
				
				<?= $form->field($model, 'share_user')->searchInput()->selectajaxmult(\yii\helpers\Url::toRoute('user'),['style'=>'width:200px;']) ?>

				<?= $form->field($model, 'alias_name')->searchInput() ?>

				<?= $form->field($model, 'hash_key')->searchInput() ?>

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
