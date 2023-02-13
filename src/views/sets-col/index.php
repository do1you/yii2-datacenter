<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row dc-sets-columns-index">
	<div class="col-xs-12 col-md-12">
		<?php if(empty($sId)) echo $this->render('_search', ['model' => $model]); ?>
    	<div class="widget flat radius-bordered">
    		<div class="widget-header bg-themeprimary">
    		    <span class="widget-caption">&nbsp;</span>
    		    <div class="widget-buttons">
    				<a href="#" data-toggle="collapse" title="<?= Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
    				<a href="#" data-toggle="maximize" title="<?= Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
    		    </div>
    		</div>
    		<div class="widget-body checkForm">
    			<div class="row">
    				<div class="col-xs-12 col-md-12">
    					<div class="pull-right margin-bottom-10">
    						<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common','树型数据')?></a> */?>
    						<a class="btn btn-primary" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" href="<?php echo Url::to(['sets-col/create', 'sId'=>(empty($sId) ? '' : $sId)]);?>"><i class='fa fa-plus'></i> <?= Yii::t('common','添加')?></a>
    						<a class="btn btn-primary" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" href="<?php echo Url::to(['sets-col/batch-create', 'sId'=>(empty($sId) ? '' : $sId)]);?>"><i class='fa fa-indent'></i> <?= Yii::t('common','批量添加')?></a>
    						<button class="btn btn-primary checkSubmit" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" reaction="<?php echo Url::to(['sets-col/delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= Yii::t('common','批量删除')?></button>
    						<?php echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken());?>
    		    		</div>
    				</div>
    			</div>

            	<?= GridView::widget([
                    'dataProvider' => $dataProvider,
            	    'filterModel' => (empty($sId) ? $model : null),
                    'columns' => [
                    	[
                    	    'class' => '\yii\grid\CheckboxColumn',
                    	    'name' => 'id',
                    	    'header' => '<label><input type="checkbox" name="id_all" class="checkActive select-on-check-all"><span class="text"></span></label>',
                    	    'content' => function($model, $key, $index){
                    	       return '<label><input type="checkbox" name="id[]" class="checkActive" value="'.$key.'"><span class="text"></span></label>';
            	            },
            	        ],
                    	//['class' => '\yii\grid\SerialColumn'],

                	     'id',
                	     [
                	         'attribute' => 'set_id',
                	         'value' => 'sets.v_title',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'switch_source',
                	         'value' => function($model){
                	             return ($model['switch_type']==2 ? $model['forSets']['v_title'] : $model['model']['v_tb_name']);
                	         },
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'name',
                	         'value' => 'name',
                	         'contentOptions' => ['nowrap'=>''],
            	         ],                	     
                	     [
                	         'attribute' => 'label',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::textarea('DcSetsColumns[label]',$model->label,['class'=>'form-control input-sm w100','updateid'=>$model['id']]);
                	         },
                	     ],
                	     [
                	         'attribute' => 'sql_formula',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::textarea('DcSetsColumns[sql_formula]',$model->sql_formula,['class'=>'form-control input-sm w100','updateid'=>$model['id']]);
                	         },
            	         ],
            	         [
            	             'attribute' => 'formula',
            	             'format' => 'raw',
            	             'value' => function ($model) {
            	             return Html::textarea('DcSetsColumns[formula]',$model->formula,['class'=>'form-control input-sm w100','updateid'=>$model['id']]);
                	         },
            	         ],
                	     [
                	         'attribute' => 'is_frozen',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::button($model->v_is_frozen,['name'=>'DcSetsColumns[is_frozen]','class'=>'btn btn-xs shiny'.($model->is_frozen ? ' btn-primary' : ''),'updateid'=>$model['id']]);
                	         },
                	     ],
                	     [
                	         'attribute' => 'is_hide',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::button($model->v_is_hide,['name'=>'DcSetsColumns[is_hide]','class'=>'btn btn-xs shiny'.($model->is_hide ? ' btn-primary' : ''),'updateid'=>$model['id']]);
                	         },
                	     ],
                	     [
                	         'attribute' => 'is_summary',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::button($model->v_is_summary,['name'=>'DcSetsColumns[is_summary]','class'=>'btn btn-xs shiny'.($model->is_summary ? ' btn-primary' : ''),'updateid'=>$model['id']]);
                	         },
                	     ],
                	     [
                	         'attribute' => 'is_search',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::button($model->v_is_search,['name'=>'DcSetsColumns[is_search]','class'=>'btn btn-xs shiny'.($model->is_search ? ' btn-primary' : ''),'updateid'=>$model['id']]);
                	         },
                	     ],
                	     [
                	         'attribute' => 'is_back_search',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::button($model->v_is_back_search,['name'=>'DcSetsColumns[is_back_search]','class'=>'btn btn-xs shiny'.($model->is_back_search ? ' btn-primary' : ''),'updateid'=>$model['id']]);
                	         },
                	     ],
                	     
                	     /*[
                	         'attribute' => 'type',
                	         'value' => 'v_type',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'fun',
                	         'value' => 'v_fun',
                	         'filter' => false,
                	     ],*/
                	     
                	     [
                	         'attribute' => 'paixu',
                	         'format' => 'raw',
                	         'value' => function ($model) {
                	         return Html::textInput('DcSetsColumns[paixu]',$model->paixu,['class'=>'form-control input-sm w50','updateid'=>$model['id']]);
                	         },
                	     ],

                        [
                        	'class' => '\yii\grid\ActionColumn',
                            'buttonOptions' => ['data-pjax'=>(empty($sId) ? '1' : '0')],
                            'urlCreator' => function ($action, $model, $key, $index) {
                                return ['sets-col/'.$action, 'id' => $model->id];
            	            },
                        ],
                    ],
                ]); ?>

		    </div>
	    </div>
	</div>
</div>
<?php 
$url = Url::to(['sets-col/update']);
$this->registerJs("
var fn = function(id, name, value, callFn){
    var params = {'saveOneCol':'1'};
    params[name] = value;
    $.post(('{$url}?id='+id),params,function(json){
    	if(json.success){
    		Notify('操作成功！', 'top-right', '5000', 'success', 'fa-check', true);
            callFn && callFn();
    	}else{
    		Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
    	}
	},'json');
},fn1 = function(){
    $('.grid-view').find('textarea[updateid]').each(function(){
        var t = $(this);
        t.height(t.parent().height()-16);
    });
};
$('.grid-view').on('change','input[updateid],textarea[updateid]',function(){
    var _t = $(this);
    fn(_t.attr('updateid'),_t.attr('name'), _t.val());
}).on('click','button[updateid]',function(){
    var _t = $(this),
        value = _t.hasClass('btn-primary') ? '0' : '1';
    fn(_t.attr('updateid'),_t.attr('name'), value, function(){
        value=='0' ? _t.removeClass('btn-primary').text('否') : _t.addClass('btn-primary').text('是');
    });
});
fn1();
$('a[data-toggle=tab]').on('shown.bs.tab', fn1);
");
?>
<?php Pjax::end(); ?>


