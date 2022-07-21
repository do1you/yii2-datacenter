<?php
use webadmin\widgets\ActiveForm;
?>
<!-- 保存报表字段 -->
<div class="row">
    <div class="col-md-12">
    	<?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
            
            <?php if(!$model->col_id):?>
            	<?= $form->field($model, 'formula')->textInput(['maxlength' => true])->hint('采用{列名称}进行计算，示例：<span class="orange">{微信}+{支付宝}+{银行卡}+{现金}</span>
                <br>支持使用条件判断，示例：<span class="orange">{业绩}>10000 ? 500 : 100</span>
                <br>同时判断多个条件，示例：<span class="orange">{业绩}>10000 && {业绩}<=30000 ? 500 : 100</span>
                <br>只要有一个条件满足，示例：<span class="orange">{业绩}>10000 || {桌数}>=3 ? 500 : 100</span>
                <br>多条件判断（阶梯条件）使用嵌套，示例：<span class="orange">{业绩}>100000 ? 500 : ({业绩}>50000 ? 300 : ({业绩}>30000 ? 200 : 100))</span>
                ') ?>
            <?php endif;?>
            
            <?= $form->field($model, 'resp_fun')->textInput(['maxlength' => true])->select2($model->getV_resp_fun(false), ['prompt'=>'请选择']) ?>
            
            <?= $form->field($model, 'paixu')->textInput(['maxlength' => true]) ?>

		<?php ActiveForm::end(); ?>
    </div>
</div>