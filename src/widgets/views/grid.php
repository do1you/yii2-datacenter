<?php 
$columns = $model->getV_columns();
?>
<div class="table-scrollable-debug">
    <table class="table table-striped table-bordered table-hover table-nowrap notFix" id="<?php echo $id?>">
    	<thead></thead>
    	<tbody></tbody>
    	<?php if(($totalRow = $model['v_summary']) && !empty($columns)):?>
    	<tfoot>
    		<tr class="success">
    		<?php foreach($columns as $col):?>
    			<td><?php echo (isset($totalRow[$col['name']]) ? $totalRow[$col['name']] : '');?></td>
    		<?php endforeach;?>
    		</tr>
    	</tfoot>
    	<?php endif;?>
    </table>
</div>

<?php
// 组装字段
$colModel = [];
if($columns){
    foreach($columns as $col){
        $colModel[] = [
            'colnmnId' => $col['id'],
            'data' => $col['name'],
            'name' => $col['label'],
            'bSortable' => $col['order'],
            //'defValue' => '',
            'title' => $col['label'],
            'sClass' => 'left'
        ];
    }
}
$colModel = json_encode($colModel);
$pageSize = $model->getPagination()->getPageSize();
$pageStart = $model->getPagination()->getPage();
$fixedScript = $model['v_frozen']>0 ? 'new $.fn.dataTable.FixedColumns(table,{"iLeftColumns":'.$model['v_frozen'].'});' : '';
$reorderState = $this->context && $this->context->isCache===false ? 'true' : 'false'; // 'table-tool-cus'
$script = <<<eot
(function(){
var colModel = {$colModel};
var draw,table = $("#{$id}").dataTable({
	"sDom": "<B>t<'row'<'col-xs-12 col-md-5 margin-top-10'<'pull-left'l><'pull-left margin-pageing'i>><'col-xs-12 col-md-7 margin-top-10'p>>",
    "buttons": ['colvis'],
    "initComplete": function(){ $('.data-report-row').triggerHandler('relad.layout'); },
    "processing" : true,
	"searching" : false,
	"serverSide" : true,
	"bPaginate" : true,
	"bAutoWidth" : true,
	"colReorder" : ({$reorderState} ? {"iFixedColumnsLeft":1,"reorderCallback":function(){this.reportId = "{$model['id']}"; $(document).triggerHandler('dataTable.colReorder',this); }} : true),
    "stateSave": true,
    "sScrollX" : '100%',
    "sScrollY" : ($(window).height()-280),
    "bScrollCollapse" : true,
    //"sScrollXInner" : '150%',
    "displayStart" : {$pageStart},
    "iDisplayLength" : {$pageSize},// 每页显示行数
    "aLengthMenu": [ 10,20,30,50,100,200,250,500 ],
	"order" : [], // [ 1, "desc" ]
	"ajax": {
		"type" : "GET",
		"url" : '{$apiUrl}',
		"dataType": "json",
        "dataSrc": function(json){
            json = $.extend(json,{
                'draw' : (draw || 1),
                'page' : json.data.pages.currentPage,
                'total' : json.data.pages.pageCount,
                'recordsTotal' : json.data.pages.totalCount,
                'recordsFiltered' : json.data.pages.totalCount
            });
            return json.data.rows;
        },
		"data" : function(data){
            draw = data.draw;
			data['page'] = (data.start / data.length) + 1;
            data['per-page'] = data.length;
			delete data.search;
			delete data.start;
            delete data.length;
			delete data.columns;
			if(data.order && data.order[0]){
				data.sort = '' + (data.order[0].dir=='desc' ? '-' : '') + colModel[data.order[0].column].data;
				delete data.order;
			}
		}
	},
	"sAjaxDataProp" : 'rows',
	"columns": colModel
});
{$fixedScript}
table.api().settings()[0].reportId = "{$model['id']}"; // 记录报表ID
})();
eot;
$this->registerJs($script);
?>