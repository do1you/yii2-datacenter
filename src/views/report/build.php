<?php
use webadmin\widgets\ActiveForm;
?>
<div class="row">
	<div class="col-sm-2 col-xs-12 report_box_left">
		<div class="well with-header well-min-height" style="padding-top:50px;min-height:500px;">
            <div class="header bg-themeprimary">数据集</div>
    		<?= $this->render('_tree', []) ?>
        </div>
	</div>
	<div class="col-sm-10 col-xs-12 report_box_right">
		<div class="well with-header well-min-height" id="report_box" style="padding:50px 12px 12px;min-height:500px;">
            <div class="header bg-themeprimary">构建报表</div>
            <div id="report_div">
            	<?= $this->render('_reports', [
            	    'reportList' => \datacenter\models\DcReport::model()->findModel([
            	        'create_user' => Yii::$app->user->id,
            	        'state' => '9',
            	    ],true),
            	]) ?>
            </div>
        </div>
	</div>
</div>

<!-- 保存报表 -->
<?php $rmodel = new \datacenter\models\DcReport;?>
<div id="saveReportDiv" style="display:none;">
    <div class="row">
        <div class="col-md-12">
        	<?php $form = ActiveForm::begin(); ?>
                <?= $form->field($rmodel, 'title')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($rmodel, 'cat_id')->dropDownList(\datacenter\models\DcCat::treeOptions(),['prompt'=>'请选择分类']) ?>
                
                <?= $form->field($rmodel, 'paixu')->textInput(['maxlength' => true]) ?>
   
    		<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>