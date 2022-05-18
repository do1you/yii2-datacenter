<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

$model->afterFind();
Yii::$app->controller->currNav[] = Yii::t('common','批量添加');

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<a class="btn btn-primary" href="<?php echo Url::to(['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','列表')?></a>
		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row dc-sets-columns-form">
	<?php $form = ActiveForm::begin(); ?>
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            
            <?= $form->field($model, 'set_id')->textInput()->selectajax(\yii\helpers\Url::toRoute('sets'),[]) ?>

			<?php if($model['sets']['set_type']=='model'):?>
				<?= $form->field($model, 'switch_type')->textInput()->dropDownList($model->getV_switch_type(false), []) ?>
				
            	<?= $form->field($model, 'model_id',['options'=>['class'=>'form-group box_form box_model']])->textInput()->selectajax(\yii\helpers\Url::toRoute('model'),[]) ?>
            	
            	<?= $form->field($model, 'for_set_id',['options'=>['class'=>'form-group box_form box_sets']])->textInput()->selectajax(\yii\helpers\Url::toRoute(['forsets','fId'=>$model['set_id']]),[]) ?>
  			<?php endif;?>
            
        	<?php if(($fModel = $model['switch_type']==2 ? $model['forSets'] : $model['model'])):?>
        		<?php 
        		$list = \yii\helpers\ArrayHelper::map($fModel['columns'], 'id', 'v_name');
        		$labels = \yii\helpers\ArrayHelper::map($fModel['columns'], 'id', 'label');
        		$model->label = $model->label ? $model->label : $labels[$model['column_id']];
        		?>
        		
        		<?= $form->field($model, 'column_id')->textInput(['maxlength' => true])->duallistbox($list, []) ?>
        		
        		<?= $form->field($model, 'fun')->textInput(['maxlength' => true])->dropDownList($model->getV_fun(false), ['prompt'=>'请选择']) ?>
        		
        		<?= $form->field($model, 'sql_formula')->textInput(['maxlength' => true])->hint('使用各字段对应的名称标签：{name}，例：table.{name}/100') ?>
        	<?php endif;?>
            
            <?= $form->field($model, 'formula')->textInput(['maxlength' => true])->hint('通过字段结果公式计算，以标签做主键计算，例：{微信}+{支付宝}+{银行卡}+{现金}') ?>
            
            <?= $form->field($model, 'paixu')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'is_frozen')->textInput()->dropDownList($model->getV_is_frozen(false), []) ?>
            
            <?= $form->field($model, 'is_search')->textInput()->dropDownList($model->getV_is_search(false), []) ?>
            
            <?php if(in_array($model['sets']['set_type'], ['model','sql'])):?>
            	<?= $form->field($model, 'is_back_search')->textInput()->dropDownList($model->getV_is_back_search(false), []) ?>
            <?php endif;?>
            
            <?= $form->field($model, 'type')->textInput(['maxlength' => true])->dropDownList($model->getV_type(false), ['prompt'=>'请选择']) ?>
            
            <?= $form->field($model, 'search_value')->textInput(['maxlength' => true])->dropDownList($model->getV_search_value(false), ['prompt'=>'请选择']) ?>
            
            <?= $form->field($model, 'search_value_text')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'search_params')->textarea(['rows' => 6])->hint('每行一个选项，值和名称用“|”分隔，示例：value|name') ?>
            
            <?= $form->field($model, 'search_params_text')->textInput(['maxlength' => true])
                ->hint('格式化文本：正则表达式（9:[0-9], a:[A-Za-z], w:[A-Za-z0-9], *:.）；<br>异步下拉：表名.取值字段.显示字段（table.key.text）')?>
            
            <?= $form->field($model, 'search_params_dd')->textInput()->selectajax(\yii\helpers\Url::toRoute('dd'), []) ?>
            
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
$url = Url::to([Yii::$app->controller->action->id,'id'=>$model['id'],'mId'=>$model['model_id'],'fId'=>$model['for_set_id']]);
$url1 = Url::to([Yii::$app->controller->action->id,'id'=>$model['id'],'sId'=>$model['set_id']]);
$url2 = Url::to([Yii::$app->controller->action->id,'id'=>$model['id'],'sId'=>$model['set_id'],'mId'=>$model['model_id'],'fId'=>$model['for_set_id']]);
$this->registerJs("
// 选择数据集
$('#dcsetscolumns-set_id').on('change',function(){
    var value = $(this).val();
    location.href = '{$url}&sId=' + (value || '');
});
// 选择归属类型
$('#dcsetscolumns-switch_type').on('change',function(){
    var value = $(this).val();
    $('.box_sets,.box_model').slideUp();
    $(value == 1 ? '.box_model' : '.box_sets').slideDown();
}).triggerHandler('change');
// 选择数据模型
$('#dcsetscolumns-model_id').on('change',function(){
    var value = $(this).val();
    location.href = '{$url1}&mId=' + (value || '');
});
// 选择归属数据集
$('#dcsetscolumns-for_set_id').on('change',function(){
    var value = $(this).val();
    location.href = '{$url1}&fId=' + (value || '');
});
// 选择是否可查
$('#dcsetscolumns-is_search').on('change',function(){
    $('#dcsetscolumns-type').triggerHandler('change');
}).triggerHandler('change');
// 选择查询类型
$('#dcsetscolumns-type').on('change',function(){
    var isSearch = $('#dcsetscolumns-is_search').val(),
        value = $(this).val(),
        fn = function(id,isShow){ $(id).closest('.form-group')[isShow ? 'slideDown' : 'slideUp'](); };
    fn('#dcsetscolumns-search_params,#dcsetscolumns-search_params_text,#dcsetscolumns-search_params_dd,#dcsetscolumns-search_value,#dcsetscolumns-search_value_text');
    if(isSearch=='1'){
        fn(this,1);
        if(value=='dd' || value=='ddmulti' || value=='ddselect2' || value=='ddselect2multi'){
            // 字典选项
            fn('#dcsetscolumns-search_value_text,#dcsetscolumns-search_params_dd',1);
        }else if(value=='datetimerange' || value=='daterange' || value=='datetime' || value=='date'){
            // 时间选项
            fn('#dcsetscolumns-search_value',1);
        }else if(value=='select' || value=='select2' || value=='select2mult' || value=='selectmult'){
            // 下拉选项
            fn('#dcsetscolumns-search_value_text,#dcsetscolumns-search_params',1);
        }else{
            // 其他
            fn('#dcsetscolumns-search_value_text',1);
            if(value=='mask' || value=='selectajax' || value=='selectajaxmult'){
                fn('#dcsetscolumns-search_params_text',1);
            }
        }
    }else{
        fn(this);
    }
    

}).triggerHandler('change');
");
?>
<?php Pjax::end(); ?>

