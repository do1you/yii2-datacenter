<?php
use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

$searchList = $model->getSearch();
?>
<?php if(!empty($searchList)):?>
    <div class="row dataconter-search">
    	<div class="col-xs-12">
    		<div class="widget margin-bottom-20">
    			<div class="widget-body bordered-left bordered-themeprimary">
                    <?php $form = ActiveForm::begin([
                        // 'action' => ['report','rid'=>Yii::$app->request->get('rid'),],
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
                        <a class="btn btn-primary search_box" href="#"><i class='fa fa-angle-double-up'></i> <?= Yii::t('datacenter','收起过滤')?></a>
                        <?= Html::hiddenInput('is_export',''); ?>
                        <?php  
                        $this->registerJs("$('#search_btn,#export_btn').on('click',function(){
                            $(this).closest('form').find('input[name=is_export]').val($(this).attr('id')=='export_btn' ? '1' : '');
                        });");
                        ?>
                    </div>
    
    				<?php ActiveForm::end(); ?>
    			</div>
    		</div>
    	</div>
    </div>
<?php endif;?>