<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

?>
<?php Pjax::begin(['timeout'=>5000]); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common','树型数据')?></a> */?>
			<a class="btn btn-primary" href="<?php echo Url::to(!empty(Yii::$app->session[Yii::$app->controller->id]) ? Yii::$app->session[Yii::$app->controller->id] : ['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','列表')?></a>
			<?php if(Yii::$app->controller->action->id!='create'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['create'])?>"><i class="ace-icon fa fa-plus bigger-110"></i> <?php echo Yii::t('common','添加')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['update','id'=>$model->primaryKey])?>"><i class="ace-icon fa fa-edit bigger-110"></i> <?php echo Yii::t('common','编辑')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view' || Yii::$app->controller->action->id=='update'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['delete','id'=>$model->primaryKey])?>" data-pjax="0"><i class="ace-icon fa fa-trash-o bigger-110"></i> <?php echo Yii::t('common','删除')?></a>
			<?php endif;?>
		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row dc-sets-form">
	<div class="col-xs-12">
		<!-- widget start -->
		<div class="widget flat radius-bordered">
			<div class="widget-header bg-themeprimary">
			    <span class="widget-caption">数据集 <?php echo $model->id ? $model['title'] : '新增'?></span>
			</div>
			<div class="widget-body">
			    <div class="widget-main ">
			    	<!-- tab start -->
			    	<div class="tabbable">
        			    <ul class="nav nav-tabs tabs-flat">
        		    		<li class="active"><a data-toggle="tab" href="#tab_baseinfo" aria-expanded="true">基本信息</a></li>
    		    			<?php if($model['id']):?>
    		    				<li><a data-toggle="tab" href="#tab_column" aria-expanded="false">数据集字段</a></li>
    		    				<li><a data-toggle="tab" href="#tab_relation" aria-expanded="false">数据集关系</a></li>
    		    			<?php endif;?>
        			    </ul>
        			    <div class="tab-content tabs-flat">
        		    		<div id="tab_baseinfo" class="tab-pane active">
        		    			<?= $this->render('_baseinfo', [
                                    'model' => $model,
                                ]) ?>
        		    		</div>
        		    		<?php if($model['id']):?>
            		    		<div id="tab_column" class="tab-pane">
            		    			<?= $this->render('_column', [
                                        'model' => $model,
                                    ]) ?>
            		    		</div>
            		    		<div id="tab_relation" class="tab-pane">
            		    			<?= $this->render('_relation', [
                                        'model' => $model,
                                    ]) ?>
            		    		</div>
        		    		<?php endif;?>
        		    	</div>
        		    </div>
        		    <!-- tab end -->
            	</div>
            </div>    
        </div> 
        <!-- widget end -->
    </div>    		   
</div>
<?php $this->registerJs("location.hash && $('a[href=\"'+location.hash+'\"]').tab('show');");?>
<?php Pjax::end(); ?>

