<?php 
use webadmin\widgets\ActiveForm;
?>
<?php echo $this->render('view', [
    'list' => $list,
    //'cache' => false,
]);
$rmodel = new \datacenter\models\DcUserReport;
$rmodel->loadDefaultValues();
?>

<div id="saveReportDiv" style="display:none;">
    <div class="row">
        <div class="col-md-12">
        	<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
                <?= $form->field($rmodel, 'alias_name')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($rmodel, 'paixu')->textInput(['maxlength' => true]) ?>
   
    		<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php 
$durl = \yii\helpers\Url::to(['collection']);
$durl1 = \yii\helpers\Url::to(['set-collection']);
$script = <<<eot
$(document).on('click','a[data-toggle="collection"],a[data-toggle="cancel"]',function(e,state){
    var box,t = $(this),
        reportId = t.attr('report-id'),
        setId = t.attr('set-id'),
        userReportId = t.attr('user-report-id'),
        userSetId = t.attr('user-set-id');

    if(reportId || setId){
        box = bootbox.dialog({
            message: $('#saveReportDiv').html(),
            title: '保存报表',
            className: 'modal-primary',
            buttons: {
                success: {
                    label: '保存',
                    className: 'btn-primary',
                    callback: function(e){
                        var searchParmas = t.closest('.data-report-index').find('.dataconter-search form').serializeJson(),
                            params = box.find('form').serializeJson();
                        params.reportId = reportId||'';
                        params.setId = setId||'';
                        params.userReportId = userReportId||'';
                        params.userSetId = userSetId||'';
                        $.extend(params, searchParmas);
                        $.getJSON((reportId ? "{$durl}" : "{$durl1}"),params,function(json){
                            if(json.success){
                                Notify((json.msg || '操作成功'), 'top-right', '5000', 'success', 'fa-check', true);
                            }else{
                                Notify((json.msg || '操作失败'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                            }
                        });
                    }
                }
            }
        });
    }else if(userReportId || userSetId){
        $.getJSON((userReportId ? "{$durl}" : "{$durl1}"),{
            userReportId:(userReportId||''),
            userSetId:(userSetId||'')
        },function(json){
            if(json.success){
                Notify((json.msg || '操作成功'), 'top-right', '5000', 'success', 'fa-check', true);
            }else{
                Notify((json.msg || '操作失败'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
            }
        });
    }
});
eot;
$this->registerJs($script);
?>