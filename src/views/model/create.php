<?php
Yii::$app->controller->currNav[] = Yii::t('common','添加');
?>
<?= $this->render('_form', [
    'model' => $model,
]) ?>