<?php
use yii\widgets\Pjax;

$count = !empty($list) ? count($list) : 1;
if($count<=1){
    $col = 12;
}elseif($count<=2){
    $col = 6;
}elseif($count<=6){
    $col = 4;
}else{
    $col = 3;
}
$row = 12/$col;
if($count>1){ // 瀑布流
    $this->registerJsFile("@assetUrl/js/masonry.pkgd.min.js",['depends' => \webadmin\WebAdminAsset::className()]);
    $this->registerJs("$('.data-report-row').on('relad.layout',function(){
        $('.data-report-row').masonry({itemSelector:'.data-report-index'});
    }).triggerHandler('relad.layout');", 4, 'report.item.masonry');
}
?>
<div class="row data-report-row">
	<?php foreach($list as $key=>$model):?>
    	<div class="col-xs-12 col-md-<?php echo $col?> data-report-index" rid="<?php echo $model['id']?>">
        	<?= \datacenter\widgets\Grid::widget([
        	    'reportList' => $list,
                'reportModel' => $model,
        	    'isCache' => (!isset($cache) ? true : $cache),
            ]); ?>
    	</div>
	<?php endforeach;?>
</div>
<?= $this->render('/report-view/_nav', []) ?>