<?php
Yii::$app->controller->currNav[] = Yii::t('common','编辑');
?>
<?= $this->render('_form', [
    'model' => $model,
]) ?>