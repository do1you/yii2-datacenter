<?php if(Yii::$app->session['API_TOKEN']):?>
	<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 data-report-index">
    	<div class="widget flat margin-bottom-10">
            <div class="widget-header bg-pink">
                <span class="widget-caption">报表功能</span>
            </div>
            <div class="widget-body no-padding">
                <div class="dd dd-draghandle">
                    <ol class="dd-list">
                    	<li class="dd-item">
                			<a class="dd2-content well bordered-left bordered-orange" href="<?php echo \yii\helpers\Url::to(['report/build'])?>">构建报表 </a>
                		</li>
                		<li class="dd-item">
                			<a class="dd2-content well bordered-left bordered-orange" href="<?php echo \yii\helpers\Url::to(['report/index'])?>">报表管理 </a>
                		</li>
                		<li class="dd-item">
                			<a class="dd2-content well bordered-left bordered-orange" href="<?php echo \yii\helpers\Url::to(['sets/index'])?>">数据集管理 </a>
                		</li>
                		<li class="dd-item">
                			<a class="dd2-content well bordered-left bordered-orange" href="<?php echo \yii\helpers\Url::to(['share/index'])?>">分享报表 </a>
                		</li>
                    </ol>
                </div>
            </div>
        </div>
	</div>
<?php endif;?>