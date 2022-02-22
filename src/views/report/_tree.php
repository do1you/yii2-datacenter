<div id="report_tree" class="tree tree-solid-line tree-unselectable">
    <div class="tree-folder" style="display: none;">
        <div class="tree-folder-header">
            <i class="fa fa-folder"></i>
            <div class="tree-folder-name" style="display:inline-block"></div>
        </div>
        <div class="tree-folder-content">
        </div>
        <div class="tree-loader" style="display: none;"></div>
    </div>
    <div class="tree-item" style="display: none;">
        <i class="tree-dot"></i>
        <div class="tree-item-name"></div>
    </div>
</div>

<div id="context-menu">
  	<ul class="dropdown-menu" role="menu">
        <li><a tabindex="-1" class="frozen">冻结/取消</a></li>
        <li class="divider"></li>
        <li><a tabindex="-1" class="create">创建列</a></li>
        <li class="divider"></li>
        <li><a tabindex="-1" class="update">修改列</a></li>
        <li class="divider"></li>
        <li><a tabindex="-1" class="remove">删除列</a></li>        
  	</ul>
</div>                               
                                    
<?php 
$treeData = \datacenter\models\DcCat::treeData();
$url = \yii\helpers\Url::to(['sets']);
$url1 = \yii\helpers\Url::to(['column']);
$url2 = \yii\helpers\Url::to(['save']);
$url3 = \yii\helpers\Url::to(['report']);
$url4 = \yii\helpers\Url::to(['report-col']);
$items = json_encode($treeData);
$this->registerJsFile('@assetUrl/js/fuelux/treeview/tree-custom.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
$this->registerJsFile('@assetUrl/js/bootstrap-contextmenu/bootstrap-contextmenu.js',['depends' => \webadmin\WebAdminAsset::className()]);
$this->registerJs("
// 树型控件
$('#report_tree').tree({
    selectable: false,
    dataSource: {
        data : function(options, callback) {
		    if(! ('text' in options) && !('type' in options)) { // 根节点
                callback({ data: {$items} });
		    } else if (('type' in options) && options.type=='folder') { // 子节点
                var isSets = options.sid=='1' ? true : false;
                $.getJSON((isSets ? '{$url1}' : '{$url}'),{mId:options.id},function(json){
                    var children = options.children || [];
                    if(json && json.items){
                        $.each(json.items, function(index,item){
                            children.push({
                                id : item.id,
                                name : item.text,
                                'icon-class':(isSets ? '' : 'themeprimary set-folder'),
                                sid : (isSets ? '2' : '1'),
                                type : (isSets ? 'item' : 'folder')
                            });
                        });
                    }
                    callback({ data: children });
                });
		    }
		}
    },
    loadingHTML: '<div class=\"tree-loading\"><i class=\"fa fa-rotate-right fa-spin\"></i></div>'
});

// 拖拉显示报表
var hasTouch = 'ontouchstart' in document,
    moveStatus,startX,startY,moveEl,offset,isFolder,itemData,pointEl,
    getx = function(e){
		return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientX : e.clientX;
	},
    gety = function(e){
		return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientY : e.clientY;
	},
    onStartEvent = function(e){ // 拖动前
        var target = $(e.target).closest('.tree-item,.tree-folder-header');
        isFolder = target.is('.tree-folder-header');
        itemData = target.data();
        if(target && target.length && (!isFolder || target.find('.set-folder').length) && itemData && itemData.id){
            moveStatus = target;
            moveEl = target.clone();
            startX = getx(e);
            startY = gety(e);
            offset = moveStatus.offset();
            $('body').append(moveEl);
            return false;
        }
    },
    onMoveEvent = function(e){ // 拖动中
        if(!moveStatus) return;
		var x = getx(e)-startX,
            y = gety(e)-startY;
        moveEl.css({
            'top'     : (offset.top+y),
            'left'     : (offset.left+x),
            'position' : 'absolute'
        })[0].style.visibility = 'hidden';
        pointEl = $(document.elementFromPoint(e.pageX - document.body.scrollLeft, e.pageY - (window.pageYOffset || document.documentElement.scrollTop)));
        moveEl[0].style.visibility = 'visible';
        window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty(); 
        return false;
    },
    onEndEvent = function(e){
        moveEl && moveEl.remove();
        $('body>.mouseover').remove();
        if(!moveStatus) return;

        if(itemData.id && pointEl){
            var boxEl = pointEl.closest('.data-report-index');
            boxEl = boxEl.length>0 ? boxEl : pointEl.closest('.report_box_right');
            if(boxEl && boxEl.length>0){
                $.getJSON('{$url2}',{rid:(boxEl.attr('rid') || ''),type:itemData.sid,id:itemData.id},function(json){
                    if(json.success){
                        $('#report_div').load('{$url3}');
                    }else{
                        Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                    }
                });
            }
        }
        
        moveStatus = null;
        itemData = null;
        pointEl = null;

        return false;
    };
if(hasTouch){
    $('#report_tree').on('touchstart',onStartEvent);
    $(window).on('touchmove', onMoveEvent);
    $(window).on('touchend', onEndEvent);
    $(window).on('touchcancel', onEndEvent);
}
$('#report_tree').on('mousedown', onStartEvent);
$(window).on('mousemove', onMoveEvent);
$(window).on('mouseup', onEndEvent);

// 删除报表
$('#report_div').on('click', '[data-toggle=dispose]', function(){
    var boxEl = $(this).closest('.data-report-index'),
        rid = boxEl.length ? boxEl.attr('rid') : '';
    if(rid){
        $.getJSON('{$url2}',{rid:rid,type:3},function(json){
            if(json.success){
                $('#report_div').load('{$url3}');
            }else{
                Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
            }
        });
    }else{
        $('#report_div').load('{$url3}');
    }
});

// 保存报表
$('#report_div').on('click', '[data-toggle=save]', function(){
    var boxEl = $(this).closest('.data-report-index'),
        rid = boxEl.length ? boxEl.attr('rid') : '',
        box = rid ? bootbox.dialog({
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
                            url: '{$url2}?rid='+rid+'&type=4', 
                            type: 'POST',
                            data: params, 
                            dataType: 'json',
                            success: function(json){
                                if(json.success){
                                    $('#report_div').load('{$url3}');
                                }else{
                                    Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                                }
                			}
                        });
                    }
                }
            }
        }) : null;
});

//字段排序
$(document)
	.on('dataTable.colReorder', function(e,a){
        if(a && a.s && a.s.dt && a.s.dt.aoColumns && a.reportId){
            var nextId,targetIndex = a.s.mouse.targetIndex,
                toIndex = a.s.mouse.toIndex;
            if(a.s.dt.aoColumns[toIndex]){
                nextId = a.s.dt.aoColumns[toIndex+1] ? a.s.dt.aoColumns[toIndex+1].colnmnId : '';
                $.getJSON('{$url2}',{rid:a.reportId,type:5,id:a.s.dt.aoColumns[toIndex].colnmnId,nid:nextId},function(json){
                    if(!json.success){
                        $('#report_div').load('{$url3}');
                        Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                    }
                });
            }
        }
	});

// 右键点击冻结和删除列
$(document).contextmenu({
    scopes:'.dataTables_scrollHeadInner,.DTFC_LeftHeadWrapper',
    target:'#context-menu',
    before: function(e,context) {
        var el = $(e.target); 
        if(!el.is('[data-column-index]')){
            return false;
        }
        context.data('target',el);
    },
    onItem: function(context,e) {
        var target,table,api,index,settings,column,reportId,el = context.data('target') || '';
        if(el && (table = el.closest('.dataTables_wrapper').find('.dataTables_scrollBody table.table')) && (api = table.dataTable().api())){
            index = el.attr('data-column-index');
            settings = api.settings()[0];
            column = settings.aoColumns[index];
            reportId = settings.reportId;
            target = $(e.target);
            if(column && column.colnmnId && reportId){
                // 添加修改列
                if(target.is('.update,.create')){
                    var box = bootbox.dialog({
                        message: '<div id=\'saveReportColDiv\'></div>',
                        title: target.text(),
                        className: 'modal-primary',
                        buttons: {
                            success: {
                                label: '保存',
                                className: 'btn-primary',
                                callback: function(e){
                                    var params = box.find('form').serializeJson();
                                    $.ajax({
                                        url: '{$url2}?id='+colnmnId+'&rid='+reportId+'&type=8', 
                                        type: 'POST',
                                        data: params, 
                                        dataType: 'json',
                                        success: function(json){
                                            if(json.success){
                                                $('#report_div').load('{$url3}');
                                            }else{
                                                Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                                            }
                            			}
                                    });
                                }
                            }
                        }
                    }),colnmnId = (target.is('.update') ? column.colnmnId : '');
                    $('#saveReportColDiv').load('{$url4}?id='+colnmnId);
                }else{
                    // 冻结/删除列
                    $.getJSON('{$url2}',{id:column.colnmnId,rid:reportId,type:(target.is('.frozen') ? 6 : 7)},function(json){
                        if(json.success){
                            $('#report_div').load('{$url3}');
                        }else{
                            Notify((json.msg || json.message || '操作失败！'), 'top-right', '5000', 'darkorange', 'fa-warning', true);
                        }
                    });
                }
            }
        }
    }
});

");


?>