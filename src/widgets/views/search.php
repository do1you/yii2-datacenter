<?php
use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

$searchList = $model->getSearchModels();
$params = Yii::$app->request->get();
$ids = !empty($params['id']) ? explode(',',$params['id']) : '';
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
                        'action' => (count($ids)>1 ? [Yii::$app->controller->action->id] : $params),
                        'method' => 'get',
                        'enableClientScript' => false,
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => false,
                        'validationStateOn' => false,
                        'options' => [
                            //'data-pjax' => 1,
                            'class' => 'form-inline'
                        ],
                    ]);?>
    
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
                    	<?php if(count($ids)>1):?>
                    		<?= Html::button(Yii::t('common','查询'), ['class' => 'btn btn-primary report_search_btn']) ?>
                    	<?php else:?>
                    		<?= Html::submitButton(Yii::t('common','查询'), ['class' => 'btn btn-primary report_search_btn']) ?>
                    	<?php endif;?>
                        <?= Html::submitButton(Yii::t('common','导出'), ['class' => 'btn btn-primary report_export_btn']) ?>
                        <?= Html::hiddenInput('is_export',''); ?>
                        <?php  
                        $apiUrl = $this->context->apiUrl;
                        $this->registerJs("$('.report_search_btn,.report_export_btn').off('click').on('click',function(){
                            var form = $(this).closest('form');
                            form.find('input[name=is_export]').val($(this).is('.report_export_btn') ? '1' : '');
                            if($(this).is('button[type=button].report_search_btn')){ // 异步加载数据
                                var box = $(this).closest('.report-item-box'),
                                    table = box.find('.dataTable[id]').eq(0),
                                    ajax = $(table).dataTable().api().ajax,
                                    apiUrl = box.attr('api-url');
                                if(apiUrl){
                                    ajax.url(apiUrl + (apiUrl.indexOf('?')<0 ? '?' : '&') + form.serialize());
                                    ajax.reload();
                                }
                                return false;
                            }
                        });", 4, 'report.search.submit');
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
                        });$('.dataconter-search').find('.form-group').not('.search_box').hide();", 4, 'report.search.slide');
                        ?>
                    </div>
    
    				<?php ActiveForm::end(); ?>
    			</div>
    		</div>
    	</div>
    </div>
<?php endif;?>