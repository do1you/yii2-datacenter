<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

$source_col = array_merge([''=>''],$model['v_source_col']);

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

<div class="row dc-relation-form">
	<?php $form = ActiveForm::begin(); ?>
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'source_model')->textInput()->selectajax(\yii\helpers\Url::toRoute('model'),[])  ?>

            <?= $form->field($model, 'target_model')->textInput()->selectajax(\yii\helpers\Url::toRoute('model'),[])  ?>
            
            <?= $form->field($model, 'rel_type')->textInput()->select2($model->getV_rel_type(false), []) ?>
            
            <?= $form->field($model, 'rel_where')->textInput(['maxlength' => true]) ?>
            
            <div class="form-group required">
                <label class="col-sm-2 control-label no-padding-right" for="dcrelation-source_col">关联关系</label>
                <div class="col-sm-7">
                	<div class="row">
                		<div class="col-sm-12">
                			<?php foreach($source_col as $key=>$val):?>
                				<?php 
                				    if($val){
                				        $model->source_col = $key;
                				        $model->target_col = $val;
                				    }
                				?>
                            	<div <?php echo ($val=='' ? 'class="input-group add_relation_el" style="display:none"' : 'class="input-group relation_el relation_val"')?>>
                                    <?= $form->field($model, 'source_col', ['template'=>"{input}\n{error}",'options'=>['class'=>'form-control no-padding','style'=>'border:0px;']])
                                    ->dropDownList(($val=='' ? [] : [$key=>$key]),['name'=>'DcRelation[source_col][]']) ?>
                                    <span class="input-group-addon" style="width:auto;">
                                        <i class="fa fa-arrows-h"></i>
                                    </span>
                                    <?= $form->field($model, 'target_col', ['template'=>"{input}",'options'=>['class'=>'form-control no-padding','style'=>'border:0px;']])
                                    ->dropDownList(($val=='' ? [] : [$val=>$val]),['name'=>'DcRelation[target_col][]']) ?>
                                </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                	<?= Html::buttonInput(Yii::t('datacenter','添加'), ['class' => 'btn btn-primary', 'id'=>'add_relation_btn']) ?>
                	<?= Html::buttonInput(Yii::t('datacenter','移除'), ['class' => 'btn btn-primary', 'id'=>'remove_relation_btn']) ?>
                </div>
            </div>
            
            <?php $model->is_reverse_save = '1';?>
            <?= $form->field($model, 'is_reverse_save')->textInput(['maxlength' => true])->switchs() ?>
            
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

<style>
.relation_el>div.form-control{width:200px;}
</style>
<?php
$url = \yii\helpers\Url::toRoute('column');
$addScript = count($source_col)>1 ? "$('#dcrelation-source_model').triggerHandler('change');$('#dcrelation-target_model').triggerHandler('change');" : "$('#add_relation_btn').triggerHandler('click');";
$this->registerJs("
// 移除匹配关系
$('#remove_relation_btn').on('click',function(){
    if($('.relation_el').length<=1){
        Notify('至少需要保留一个关联关系！', 'top-right', '5000', 'danger', 'fa-bolt', true);
    }else{
        $('.relation_el').eq(-1).remove();
    }
});
// 添加匹配关系
$('#add_relation_btn').on('click',function(){
    var el = $('div.add_relation_el').eq(0),
        cel = el.clone().removeClass('add_relation_el').addClass('relation_el').show();
    el.parent().append(cel);
    $('#dcrelation-source_model').triggerHandler('change');
    $('#dcrelation-target_model').triggerHandler('change');
});
// 选择源模型和目标模型
$('#dcrelation-source_model,#dcrelation-target_model').on('change',function(e){
    var mId = ($(this).val() || '-999'),
        elname = ($(this).attr('id') == 'dcrelation-source_model' ? 'DcRelation[source_col][]' : 'DcRelation[target_col][]'),
        el = $('.relation_el select[name=\"'+ elname +'\"]');
    e.isTrigger && (e.isTrigger=='3') && el.val('');
    el.select2({
         ajax: {
             type:'GET',
             url: '{$url}',
             dataType: 'json',
             delay: 250,
             data: function (params) {
                 return {q: params.term,page: params.page, mId: mId};
             },
             processResults: function (data, params) {
                 params.page = params.page || 1;
                 return {
                     results: data.items,
                     pagination: {
                         more: params.page < data.total_page
                     }
                 };
             },
             cache: true
         },
         placeholder:'请选择',
         language: 'zh-CN',
         tags: false,
         allowClear: true,
         escapeMarkup: function (m){ return m; },
         minimumInputLength: 0,
         formatResult: function formatRepo(r){return r.text;},
         formatSelection: function formatRepoSelection(r){return r.text;}
    });
});

{$addScript}
");
?>
<?php Pjax::end(); ?>

