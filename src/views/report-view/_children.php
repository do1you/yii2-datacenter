<?php if(!empty($catItem['children'])):foreach($catItem['children'] as $key=>$childItem):?>
	<?php if(in_array($childItem['id'],$haveCatIds)):?>
    	<li class="dd-item bordered-<?php echo $colors[$key%10]?>">
            <div class="dd2-content well bg-themeprimary"><?php echo $childItem['name']?></div>
            <?php if(!empty($childItem['children']) || !empty($reportList[$childItem['id']])):?>
                <ol class="dd-list" style="">
                	<?php echo $this->render('_children', array('treeData'=>$treeData,'reportList'=>$reportList,'catItem'=>$childItem,'colors'=>$colors,'haveCatIds'=>$haveCatIds));  // 条件搜索?>
                </ol>
            <?php endif;?>
        </li>
    <?php endif;?>
<?php endforeach;endif;?>

<?php if(!empty($reportList[$catItem['id']])):foreach($reportList[$catItem['id']] as $item):?>
	<li class="dd-item"><a class="dd2-content well bordered-left bordered-themeprimary" href="<?php echo \yii\helpers\Url::to(['view','id'=>$item['id']])?>"><?php echo $item['v_title']?></a></li>
<?php endforeach;endif;?>