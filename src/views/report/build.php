<?php
use webadmin\widgets\ActiveForm;
use \yii\helpers\Url;
?>
<div class="row">
	<div class="col-sm-2 col-xs-12 report_box_left">
		<div class="widget flat radius-bordered">
    		<div class="widget-header bg-themeprimary">
    		    <span class="widget-caption">数据集</span>
    		    <div class="widget-buttons">
    				<a href="<?php echo Url::to(['sets/create','type'=>'excel']);?>" target="_blank" title="<?= Yii::t('datacenter','导入EXCEL')?>"><i class="fa fa-file-excel-o"></i></a>
    		    </div>
    		</div>
    		<div class="widget-body well-min-height" style="min-height:500px;">
    			<?= $this->render('_tree', [
        		    'id' => $id,
        		]) ?>
    		</div>
		</div>
	</div>
	<div class="col-sm-10 col-xs-12 report_box_right">
		<div class="widget flat radius-bordered">
    		<div class="widget-header bg-themeprimary">
    		    <span class="widget-caption">构建报表</span>
    	    	<div class="widget-buttons">
    		    	<a href="#" data-toggle="modal" data-target=".bs-nav-modal" title="<?= Yii::t('datacenter','报表选择')?>"><i class="fa fa-bars"></i></a>
		    	</div>
    		</div>
    		<div class="widget-body well-min-height" style="min-height:500px;">
    			<div id="report_div">
                	<?= $this->render('_reports', [
                	    'reportList' => $reportList,
                	]) ?>
                </div>
    		</div>
		</div>
	</div>
</div>

<!-- 保存报表 -->
<?php 
if(Yii::$app->controller->action->id=='update'){
    $rmodel = reset($reportList);
}else{
    $rmodel = new \datacenter\models\DcReport;
}
?>
<div id="saveReportDiv" style="display:none;">
    <div class="row">
        <div class="col-md-12">
        	<?php $form = ActiveForm::begin(); ?>
                <?= $form->field($rmodel, 'title')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($rmodel, 'cat_id')->dropDownList(\datacenter\models\DcCat::authorityTreeOptions(Yii::$app->user->id),[]) // 'prompt'=>'请选择分类'?>
                
                <?= $form->field($rmodel, 'paixu')->textInput(['maxlength' => true]) ?>
   
    		<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?= $this->render('/report-view/_nav', []) ?>