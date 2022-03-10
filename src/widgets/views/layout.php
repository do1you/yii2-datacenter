<?php 

$sourceList = $model ? $model['v_source'] : [];
?>
<?php if(isset($cache) && $cache===true) echo $search;?>
<div class="widget flat radius-bordered">
	<div class="widget-header bg-themeprimary">
	    <span class="widget-caption"><?php echo $model['title']?>&nbsp;</span>
	    <div class="widget-buttons">
	    	<?php if(isset($cache) && $cache===true):?>
				<?php 
                $form = Yii::createObject('webadmin\widgets\ActiveForm');
                foreach($sourceList as $source){
                    if($source['is_dynamic']=='1'){
                        $model->set_source = Yii::$app->session[$source['v_sessionName']];
                        echo '<div class="btn-group"><div class="tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="'.$source['name'].'">';
                        echo $form->field($model, 'set_source', ['template'=>'{input}','options'=>['class' => '']])
                        ->select2(\yii\helpers\ArrayHelper::map($source['dynamicList'], 'id', 'name'),[
                            'class'=>'form-control select-dynamic-source',
                            'source_id'=>$source['id'],
                            'id'=>'select-dynamic-source-'.$source['id'],
                        ]);
                        echo '</div></div>';
                    }
                }
                ?>
            <?php endif;?>
			<a href="#" data-toggle="collapse" title="<?= Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
			<a href="#" data-toggle="maximize" title="<?= Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
			<?php if(isset($cache) && $cache===false):?>
				<a href="#" data-toggle="save" report-id="<?php echo $model['id']?>" title="<?= Yii::t('datacenter','保存报表')?>"><i class="fa fa-save"></i></a>
				<a href="#" data-toggle="dispose" report-id="<?php echo $model['id']?>" title="<?= Yii::t('datacenter','删除')?>"><i class="fa fa-times"></i></a>
			<?php elseif(isset($cache) && $cache===true):?>
				<a href="#" data-toggle="collection" report-id="<?php echo $model['id']?>" title="<?= Yii::t('datacenter','收藏报表')?>"><i class="fa fa-star-o"></i></a>
			<?php endif;?>
	    </div>
	</div>
	<div class="widget-body">
    	<?php echo $content;?>
    </div>
</div>
<?php 
$url = \yii\helpers\Url::to(['set-source']);
$script = <<<eot
// 切换数据源
$('select.select-dynamic-source').off('change').on('change',function(){
    var t = $(this),
        id = t.val(),
        sid = t.attr('source_id');
    location.href = "{$url}?id="+id+"&sid="+sid;

});
eot;
$this->registerJs($script);
?>