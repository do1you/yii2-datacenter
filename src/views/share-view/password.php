<?php
use yii\helpers\Html;
use webadmin\widgets\ActiveForm;
$model->forUserModel['password'] = '';
?>
<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="well with-header with-footer">
            <div class="header bg-themeprimary"><?php echo $model['v_report_title']?></div>
            <div id="dropdownbuttons">
                <div class="row">
                	<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
                        <?php $form = ActiveForm::begin([
                            'action' => ['view','h'=>$model->forUserModel['hash_key']],
                        ]); ?>
                    
                        <?= $form->field($model->forUserModel, 'password')->passwordInput(['maxlength' => true, 'name' => 'password']) ?>
                        
                        <div class="form-group">
                        	<div class="col-sm-offset-2 col-sm-10">
                            	<?= Html::submitButton(Yii::t('authority', ' 提交 '), ['class' => 'btn btn-primary shiny']) ?>
                            </div>
                        </div>
                    
                        <?php ActiveForm::end(); ?>
                	</div>
                </div>
            </div>
            <div class="footer"><code>请输入密码以访问数据报表-_- </code></div>
        </div>
    </div>
</div>

