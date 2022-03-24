<?php echo $this->render('view', [
    'list' => $list,
    //'cache' => false,
]);?>

<?php 
$durl = \yii\helpers\Url::to(['collection']);
$script = <<<eot
$(document).on('click','a[data-toggle="collection"]',function(e,state){
    var t = $(this),
        reportId = t.attr('report-id');
    reportId && $.getJSON("{$durl}",{id:reportId,show:(state==99?'1':'')},function(json){
        if(json.success){
            t.find('i').removeClass('fa-star fa-star-o').addClass(json.state=='1' ? 'fa-star' : 'fa-star-o');
            state!=99 && Notify((json.msg || '操作成功'), 'top-right', '5000', 'success', 'fa-check', true);
        }else{
            state!=99 && Notify((json.msg || '操作失败'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
        }
    });
});
setTimeout(function(){ $('a[data-toggle="collection"]').trigger('click',99);}, 1000);
eot;
$this->registerJs($script);
?>