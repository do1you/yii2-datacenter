<?php
use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

$searchList = $model->getSearchModels();
$params = Yii::$app->request->get();
$params[0] = Yii::$app->controller->action->id;
$pagination = $model['pagination'];
unset($params['SysConfig'],$params['is_export'],$params['_'],$params['t'],$params['sign']);
if($pagination){
    unset($params[$pagination->pageSizeParam],$params[$pagination->pageParam]);
}
?>
<?php if(!empty($searchList)):?>
    <div class="row dataconter-search">
    	<div class="col-xs-12">
    		<div class="widget margin-bottom-20">
    			<div class="widget-body bordered-left bordered-themeprimary">
                    <?php $form = ActiveForm::begin([
                        'action' => $params,
                        'method' => 'get',
                        'enableClientScript' => false,
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => false,
                        'validationStateOn' => false,
                        'options' => [
                            //'data-pjax' => 1,
                            'class' => 'form-inline'
                        ],
                    ]); ?>
    
    				<?php foreach($searchList as $k=>$item):?>
                    	<?php 
                    	echo $this->render('@webadmin/modules/config/views/sys-config/_config',[
                    	    'item' => $item,
                    	    'k' => $item['attribute'],
                    	    'form' => $form->searchInput(),
                    	]);
                    	?>
                    <?php endforeach;?>
    
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('common','查询'), ['class' => 'btn btn-primary', 'id'=>'search_btn']) ?>
                        <?= Html::submitButton(Yii::t('common','导出'), ['class' => 'btn btn-primary', 'id'=>'export_btn']) ?>
                        <?= Html::hiddenInput('is_export',''); ?>
                        <?php  
                        $this->registerJs("$('#search_btn,#export_btn').on('click',function(){
                            $(this).closest('form').find('input[name=is_export]').val($(this).attr('id')=='export_btn' ? '1' : '');
                        });");
                        ?>
                    </div>
                    
                    <!-- 收缩/展开 -->
                    <div class="form-group search_box">
                        <a class="btn btn-primary search_box_btn" href="#" style="display:none;"><i class='fa fa-angle-double-up'></i> <?= Yii::t('datacenter','收起过滤')?></a>
                        <a class="btn btn-primary search_box_btn" href="#"><i class='fa fa-angle-double-down'></i> <?= Yii::t('datacenter','展开过滤')?></a>
                        <?php  
                        $this->registerJs("$('.search_box_btn').off('click').on('click',function(){
                            $(this).closest('.dataconter-search').find('.form-group').not('.search_box').toggle();
                            $(this).closest('.search_box').find('.search_box_btn').show();
                            $(this).hide();
                        });$('.dataconter-search').find('.form-group').not('.search_box').hide();");
                        ?>
                    </div>
    
    				<?php ActiveForm::end(); ?>
    			</div>
    		</div>
    	</div>
    </div>
<?php endif;?>