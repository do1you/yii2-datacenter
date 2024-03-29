<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row dc-sets-index">
	<div class="col-xs-12 col-md-12">
		<?php echo $this->render('_search', ['model' => $model]); ?>
    	<div class="widget flat radius-bordered">
    		<div class="widget-header bg-themeprimary">
    		    <span class="widget-caption">&nbsp;</span>
    		    <div class="widget-buttons">
    				<a href="#" data-toggle="collapse" title="<?= Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
    				<a href="#" data-toggle="maximize" title="<?= Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
    				<a href="#" data-toggle="modal" data-target=".bs-nav-modal" title="<?= Yii::t('datacenter','报表选择')?>"><i class="fa fa-bars"></i></a>
    		    </div>
    		</div>
    		<div class="widget-body checkForm">
    			<div class="row">
    				<div class="col-xs-12 col-md-12">
    					<div class="pull-right margin-bottom-10">
    						<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common','树型数据')?></a> */?>
    						<a class="btn btn-primary" data-pjax="0" href="<?php echo Url::to(['create']);?>"><i class='fa fa-plus'></i> <?= Yii::t('common','添加')?></a>
    						<button class="btn btn-primary checkSubmit" reaction="<?php echo Url::to(['delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= Yii::t('common','批量删除')?></button>
    						<button class="btn btn-primary checkSubmit" reaction="<?php echo Url::to(['report-view/set-view','reid'=>'id']);?>" type="button"><i class='fa fa-bars'></i> <?= Yii::t('common','多表查看')?></button>
    						<?php echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken());?>
    		    		</div>
    				</div>
    			</div>

            	<?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $model,
                    'columns' => [
                    	[
                    	    'class' => '\yii\grid\CheckboxColumn',
                    	    'name' => 'id',
                    	    'header' => '<label><input type="checkbox" name="id_all" class="checkActive select-on-check-all"><span class="text"></span></label>',
                    	    'content' => function($model, $key, $index){
                    	       return '<label><input type="checkbox" name="id[]" class="checkActive" value="'.$key.'"><span class="text"></span></label>';
            	            },
            	        ],
                    	//['class' => '\yii\grid\SerialColumn'],

                	     'id',
                	     'title',
                	     [
                	         'attribute' => 'cat_id',
                	         'value' => 'cat.v_parentName',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'set_type',
                	         'value' => 'v_set_type',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'main_model',
                	         'value' => 'mainModel.v_tb_name',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'relation_models',
                	         'value' => 'v_relation_models_str',
                	         'filter' => false,
                	     ],
                	     'run_script',
                	     //'excel_file',
                	     [
                	         'attribute' => 'state',
                	         'value' => 'v_state',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'is_index_show',
                	         'value' => 'v_is_index_show',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'create_user',
                	         'value' => 'user.name',
                	         'filter' => false,
                	     ],
                	     'update_time',

                        [
                        	'class' => '\yii\grid\ActionColumn',
                        	'buttonOptions' => ['data-pjax'=>'0'],
                            'headerOptions'=>['width'=>'120'],
                            'template' => '{show} {copy} {view} {update} {delete}',
                            'buttons'=>[
                                'show'=>function($url,$model){
                                    return Html::a('<span class="fa fa-navicon"></span>', \yii\helpers\Url::to([
                                        'report-view/set-view', 'id' => $model->id
                                    ]), [
                                        'title' => Yii::t('datacenter', '查看报表'),
                                        'target' => '_blank',
                                        'data-pjax'=>'0',
                                    ]);
                    	         },
                    	         'copy'=>function($url,$model){
                        	         return Html::a('<span class="fa fa-copy"></span>', $url, [
                        	             'title' => Yii::t('datacenter', '复制数据集'),
                        	             'data-pjax'=>'0',
                        	         ]);
                    	         },
                	         ],
                        ],
                    ],
                ]); ?>

		    </div>
	    </div>
	</div>
</div>
<?php Pjax::end(); ?>
<?= $this->render('/report-view/_nav', []) ?>


