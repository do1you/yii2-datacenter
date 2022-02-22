<?php $content = $this->render('_reports', [
    'reportList' => \datacenter\models\DcReport::model()->findModel([
        'create_user' => Yii::$app->user->id,
        'state' => '9',
    ],true),
]);?>
<?php $this->head() ?>
<?php $this->beginPage() ?>
<?php $this->beginBody() ?>
<?php echo $content;?>    
<?php $this->endBody() ?>
<?php $this->endPage() ?>