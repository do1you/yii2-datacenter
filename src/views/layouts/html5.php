<?php $this->beginContent('@webadmin/views/html5.php'); ?>
	<?php echo $this->render('@webadmin/views/_flash'); ?>
	<?php if(Yii::$app->session['API_TOKEN']):?>
    	<div class="alert alert-warning fade in radius-bordered alert-shadowed">
    	    <button class="close" data-dismiss="alert">×</button>
    	    建议您直接访问数据中心地址 
    	    <a href="<?php echo \yii\helpers\Url::to(['//authority/user/logout'],true)?>" target="_blank">
    	    	<strong class="orange"><?php echo \yii\helpers\Url::to(['//yalalat'],true)?></strong>
    	    </a>，登录用户名 <strong class="orange"><?php echo Yii::$app->user->identity['login_name']?></strong>，
    	    登录密码为 <strong class="orange">当前系统密码</strong>，
    	    以获得更好的用户体验！
    	</div>
	<?php endif;?>
	<?php 
	$this->registerJs("setTimeout(function(){
		$('body>.alert:not(.exceldown)').fadeOut();
	},10000);");
	?>
	<?php echo $content?>
<?php $this->endContent(); ?>