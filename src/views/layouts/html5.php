<?php $this->beginContent('@webadmin/views/html5.php'); ?>
	<?php echo $this->render('@webadmin/views/_flash'); ?>
	<?php if(Yii::$app->session['API_TOKEN']):?>
    	<div class="alert alert-info fade in">
    	    <button class="close" data-dismiss="alert">×</button>
    	    建议您直接访问数据中心地址 
    	    <a href="<?php echo \yii\helpers\Url::to(['//authority/user/logout'],true)?>" target="_blank">
    	    	<span class="orange"><?php echo \yii\helpers\Url::to(['//yalalat'],true)?></span>
    	    </a>，登录用户名 <span class="orange"><?php echo Yii::$app->user->identity['login_name']?></span>，
    	    登录密码为 <span class="orange">当前系统密码</span>，
    	    以获得更好的用户体验！
    	</div>
	<?php endif;?>
	<?php 
	$this->registerJs("setTimeout(function(){
		$('body>.alert:not(.exceldown)').fadeOut();
	},8000);");
	?>
	<?php echo $content?>
<?php $this->endContent(); ?>