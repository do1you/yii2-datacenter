<?php

use datacenter\models\DcModel;
use yii\data\ActiveDataProvider;

$bmodel = new DcModel();
$dataProvider = $bmodel->search(['DcModel'=>['source_id'=>$model['id']]],[],['source','cat']);

Yii::$app->session['model'] = ['source/view','id'=>$model['id'],'#'=>'tab_model'];

echo $this->render('/model/index', [
    'dataProvider' => $dataProvider,
    'model' => $bmodel,
    'sId' => $model['id'],
]);

?>