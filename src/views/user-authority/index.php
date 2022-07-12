<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row auth-role-index">
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
            	<?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $model,
                    'columns' => [
                    	/*[
                    	    'class' => '\yii\grid\CheckboxColumn',
                    	    'name' => 'id',
                    	    'header' => '<label><input type="checkbox" name="id_all" class="checkActive select-on-check-all"><span class="text"></span></label>',
                    	    'content' => function($model, $key, $index){
                    	       return '<label><input type="checkbox" name="id[]" class="checkActive" value="'.$key.'"><span class="text"></span></label>';
            	            },
            	        ],*/
                    	//['class' => '\yii\grid\SerialColumn'],

        	            'id',
        	            'name',
        	            'login_name',
        	            'mobile',
        	            [
        	                'attribute' => 'state',
        	                'value' => 'v_state',
        	            ],
        	            [
        	                'attribute' => 'roleList',
        	                'value' => 'v_roleList',
        	            ],
        	            
        	            [
        	                'class' => '\yii\grid\ActionColumn',
        	                'buttonOptions' => ['data-pjax'=>'1'],
        	                'template' => '{view} {update}',
        	            ],
                    ],
                ]); ?>

		    </div>
	    </div>
	</div>
</div>
<?php Pjax::end(); ?>


