<?php
Yii::$app->controller->currNav[] = Yii::t('common','查看');
?>
<?= $this->render('_form', [
    'model' => $model,
]) ?>