<?php if(isset($cache) && $cache===true) echo $search;?>
<div class="widget flat radius-bordered">
	<div class="widget-header bg-themeprimary">
	    <span class="widget-caption"><?php echo $model['title']?>&nbsp;</span>
	    <div class="widget-buttons">
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