<?php 
use webadmin\widgets\ActiveForm;
?>
<?php echo $this->render('view', [
    'list' => $list,
    //'cache' => false,
]);?>

<div id="saveReportDiv" style="display:none;">
    <div class="row">
        <div class="col-md-12">
        	<?php $form = ActiveForm::begin(); ?>
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
    var t = $(this),
        reportId = t.attr('report-id'),
        setId = t.attr('set-id'),
        userReportId = t.attr('user-report-id'),
        userSetId = t.attr('user-set-id');

    if(reportId || setId){
        bootbox.dialog({
            message: $('#saveReportDiv').html(),
            title: '保存报表',
            className: 'modal-primary',
            buttons: {
                success: {
                    label: '保存',
                    className: 'btn-primary',
                    callback: function(e){
                        var params = box.find('form').serializeJson();
                        $.ajax({
                            url: '{$url2}&rid='+rid+'&type=4', 
                            type: 'POST',
                            data: params, 
                            dataType: 'json',
                            success: function(json){
                                if(json.success){
                                    $('#report_div').load('{$url3}');
                                    Notify('操作成功！', 'top-right', '5000', 'success', 'fa-check', true);
                                }else{
                                    Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                                }
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