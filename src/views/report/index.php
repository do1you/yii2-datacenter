<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row dc-report-index">
	<div class="col-xs-12 col-md-12">
		<?php echo $this->render('_search', ['model' => $model]); ?>
    	<div class="widget flat radius-bordered">
    		<div class="widget-header bg-themeprimary">
    		    <span class="widget-caption">&nbsp;</span>
    		    <div class="widget-buttons">
    				<a href="#" data-toggle="collapse" title="<?= Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
    				<a href="#" data-toggle="maximize" title="<?= Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
    		    </div>
    		</div>
    		<div class="widget-body checkForm">
    			<div class="row">
    				<div class="col-xs-12 col-md-12">
    					<div class="pull-right margin-bottom-10">
    						<a class="btn btn-primary" href="<?php echo Url::to(['build']);?>" data-pjax="0"><i class='fa fa-building'></i> <?= Yii::t('datacenter','构建报表')?></a>
    						<button class="btn btn-primary checkSubmit" reaction="<?php echo Url::to(['delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= Yii::t('common','批量删除')?></button>
    						<button class="btn btn-primary checkSubmit" reaction="<?php echo Url::to(['report-view/view','reid'=>'id']);?>" type="button"><i class='fa fa-bars'></i> <?= Yii::t('common','多表查看')?></button>
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
                	         'attribute' => 'state',
                	         'value' => 'v_state',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'show_type',
                	         'value' => 'v_show_type',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'set_ids',
                	         'value' => 'v_sets_str',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'paixu',
                	         'value' => 'paixu',
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
                    	     'template' => '{show} {copy} {update} {delete}',
                    	     'buttons'=>[
                    	         'show'=>function($url,$model){
                    	             return Html::a('<span class="fa fa-navicon"></span>', ['report-view/view', 'id' => $model->id], [
                        	             'title' => Yii::t('datacenter', '查看报表'),
                        	             'target' => '_blank',
                        	             'data-pjax'=>'0',
                        	         ]);
                        	     },
                        	     'copy'=>function($url,$model){
                            	     return Html::a('<span class="fa fa-copy"></span>', $url, [
                            	         'title' => Yii::t('datacenter', '复制报表'),
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


