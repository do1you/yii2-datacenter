<?php
use webadmin\widgets\ActiveForm;
?>
<!-- 保存报表字段 -->
<div class="row">
    <div class="col-md-12">
    	<?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
            
            <?php if(!$model->col_id):?>
            	<?= $form->field($model, 'formula')->textInput(['maxlength' => true])->hint('标签做主键计算，示例：{微信}+{支付宝}+{银行卡}+{现金}') ?>
            <?php endif;?>
            
            <?= $form->field($model, 'resp_fun')->textInput(['maxlength' => true])->select2($model->getV_resp_fun(false), ['prompt'=>'请选择']) ?>
            
            <?= $form->field($model, 'paixu')->textInput(['maxlength' => true]) ?>

		<?php ActiveForm::end(); ?>
    </div>
</div>