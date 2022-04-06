<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row dc-sets-columns-index">
	<div class="col-xs-12 col-md-12">
		<?php if(empty($sId)) echo $this->render('_search', ['model' => $model]); ?>
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
    						<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common','树型数据')?></a> */?>
    						<a class="btn btn-primary" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" href="<?php echo Url::to(['sets-col/create', 'sId'=>(empty($sId) ? '' : $sId)]);?>"><i class='fa fa-plus'></i> <?= Yii::t('common','添加')?></a>
    						<a class="btn btn-primary" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" href="<?php echo Url::to(['sets-col/batch-create', 'sId'=>(empty($sId) ? '' : $sId)]);?>"><i class='fa fa-indent'></i> <?= Yii::t('common','批量添加')?></a>
    						<button class="btn btn-primary checkSubmit" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" reaction="<?php echo Url::to(['sets-col/delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= Yii::t('common','批量删除')?></button>
    						<?php echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken());?>
    		    		</div>
    				</div>
    			</div>

            	<?= GridView::widget([
                    'dataProvider' => $dataProvider,
            	    'filterModel' => (empty($sId) ? $model : null),
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
                	     [
                	         'attribute' => 'set_id',
                	         'value' => 'sets.title',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'model_id',
                	         'value' => 'model.v_tb_name',
                	         'filter' => false,
                	     ],
                	     'name',
                	     'label',
                	     'sql_formula',
                	     [
                	         'attribute' => 'is_search',
                	         'value' => 'v_is_search',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'type',
                	         'value' => 'v_type',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'fun',
                	         'value' => 'v_fun',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'is_frozen',
                	         'value' => 'v_is_frozen',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'paixu',
                	         'value' => 'paixu',
                	         'filter' => false,
                	     ],
                	     'formula',

                        [
                        	'class' => '\yii\grid\ActionColumn',
                            'buttonOptions' => ['data-pjax'=>(empty($sId) ? '1' : '0')],
                            'urlCreator' => function ($action, $model, $key, $index) {
                                return ['sets-col/'.$action, 'id' => $model->id];
            	            },
                        ],
                    ],
                ]); ?>

		    </div>
	    </div>
	</div>
</div>
<?php Pjax::end(); ?>


