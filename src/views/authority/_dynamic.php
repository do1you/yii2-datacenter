<?php 
$sourceList = \yii\helpers\ArrayHelper::map(\datacenter\models\DcSource::find()->where(['is_dynamic'=>'1'])->all(), 'id', 'v_self'); // 'is_dynamic'=>'1'
?>
<?php if($sourceList):?>
<!-- tab start -->
<div class="tabbable">
    <ul class="nav nav-tabs tabs-flat">
    	<?php $key=0;foreach($sourceList as $source):$key++;?>
    		<?php if($key==1):?>
				<li class="active"><a data-toggle="tab" href="#tab_dynamic_<?php echo $source['id']?>" aria-expanded="true"><?php echo $source['name']?></a></li>
			<?php else:?>
				<li><a data-toggle="tab" href="#tab_dynamic_<?php echo $source['id']?>" aria-expanded="false"><?php echo $source['name']?></a></li>
			<?php endif;?>
		<?php endforeach;?>
    </ul>
    <div class="tab-content tabs-flat">
    	<?php $key=0;foreach($sourceList as $source):$key++;?>
    		<div id="tab_dynamic_<?php echo $source['id']?>" class="tab-pane<?php echo ($key==1 ? ' active' : '')?>">
    			<?php $list = \yii\helpers\ArrayHelper::map($source->getDynamicList(), 'id', 'name');?>
    			<?php echo $form->field($aModel, 'dynamicSourceList')->label($source['name'])
    			->duallistbox($list, [
    			    'style'=>'height:350px;',
    			    'name'=>"DcRoleAuthority[dynamicSourceList][{$source['id']}][]",
    			    'value'=>(isset($aModel->dynamicSourceList[$source['id']]) ? $aModel->dynamicSourceList[$source['id']] : []),
    			    'id'=>"dcroleauthority-dynamicsourcelist-{$source['id']}",
                ])?>
    		</div>
		<?php endforeach;?>
	</div>
</div>
<!-- tab end -->
<?php endif;?>