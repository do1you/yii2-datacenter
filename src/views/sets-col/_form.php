<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common','树型数据')?></a> */?>
			<a class="btn btn-primary" href="<?php echo Url::to(['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','列表')?></a>
			<?php if(Yii::$app->controller->action->id!='create'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['create'])?>"><i class="ace-icon fa fa-plus bigger-110"></i> <?php echo Yii::t('common','添加')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['update','id'=>$model->primaryKey])?>"><i class="ace-icon fa fa-edit bigger-110"></i> <?php echo Yii::t('common','编辑')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view' || Yii::$app->controller->action->id=='update'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['delete','id'=>$model->primaryKey])?>" data-pjax="0"><i class="ace-icon fa fa-trash-o bigger-110"></i> <?php echo Yii::t('common','删除')?></a>
			<?php endif;?>
		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row dc-sets-columns-form">
	<?php $form = ActiveForm::begin(); ?>
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'set_id')->textInput()->selectajax(\yii\helpers\Url::toRoute('sets'),[]) ?>

			<?php if($model['sets']['set_type']=='model'):?>
            	<?= $form->field($model, 'model_id',['options'=>['class'=>'form-group box_form box_model']])->textInput()->selectajax(\yii\helpers\Url::toRoute('model'),[]) ?>
  			<?php endif;?>
            
        	<?php if($model['sets']['set_type']=='model' && $model['model']):?>
        		<?php 
        		$list = \yii\helpers\ArrayHelper::map($model['model']['columns'], 'name', 'v_name');
        		$labels = \yii\helpers\ArrayHelper::map($model['model']['columns'], 'name', 'label');
        		$model->label = $model->label ? $model->label : $labels[$model['name']];
        		?>
        		
        		<?= $form->field($model, 'name')->textInput(['maxlength' => true])->select2($list, ['prompt'=>'默认']) ?>
        		
        		<?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
        		
        		<?= $form->field($model, 'fun')->textInput(['maxlength' => true])->dropDownList($model->getV_fun(false), ['prompt'=>'请选择']) ?>
        		
        		<?= $form->field($model, 'sql_formula')->textInput(['maxlength' => true]) ?>
    		<?php elseif($model['sets']['set_type']=='sql'):?>
    			<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        		
        		<?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
        	<?php else:?>
        		<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        		
        		<?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
        	<?php endif;?>
            
            <?= $form->field($model, 'formula')->textInput(['maxlength' => true])->hint('通过字段结果公式计算，以标签做主键计算，例：{微信}+{支付宝}+{银行卡}+{现金}') ?>
            
            <?= $form->field($model, 'is_search')->textInput()->dropDownList($model->getV_is_search(false), []) ?>
            
            <?= $form->field($model, 'is_frozen')->textInput()->dropDownList($model->getV_is_frozen(false), []) ?>
            
            <?= $form->field($model, 'paixu')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'type')->textInput(['maxlength' => true])->dropDownList($model->getV_type(false), ['prompt'=>'请选择']) ?>
            
            <?= $form->field($model, 'search_value')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'search_params')->textarea(['rows' => 6]) ?>
            
            <?php if(Yii::$app->controller->action->id!='view'):?>
                <div class="form-group">
                	<div class="col-sm-offset-2 col-sm-10">
                    	<?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
                    </div>
                </div>
            <?php endif;?>
        
		</div>
	<?php ActiveForm::end(); ?>
</div>

<?php
$url = Url::to([Yii::$app->controller->action->id,'id'=>$model['id'],'mId'=>$model['model_id']]);
$url1 = Url::to([Yii::$app->controller->action->id,'id'=>$model['id'],'sId'=>$model['set_id']]);
$url2 = Url::to([Yii::$app->controller->action->id,'id'=>$model['id'],'sId'=>$model['set_id'],'mId'=>$model['model_id']]);
$this->registerJs("
// 选择数据集
$('#dcsetscolumns-set_id').on('change',function(){
    var value = $(this).val();
    location.href = '{$url}&sId=' + (value || '');
});
// 选择数据模型
$('#dcsetscolumns-model_id').on('change',function(){
    var value = $(this).val();
    location.href = '{$url1}&mId=' + (value || '');
});
// 选择字段
$('#dcsetscolumns-name').on('change',function(){
    if($(this).is('select')){
        var value = $(this).val();
        location.href = '{$url2}&cName=' + (value || '');
    }
});
// 选择是否可查
$('#dcsetscolumns-is_search').on('change',function(){
    var value = $(this).val();
    $('#dcsetscolumns-type,#dcsetscolumns-search_params').closest('.form-group')[value=='1' ? 'slideDown' : 'slideUp']();
}).triggerHandler('change');
");
?>
<?php Pjax::end(); ?>

