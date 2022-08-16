<?php
use yii\helpers\Html;
use webadmin\widgets\ActiveForm;
use datacenter\models\DcShare;

$searchList = $model->getSearchModels();
$count = !$this->context->isCache ? 2 : ($this->context->reportList ? count($this->context->reportList) : 0);
$url = $model['forUserModel'] 
    ? (
        ($model['forUserModel'] instanceof DcShare) 
        ? [Yii::$app->controller->action->id,'h'=>$model['forUserModel']['hash_key']]
        : [Yii::$app->controller->action->id,'id'=>$model['id'],'vid'=>$model['forUserModel']['id']]
      )
    : [Yii::$app->controller->action->id,'id'=>$model['id']];
?>
<?php if(!empty($searchList)):?>
    <div class="row dataconter-search tooltip-primary">
    	<div class="col-xs-12">
    		<div class="widget margin-bottom-20 tooltip-primary" data-toggle="tooltip" data-placement="left" data-original-title="<p class='text-left'>查询帮助：<br>1. 带有“剔除”字眼的条件表示剔除符合这个条件的数据<br>2. 输入框以“>、>=、<、<=、=、!=、<>、~=”开头的查询，表示运算后面的数据的条件，如：>100<br>3. “=、!=、<>、~=”的运算支持使用逗号或Tab符号进行多条件匹配，如：=张三,李四,王五<br>4. 下拉搜索框支持多条件匹配，如：张三,李四,王五<br>5. 日期过滤中带有“至”的表示进行日期范围查询<br>6. 时分条件查询，将24小时制的小时数和分钟数拼接用数字表示，时间范围在0点至9点时进行相加2400，如：2200~2600</p>">
    			<div class="widget-body bordered-left bordered-themeprimary">
                    <?php $form = ActiveForm::begin([
                        'action' => $url,
                        'method' => 'get',
                        'enableClientScript' => false,
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => false,
                        'validationStateOn' => false,
                        'options' => [
                            //'data-pjax' => 1,
                            'class' => 'form-inline'
                        ],
                    ])->searchInput();
                    $groupList = \yii\helpers\ArrayHelper::map($searchList, 'group_name', 'group_name');
                    unset($groupList['']);
                    ?>
                    <?php if($groupList): // 分组条件?>
                        <div class="more_search_box">
                            <ul class="pagination">
                            	<li class="disabled active"><a href="###">开启更多过滤条件</a></li>
                            	<?php foreach($groupList as $gName):if($gName):?>
                                	<li><a data-hide="1" href="javascript:void(0)" group="<?php echo $gName?>"><?php echo $gName?></a></li>
                                <?php endif;endforeach;?>
                            </ul>
                        </div>
                    <?php endif;?>
    				<?php $groupList = [''] + $groupList;foreach($groupList as $gName):?>
        				<?php foreach($searchList as $k=>$item):?>
        					<?php if($item['group_name']==$gName):?>
                            	<?php 
                            	if(!empty($item['group_name'])){
                            	    $form->fieldConfig['options']['group'] = $item['group_name'];
                            	}else{
                            	    unset($form->fieldConfig['options']['group']);
                            	}
                            	echo $this->render('@webadmin/modules/config/views/sys-config/_config',[
                            	    'item' => $item,
                            	    'k' => $item['attribute'],
                            	    'form' => $form,
                            	]);
                            	?>
                        	<?php endif;?>
                        <?php endforeach;?>
                    <?php endforeach;?>
    
                    <div class="form-group">
                    	<?= Html::button('<i class="fa fa-search"></i> '.Yii::t('common','查询'), ['class' => 'btn btn-primary report_search_btn']) ?>
                        <?= Html::button('<i class="fa fa-download"></i> '.Yii::t('common','导出'), ['class' => 'btn btn-primary report_export_btn']) ?>
                        <?= Html::hiddenInput('is_export',''); ?>
                        <?php  
                        $apiUrl = $this->context->apiUrl;
                        $this->registerJs("$('.report_search_btn,.report_export_btn').off('click').on('click',function(){
                            var form = $(this).closest('form');
                            form.find('input[name=is_export]').val($(this).is('.report_export_btn') ? '1' : '');
                            if('{$count}'>1 && $(this).is('button[type=button].report_search_btn')){ // 异步加载数据
                                var box = $(this).closest('.report-item-box'),
                                    table = box.find('.dataTable[id]').eq(0),
                                    ajax = $(table).dataTable().api().ajax,
                                    apiUrl = box.attr('api-url');
                                if(apiUrl){
                                    ajax.url(apiUrl + (apiUrl.indexOf('?')<0 ? '?' : '&') + form.serialize());
                                    ajax.reload();
                                }
                                return false;
                            }else{
                                form.submit();
                            }
                        });$('.dataconter-search').hide();", 4, 'report.search.submit');
                        ?>
                    </div>
                    
                    <!-- 收缩/展开 -->
                    <div class="form-group search_box">
                        <a class="btn btn-primary search_box_btn" href="#"><i class='fa fa-angle-double-up'></i> <?= Yii::t('datacenter','收起过滤')?></a>
                        <a class="btn btn-primary search_box_btn" href="#" style="display:none;"><i class='fa fa-angle-double-down'></i> <?= Yii::t('datacenter','展开过滤')?></a>
                        <?php  
                        $this->registerJs("
                        // 收缩/展开
                        $('.search_box_btn').off('click').on('click',function(){
                            $(this).closest('.dataconter-search').find('.form-group').not('.search_box').toggle();
                            $(this).closest('.search_box').find('.search_box_btn').show();
                            $(this).hide();
                            $('.data-report-row').triggerHandler('relad.layout');
                        });
                        // 更多过滤条件
                        $('.more_search_box').off('click').on('click','a[group]',function(){
                            var el = $(this),
                                group = el.attr('group'),
                                isHide = el.data('hide'),
                                box = el.closest('.dataconter-search').find('.form-group[group=\''+ group +'\']');
                            isHide ? box.show()&&el.closest('li').addClass('active') : box.hide()&&el.closest('li').removeClass('active');
                            el.data('hide', (isHide ? 0 : 1));
                        });
                        $('.dataconter-search .form-group[group]').hide();
                        ", 4, 'report.search.slide'); // $('.dataconter-search').find('.form-group').not('.search_box').hide();
                        ?>
                    </div>
    
    				<?php ActiveForm::end(); ?>
    			</div>
    		</div>
    	</div>
    </div>
<?php endif;?>