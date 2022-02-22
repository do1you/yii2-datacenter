<?php

use datacenter\models\DcSetsRelation;
use yii\data\ActiveDataProvider;

$bmodel = new DcSetsRelation();
$dataProvider = $bmodel->search(['DcSetsRelation'=>['source_sets'=>$model['id']]],[],['sourceSets','targetSets']);

Yii::$app->session['sets-rel'] = ['sets/view','id'=>$model['id'],'#'=>'tab_relation'];

echo $this->render('/sets-rel/index', [
    'dataProvider' => $dataProvider,
    'model' => $bmodel,
    'sId' => $model['id'],
]);

?>