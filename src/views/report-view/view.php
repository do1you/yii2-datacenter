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
?>
<div class="row data-report-row">
	<?php foreach($list as $key=>$model):?>
		<?php if($key>0 && $key%$row==0):?>
			</div><div class="row data-report-row">
		<?php endif;?>
    	<div class="col-xs-12 col-md-<?php echo $col?> data-report-index" rid="<?php echo $model['id']?>">
        	<?= \datacenter\widgets\Grid::widget([
                'reportModel' => $model,
        	    'isCache' => (!isset($cache) ? true : $cache),
            ]); ?>
    	</div>
	<?php endforeach;?>
</div>