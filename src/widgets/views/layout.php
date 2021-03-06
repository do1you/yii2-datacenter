<?php 
use datacenter\models\DcShare;

$sourceList = $model ? $model['v_source'] : [];
?>
<div class="report-item-box" rid="<?php echo $model['id']?>" api-url="<?php echo $this->context->apiUrl?>">
    <?php echo $search;?>
    <div class="widget flat radius-bordered">
    	<div class="widget-header bg-themeprimary">
    	    <span class="widget-caption tooltip-primary" style="position:absolute;left:12px;" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $model['v_report_body']?>">
    	    	<?php echo $model['v_report_title']?>&nbsp;
    	    </span>
    	    <div class="widget-buttons">
    	    	<?php //if(isset($cache) && $cache===true):?>
    				<?php 
    				$form = $this->context->form;
                    foreach($sourceList as $source){
                        if($source['is_dynamic']=='1'){
                            $model->set_source = Yii::$app->session[$source['v_sessionName']];
                            echo '<div class="btn-group"><div class="tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="'.$source['name'].'">';
                            echo $form->field($model, 'set_source', ['template'=>'{input}','options'=>['class' => '']])
                            ->select2(\yii\helpers\ArrayHelper::map($source->getAuthorityDynamicList(Yii::$app->user->id), 'id', 'name'),array_merge([
                                'class'=>'form-control select-dynamic-source',
                                'source_id'=>$source['id'],
                                'id'=>"select-dynamic-source-{$model['id']}-{$source['id']}",
                                ],((Yii::$app->request->get('source')||!Yii::$app->user->id) ? ['readonly'=>'readonly','disabled'=>'disabled'] : [])));
                            echo '</div></div>';
                        }
                    }
                    ?>
                <?php //endif;?>
                <?php if($model['forUserModel'] && $model['forUserModel'] instanceof DcShare):?>
                	<?php if(Yii::$app->user->id && (!$model['forUserModel']['user_ids'] || in_array(Yii::$app->user->id, $model['forUserModel']['v_user_ids']))):?>
                		<!-- a href="#" data-toggle="save" title="<?= Yii::t('datacenter','?????????????????????????????????')?>"><i class="fa fa-save"></i></a -->
                	<?php endif;?>
                <?php else:?>
                	<a href="#" data-toggle="search" title="<?= Yii::t('datacenter','??????/??????????????????')?>"><i class="fa fa-search"></i></a>
        			<?php if(isset($cache) && $cache===false): // ????????????????>
        				<?php if($model instanceof \datacenter\models\DcReport):?>
            				<a href="#" data-toggle="save" report-id="<?php echo $model['id']?>" title="<?= Yii::t('datacenter','????????????')?>"><i class="fa fa-save"></i></a>
            				<a href="#" data-toggle="dispose" report-id="<?php echo $model['id']?>" title="<?= Yii::t('datacenter','??????')?>"><i class="fa fa-times"></i></a>
        				<?php endif;?>
        			<?php elseif(isset($cache) && $cache===true):?>
        				<?php if(!Yii::$app->request->get('access-token')):?>
            				<!-- ?????? -->
            				<a href="#" data-toggle="share" <?php echo ($model instanceof \datacenter\models\DcReport ? 'report-id' : 'set-id')?>="<?php echo $model['id']?>" 
            				before-title="<?php echo $model['title']?>"
            				title="<?= Yii::t('datacenter','??????')?>"><i class="fa fa-share"></i></a>
            				<!-- ?????? -->
            				<a href="#" data-toggle="collection" 
                            <?php if($model['forUserModel']):?>
                            	<?php echo ($model instanceof \datacenter\models\DcReport ? 'user-report-id' : 'user-set-id')?>="<?php echo $model['forUserModel']['id']?>"
                            	before-title="<?php echo $model['forUserModel']['alias_name']?>"
                            <?php endif;?>
            				<?php echo ($model instanceof \datacenter\models\DcReport ? 'report-id' : 'set-id')?>="<?php echo $model['id']?>" 
            				title="<?= Yii::t('datacenter','??????')?>"><i class="fa fa-save"></i></a>
            				<?php if($model['forUserModel']):?>
            					<a href="#" data-toggle="cancel" <?php echo ($model instanceof \datacenter\models\DcReport ? 'user-report-id' : 'user-set-id')?>="<?php echo $model['forUserModel']['id']?>" title="<?= Yii::t('datacenter','??????')?>"><i class="fa fa-times"></i></a>
            				<?php endif;?>
        				<?php endif;?>
        			<?php endif;?>
        			<a href="#" data-toggle="collapse" title="<?= Yii::t('common','?????????')?>"><i class="fa fa-minus"></i></a>
        			<a href="#" data-toggle="maximize" title="<?= Yii::t('common','?????????')?>"><i class="fa fa-expand"></i></a>
    			<?php endif;?>
    	    </div>
    	</div>
    	<div class="widget-body">
        	<?php echo $content;?>
        </div>
    </div>
</div>
<?php 
$url = \yii\helpers\Url::to(['report-view/set-source']);
$script = <<<eot
// ???????????????
$('select.select-dynamic-source').off('change').on('change',function(){
    var t = $(this),
        id = t.val(),
        sid = t.attr('source_id');
    location.href = "{$url}?id="+id+"&sid="+sid;

});
// ????????????
$(document).off('click','a[data-toggle="search"]').on('click','a[data-toggle="search"]',function(){
    $(this).closest('.report-item-box').find('.dataconter-search').slideToggle(function(){
        $('.data-report-row').triggerHandler('relad.layout');
    });
});
eot;
$this->registerJs($script);
?>