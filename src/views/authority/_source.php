<?php 
$sourceList = \yii\helpers\ArrayHelper::map(\datacenter\models\DcSource::find()->where(['is_dynamic'=>'0'])->all(), 'id', 'name'); // 'is_dynamic'=>'1'
?>

<?= $form->field($aModel, 'sourceList')->duallistbox($sourceList, ['style'=>'height:350px;']) ?>