<?php 
use webadmin\widgets\ActiveForm;
?>
<?php echo $this->render('view', [
    'list' => $list,
    //'cache' => false,
]);
$rmodel = new \datacenter\models\DcUserReport;
$rmodel->loadDefaultValues();
$smodel = new \datacenter\models\DcShare;
$smodel->loadDefaultValues();
$smodel->invalid_time = date('Y-m-d H:i:s',(time()+3600*24*3));
?>

<div id="hiddenReportDiv" style="display:none;">
    <div class="row">
        <div class="col-md-12">
        	<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
                
                <?= $form->field($smodel, 'user_ids')->selectajaxmult(\yii\helpers\Url::toRoute('user'),[]) ?>
                
                <?= $form->field($smodel, 'invalid_time')->datetime([]) ?>
   
    		<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div id="saveReportDiv" style="display:none;">
    <div class="row">
        <div class="col-md-12">
        	<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
                <?= $form->field($rmodel, 'alias_name')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($rmodel, 'paixu')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($rmodel, 'is_new')->textInput(['maxlength' => true])->switchs() ?>
   
    		<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div id="shareReportDiv" style="display:none;">
    <div class="row">
        <div class="col-md-12">
        	<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'enableAjaxValidation'=>false]); ?>
                <?= $form->field($smodel, 'alias_name')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($smodel, 'user_ids')->dropDownList([],['multiple'=>'multiple','style'=>'width:100%']) ?>
                
                <?= $form->field($smodel, 'password')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($smodel, 'invalid_time')->textInput(['maxlength' => true]) ?>
                
    		<?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php 
$durl = \yii\helpers\Url::to(['collection']);
$durl1 = \yii\helpers\Url::to(['set-collection']);
$durl2 = \yii\helpers\Url::to(['user']);
$durl3 = \yii\helpers\Url::to(['share-view/view']);
$script = <<<eot
$(document).on('click','a[data-toggle="collection"],a[data-toggle="share"],a[data-toggle="cancel"]',function(e,state){
    var box,t = $(this),
        reportId = t.attr('report-id'),
        setId = t.attr('set-id'),
        userReportId = t.attr('user-report-id'),
        userSetId = t.attr('user-set-id'),
        beforeTitle = t.attr('before-title'),
        isShare = t.is('a[data-toggle="share"]');

    if(reportId || setId){
        box = bootbox.dialog({
            message: (isShare ? $('#shareReportDiv').html() : $('#saveReportDiv').html()),
            title: (isShare ? '分享报表' : '保存报表'),
            className: 'modal-primary',
            buttons: {
                success: {
                    label: '保存',
                    className: 'btn-primary',
                    callback: function(e){
                        var searchParmas = t.closest('.data-report-index').find('.dataconter-search form').serialize(),
                            params = box.find('form').serialize() + (searchParmas ? '&' + searchParmas : '');
                        if(isShare) params += "&share=9";
                        if(reportId) params += "&reportId=" + reportId;
                        if(setId) params += "&setId=" + setId;
                        if(userReportId) params += "&userReportId=" + userReportId;
                        if(userSetId) params += "&userSetId=" + userSetId;
                        $.getJSON((reportId ? "{$durl}" : "{$durl1}"),params,function(json){
                            if(json.success){
                                if(json.url){
                                    bootbox.alert('分享成功！<br>链接地址：<a href="'+json.url+'" target="_blank">'+json.url+'</a><br>访问密码：'+(json.password || '不需要密码'));
                                }else{
                                    Notify((json.msg || '操作成功'), 'top-right', '5000', 'success', 'fa-check', true);
                                }
                            }else{
                                Notify((json.msg || '操作失败'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                            }
                        });
                    }
                }
            }
        });
        beforeTitle && box.find('#dcuserreport-alias_name,#dcusersets-alias_name,#dcshare-alias_name').val(beforeTitle);
        if(isShare){
            box.find('#dcshare-user_ids').select2({
                 ajax: {
                     type:'GET',
                     url: '{$durl2}',
                     dataType: 'json',
                     delay: 250,
                     data: function (params) {
                         return {q: params.term,page: params.page};
                     },
                     processResults: function (data, params) {
                         params.page = params.page || 1;
                         return {
                             results: data.items,
                             pagination: {
                                 more: params.page < data.total_page
                             }
                         };
                     },
                     cache: true
                 },
                 placeholder:'请选择',
                 closeOnSelect:false,
                 language: 'zh-CN',
                 tags: false,
                 allowClear: true,
                 escapeMarkup: function (m){ return m; },
                 minimumInputLength: 0,
                 formatResult: function formatRepo(r){return r.text;},
                 formatSelection: function formatRepoSelection(r){return r.text;}
            });

            box.find('#dcshare-invalid_time').datetimepicker();
        }else{
            if(userReportId || userSetId){
                box.find('#dcuserreport-is_new').closest('.form-group').show();
            }else{
                box.find('#dcuserreport-is_new').closest('.form-group').hide();
            }
        }
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