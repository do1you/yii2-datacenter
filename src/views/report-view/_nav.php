<!-- 选择报表窗口 -->
<div class="modal fade modal-primary bs-nav-modal" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">报表选择</h4>
            </div>
            <div class="modal-body">加载中...</div>
        </div>
    </div>
</div>
<?php 
$url = \yii\helpers\Url::to(['report-view/index']);
$script = <<<eot
// 快捷进入报表
$('.bs-nav-modal').on('show.bs.modal',function () {
    $(this).find('.modal-body').load('{$url}');
    $(this).off('show.bs.modal');
});
eot;
$this->registerJs($script);
?>