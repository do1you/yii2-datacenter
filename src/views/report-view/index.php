<div class="row data-report-row">
	<?= $this->render('/report-view/_api_nav', []) ?>
	<?php if(empty($catList)):?>
		<div class="col-xs-12 col-md-2 data-report-index">您暂时还没有任何报表可以查看...</div>
	<?php else:?>
		<?php foreach($catList as $cat):?>
			<?php if(isset($userReport[$cat['id']]) || isset($userSets[$cat['id']])):?>
        		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 data-report-index">
                	<div class="widget flat margin-bottom-10">
                        <div class="widget-header bg-purple">
                            <span class="widget-caption">我的<?php echo $cat['name']?></span>
                        </div>
                        <div class="widget-body no-padding" style="background:none;">
                            <div class="dd dd-draghandle">
                                <ol class="dd-list">
                                	<?php if(isset($userReport[$cat['id']])):foreach($userReport[$cat['id']] as $item):?>
                                		<li class="dd-item">
                                			<a class="dd2-content well bordered-left bordered-success" href="<?php echo \yii\helpers\Url::to(['view','id'=>$item['report_id'],'vid'=>$item['id']])?>"><?php echo $item['v_name']?></a>
                                		</li>
                                	<?php endforeach;endif;?>
                                	<?php if(isset($userSets[$cat['id']])):foreach($userSets[$cat['id']] as $item):?>
                                		<li class="dd-item">
                                			<a class="dd2-content well bordered-left bordered-yellow" href="<?php echo \yii\helpers\Url::to(['set-view','id'=>$item['set_id'],'vid'=>$item['id']])?>"><?php echo $item['v_name']?></a>
                                		</li>
                                	<?php endforeach;endif;?>
                                </ol>
                            </div>
                        </div>
                    </div>
            	</div>
        	<?php endif;?>
    	<?php endforeach;?>
		<?php foreach($catList as $cat):?>
			<?php if(isset($defReport[$cat['id']]) || isset($defSets[$cat['id']])):?>
        		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 data-report-index">
                	<div class="widget flat margin-bottom-10">
                        <div class="widget-header bg-info">
                            <span class="widget-caption"><?php echo $cat['name']?></span>
                        </div>
                        <div class="widget-body no-padding" style="background:none;">
                            <div class="dd dd-draghandle">
                                <ol class="dd-list">
                                	<?php if(isset($defReport[$cat['id']])):foreach($defReport[$cat['id']] as $item):?>
                                		<li class="dd-item">
                                			<a class="dd2-content well bordered-left bordered-success" href="<?php echo \yii\helpers\Url::to(['view','id'=>$item['id']])?>"><?php echo $item['v_title']?></a>
                                		</li>
                                	<?php endforeach;endif;?>
                                	<?php if(isset($defSets[$cat['id']])):foreach($defSets[$cat['id']] as $item):?>
                                		<li class="dd-item">
                                			<a class="dd2-content well bordered-left bordered-yellow" href="<?php echo \yii\helpers\Url::to(['set-view','id'=>$item['id']])?>"><?php echo $item['v_title']?></a>
                                		</li>
                                	<?php endforeach;endif;?>
                                </ol>
                            </div>
                        </div>
                    </div>
            	</div>
        	<?php endif;?>
		<?php endforeach;?>
	<?php endif;?>
</div>

<?php
$this->registerJsFile('@assetUrl/js/nestable/jquery.nestable.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
$this->registerJs("$('.dd').nestable();$('.dd-handle a').on('mousedown', function (e) { e.stopPropagation(); });");
// 瀑布流
$this->registerJsFile("@assetUrl/js/masonry.pkgd.min.js",['depends' => \webadmin\WebAdminAsset::className()]);
$this->registerJs("$('.data-report-row').on('relad.layout',function(){
    $('.data-report-row').masonry({itemSelector:'.data-report-index'});
}).triggerHandler('relad.layout');
setTimeout(function(){
    $('.data-report-row').triggerHandler('relad.layout');
},500);
", 4, 'report.index.masonry');
?>