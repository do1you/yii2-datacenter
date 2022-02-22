<?php
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php if(!empty($showLayout)): // 树型数据节点?>
	<ol class="dd-list">
		<?php foreach($treeData as $item):?>
			<?php			
			$content = !empty($item['children']) ? $this->render('tree', array(
				'treeData'=>$item['children'],
			    'showLayout'=>true,
			),true) : "";
			?>
            <li class="dd-item dd-nodrag dd2-item" data-id="<?php echo $item['id']?>">
                <div class="dd2-handle" >
                    <i class="normal-icon <?php echo (!empty($item['icon']) ? $item['icon'] : 'fa fa-bookmark')?>"></i>
                    <i class="drag-icon fa fa-arrows-alt "></i>
                </div>
                <div class="dd2-content">
                	<span class="pull-left"><?php echo $item['name']?></span>
                    <span class="pull-right">
                        <a class="btn btn-primary btn-xs" href="<?php echo Url::to(['create','id'=>$item['id']])?>"><i class='fa fa-plus'></i> 添加</a>&nbsp;
                        <a class="btn btn-primary btn-xs edit" href="<?php echo Url::to(['update','id'=>$item['id']])?>"><i class='fa fa-edit'></i> 编辑</a>&nbsp;
                        <a class="btn btn-primary btn-xs delete" href="<?php echo Url::to(['delete','id'=>$item['id']])?>"><i class='fa fa-trash-o'></i> 删除</a>
                    </span>
                </div>
        		<?php echo $content; ?>
        	</li>
	    <?php endforeach;?>
	</ol>
<?php return;endif;?>

<?php Pjax::begin(); ?>
<div class="row dc-cat-tree">
	<div class="col-xs-12 col-md-12">
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
    						<a class="btn btn-primary" href="<?php echo Url::to(['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?= Yii::t('common','列表')?></a>
    						<a class="btn btn-primary" href="<?php echo Url::to(['create']);?>"><i class='fa fa-plus'></i> <?= Yii::t('common','添加')?></a>
    		    		</div>
    				</div>
    			</div>

            	<div class="dd dd-draghandle bordered widthAuto dd-button">
                    <?php 
                    if($treeData){
                        echo $this->render('tree', array(
                            'treeData'=>$treeData,
                            'showLayout'=>true,
                        ),true);
                        $this->registerJsFile('@assetUrl/js/nestable/jquery.nestable.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
                        $this->registerJs("$('.dd').nestable();");
                    }else{
                        echo "<hr>暂无数据";
                    }
                    ?>
                </div>

		    </div>
	    </div>
	</div>
</div>
<?php Pjax::end(); ?>


