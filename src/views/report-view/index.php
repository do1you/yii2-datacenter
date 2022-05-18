<?php
$colors = explode('|','inverse|blue|palegreen|danger|warning|info|success|darkorange|magenta|purple|maroon');
?>
<div class="row">
	<div class="col-xs-12">
		<?php if(empty($myreportList) && empty($reportList)):?>
			<div class="col-xs-12 col-md-2">您暂时还没有任何报表可以查看...</div>
		<?php endif;?>
		<div class="reportview">
            <?php if($myreportList): // 我的报表?>
            <div class="reportitem">
            	<div class="widget flat margin-bottom-10">
                    <div class="widget-header bordered-bottom bordered-themeprimary">
                        <span class="widget-caption">我的报表</span>
                    </div>
                    <div class="widget-body no-padding" style="background:none;">
                        <div class="dd">
                            <ol class="dd-list">
                            	<?php foreach($myreportList as $item):?>
                            		<li class="dd-item">
                            			<a class="dd2-content well bordered-left bordered-themeprimary" href="<?php echo \yii\helpers\Url::to(['view','id'=>$item['id']])?>"><?php echo $item['v_title']?></a>
                            		</li>
                            	<?php endforeach;?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif;?>
            
            <?php if(!empty($treeData) && !empty($reportList)):foreach($treeData as $catItem): // 分类报表?>
            	<?php if(in_array($catItem['id'],$haveCatIds)):?>
            	<div class="reportitem">
                	<div class="widget flat margin-bottom-10">
                        <div class="widget-header bordered-bottom bordered-themeprimary">
                            <span class="widget-caption"><?php echo $catItem['name']?></span>
                        </div>
                        <div class="widget-body no-padding" style="background:none;">
                            <div class="dd dd-draghandle">
                                <ol class="dd-list">
                                	<?php echo $this->render('_children', array('treeData'=>$treeData,'reportList'=>$reportList,'catItem'=>$catItem,'colors'=>$colors,'haveCatIds'=>$haveCatIds));  // 条件搜索?>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif;?>
            <?php endforeach;?>
            <?php if(!empty($reportList['0'])):foreach($reportList['0'] as $item):?>
            	<div class="reportitem">
                	<div class="widget flat margin-bottom-10">
                        <div class="widget-header bordered-bottom bordered-themeprimary">
                            <span class="widget-caption">未分类</span>
                        </div>
                        <div class="widget-body no-padding" style="background:none;">
                            <div class="dd dd-draghandle">
                                <ol class="dd-list">
                                	<li class="dd-item"><a class="dd2-content well bordered-left bordered-themeprimary" href="<?php echo \yii\helpers\Url::to(['view','id'=>$item['id']])?>"><?php echo $item['v_title']?></a></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach;endif;?>
            <?php endif;?>
        </div>
    </div>
</div>
<style>
<!--
.reportview{column-count:1;column-width:100%;column-gap:15px;-webkit-column-count:1;-webkit-column-width:100%;-webkit-column-gap:15px;}
@media (min-width: 768px) {
    .reportview{column-count:3;column-width:33.333%;column-gap:15px;-webkit-column-count:3;-webkit-column-width:33.33%;-webkit-column-gap:15px;}
}
@media (min-width: 992px) {
    .reportview{column-count:4;column-width:25%;column-gap:15px;-webkit-column-count:4;-webkit-column-width:25%;-webkit-column-gap:15px;}
}
@media (min-width: 1200px) {
    .reportview{column-count:5;column-width:20%;column-gap:15px;-webkit-column-count:5;-webkit-column-width:20%;-webkit-column-gap:15px;}
}
.reportitem{break-inside: avoid;-webkit-column-break-inside:avoid;padding-bottom:15px;}
.reportview .widget{margin-bottom:0;}
.reportview .orders-container{margin-bottom:5px;}
-->
</style>
<?php
$this->registerJsFile('@assetUrl/js/nestable/jquery.nestable.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
$this->registerJs("$('.dd').nestable();$('.dd-handle a').on('mousedown', function (e) { e.stopPropagation(); });");
// 瀑布流
/*
$this->registerJsFile("@assetUrl/js/masonry.pkgd.min.js",['depends' => \webadmin\WebAdminAsset::className()]);
$this->registerJs("$('.reportview').on('relad.layout',function(){
    $('.reportview').masonry({itemSelector:'.reportitem'});
}).triggerHandler('relad.layout');", 4, 'report.index.masonry');
*/
?>