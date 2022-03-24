<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row dc-attribute-index">
	<div class="col-xs-12 col-md-12">
		<?php if(empty($mId)) echo $this->render('_search', ['model' => $model]); ?>
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
    						<a class="btn btn-primary" data-pjax="<?php echo (empty($mId) ? '1' : '0')?>" href="<?php echo Url::to(['column/create', 'mId'=>(empty($mId) ? '' : $mId)]);?>"><i class='fa fa-plus'></i> <?= Yii::t('common','添加')?></a>
    						<button class="btn btn-primary checkSubmit" data-pjax="<?php echo (empty($mId) ? '1' : '0')?>" reaction="<?php echo Url::to(['column/delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= Yii::t('common','批量删除')?></button>
    						<?php echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken());?>
    		    		</div>
    				</div>
    			</div>

            	<?= GridView::widget([
                    'dataProvider' => $dataProvider,
            	    'filterModel' => (empty($mId) ? $model : null),
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
                	     'name',
                	     'label',
                	     'type',
                	     'length',
                	     'default',
                	     [
                	         'attribute' => 'model_id',
                	         'value' => 'model.v_tb_name',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'is_visible',
                	         'value' => 'v_is_visible',
                	         'filter' => false,
                	     ],

                        [
                        	'class' => '\yii\grid\ActionColumn',
                            'buttonOptions' => ['data-pjax'=>(empty($mId) ? '1' : '0')],
                            'urlCreator' => function ($action, $model, $key, $index) {
                                return ['column/'.$action, 'id' => $model->id];
            	            },
                        ],
                    ],
                ]); ?>

		    </div>
	    </div>
	</div>
</div>
<?php Pjax::end(); ?>


