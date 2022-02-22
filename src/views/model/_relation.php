<?php

use datacenter\models\DcRelation;
use yii\data\ActiveDataProvider;

$bmodel = new DcRelation();
$dataProvider = $bmodel->search(['DcRelation'=>['source_model'=>$model['id']]],[],['sourceModel','targetModel']);

Yii::$app->session['relation'] = ['model/view','id'=>$model['id'],'#'=>'tab_relation'];

echo $this->render('/relation/index', [
    'dataProvider' => $dataProvider,
    'model' => $bmodel,
    'mId' => $model['id'],
]);

?>