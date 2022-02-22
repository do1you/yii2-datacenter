<?php

use datacenter\models\DcAttribute;
use yii\data\ActiveDataProvider;

$bmodel = new DcAttribute();
$dataProvider = $bmodel->search(['DcAttribute'=>['model_id'=>$model['id']]],[],['dcmodel.source']);

Yii::$app->session['column'] = ['model/view','id'=>$model['id'],'#'=>'tab_column'];

echo $this->render('/column/index', [
    'dataProvider' => $dataProvider,
    'model' => $bmodel,
    'mId' => $model['id'],
]);

?>