<?php $content = $this->render('_reports', [
    'reportList' => $reportList,
]);?>
<?php $this->head() ?>
<?php $this->beginPage() ?>
<?php $this->beginBody() ?>
<?php echo $content;?>    
<?php $this->endBody() ?>
<?php $this->endPage() ?>