<?php if(!empty($reportList)):?>
	<?= $this->render('/report-view/view', [
	    'list' => $reportList,
	    'cache' => false,
	]) ?>
<?php elseif(Yii::$app->controller->action->id=='build'):?>
	将数据集字段拖进来就可以直接呈现报表啦！  ~_~
<?php endif;?>