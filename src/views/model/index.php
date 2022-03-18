<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row dc-model-index">
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
    				<div class="col-xs-12 col-md-12 form-inline">
    					<div class="pull-right margin-bottom-10">
    						<?php echo Html::dropDownList('move_cat_id','',$model->getV_cat_id(false),['prompt'=>'请选择移动分类','id'=>'move_cat_id','class'=>'form-control select2']);?>        						
    						<button class="btn btn-primary checkSubmit" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" reaction="<?php echo Url::to(['model/move']);?>" type="button"><i class='fa fa-mail-forward'></i> <?= Yii::t('datacenter','批量移动分类')?></button>
    						<a class="btn btn-primary" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" href="<?php echo Url::to(['model/create', 'sId'=>(empty($sId) ? '' : $sId)]);?>"><i class='fa fa-plus'></i> <?= Yii::t('common','添加')?></a>
    						<button class="btn btn-primary checkSubmit" data-pjax="<?php echo (empty($sId) ? '1' : '0')?>" reaction="<?php echo Url::to(['model/delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= Yii::t('common','批量删除')?></button>
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
                	     'tb_name',
                	     'tb_label',
                	     [
                	         'attribute' => 'cat_id',
                	         'value' => 'cat.v_parentName',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'source_db',
                	         'value' => 'source.name',
                	         'filter' => false,
                	     ],
                	     [
                	         'attribute' => 'is_visible',
                	         'value' => 'v_is_visible',
                	         'filter' => false,
                	     ],
                	     'paixu',
                	     'update_time',

                        [
                        	'class' => '\yii\grid\ActionColumn',
                            'buttonOptions' => ['data-pjax'=>'0'],
                            'urlCreator' => function ($action, $model, $key, $index) {
                                return ['model/'.$action, 'id' => $model->id];
            	            },
            	            'headerOptions'=>['width'=>'150'],
            	            'template' => '{copy} {init} {view} {update} {delete}',
            	            'buttons'=>[
            	                'init'=>function($url,$model){
                	                return Html::a('<span class="fa fa-gear"></span>', $url, [
                	                    'title' => Yii::t('datacenter', '构建数据集'),
                	                    'data-pjax'=>'0',
                	                ]);
                	            },
                	            'copy'=>function($url,$model){
                    	            return Html::a('<span class="fa fa-copy"></span>', $url, [
                    	                'title' => Yii::t('datacenter', '复制模型'),
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


