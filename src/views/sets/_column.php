<?php

use datacenter\models\DcSetsColumns;
use yii\data\ActiveDataProvider;

$bmodel = new DcSetsColumns();
$dataProvider = $bmodel->search(['DcSetsColumns'=>['set_id'=>$model['id']]],[],['sets','model.source','forSets']);

if($dataProvider->sort){
    $dataProvider->sort->defaultOrder = [
        'paixu' => SORT_DESC,
    ];
}

Yii::$app->session['sets-col'] = ['sets/view','id'=>$model['id'],'#'=>'tab_column'];

echo $this->render('/sets-col/index', [
    'dataProvider' => $dataProvider,
    'model' => $bmodel,
    'sId' => $model['id'],
]);

?>