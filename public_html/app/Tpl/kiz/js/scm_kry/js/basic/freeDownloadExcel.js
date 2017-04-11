/**
 * 导出excel数据
 * 数据来源于页面jqgrid表格
 * 在需要导出的地方添加<a>标签即可
 * 示例：<a data-title="订单账单明细" data-gridId="data_grid" call-condition="page.queryCondition();" class="FreeDownLoadExcel" href="#">导出</a>
 * data-title  		表示导出文件的名称
 * data-gridId 		jQgrid表Id
 * call-condition   查询条件的回调函数
 * call-head        复合表头的回调函数
 * show-title       是否展示居中样式的标题
 * show-Sum    		是否显示合计
 */
//下载
$("a[name='freeDownExcel']").on("click",function(){
	var current = $(this),
	    title = current.attr("data-title"),
	    gridId = current.attr("data-gridId"),
	    showTitle = current.attr("show-title"),
	    showSumData = current.attr("show-Sum"),
	    downUrl = current.attr('url'),
	    callCondition = current.attr("call-condition"),
	    callHead = current.attr("call-head"),
	    callFormat = current.attr("call-format"),
        tdVisible = current.attr("td-visible"),
        beforeDown = current.attr("before-down");

    //执行导入前检查，返回false则终止导入(注：返回0，''，undefined也会终止导入，若要继续请确保返回true)
    if (!!beforeDown) {
        if(!eval(beforeDown)) {
            return;
        }
    }

    if(!downUrl){
        downUrl = ctxPath + "/report/download/excel";
    } else{
        downUrl = ctxPath + downUrl;
    }
	
	showTitle = showTitle=="true"?true:false;
	showSumData = showSumData=="true"?true:false;
    tdVisible = tdVisible == "false" ? false : true;
	if(callHead) callHead = eval(callHead);
	if(callCondition) callCondition = eval(callCondition);
	if(callFormat) callFormat = eval(callFormat);
	downloadExcelPlugin.downExcelPlus(gridId,title,downUrl, callHead, callCondition, showTitle, showSumData, tdVisible,callFormat);
});

/**
 * grid数据下载增强版
 * domId: grid初始化使用的节点ID
 * title: 下载文档的文件名
 * downUrl: 下载的服务地址
 * */
var downloadExcelPlugin = {};
downloadExcelPlugin.downExcelPlus = function (domId, title, downUrl, head, condition, showTitle, showSumData, tdVisible,format){
    var downFrameId = 'downExcelFrame';
    var $iframe = $('#' + downFrameId);
    var formId = domId + 'downExcelForm';
    var $form = $('#' + formId);

    //取得下载的表数据
    var downData = downloadExcelPlugin.getDownDataPlus(domId, head, condition, showTitle, showSumData, tdVisible);
    //无数据提示
    if(downData.headList == '[]' || downData.dataList == '[]'){
    	// Message.alert({title: "提示", describe: "没有数据！"});
        $.layerMsg('导出记录为空!', false);
    	return false;
    }

    //隐藏frame
    if ($iframe.length == 0) {
        $iframe = $('<iframe id="' + downFrameId + '" class="hide"></iframe>');
        $('body').append($iframe);
    }
    //填充表单
    if ($form.length == 0) {
        var $title = $('<input name="title" type="hidden">');
        var $condition = $('<input name="condition" type="hidden">');
        var $headList = $('<input name="headList" type="hidden">');
        var $dataList = $('<input name="dataList" type="hidden">');
        var $sumList = $('<input name="sumList" type="hidden">');
        var $showTitle = $('<input name="showTitle" type="hidden">');
        
        var $format = $('<input name="format" type="hidden">')
        
        //填充数据
        $title.val(title);
        $condition.val(downData.conditionList);
        $headList.val(downData.headList);
        $dataList.val(downData.dataList);
        $sumList.val(downData.SumDataList);
        $showTitle.val(downData.showTitle);
        $format.val(format);
        
        //提交表单
        $form = $('<form role="downForm" target="downFrame" method="POST" class="hide"></form>');
        $form.attr('id', formId);
        $form.attr('action', downUrl);
        $form.append($title, $condition, $headList, $dataList, $sumList, $showTitle,$format);

        $('body').append($form);
    } else {
        $form.children('input[name="title"]').val(title);
        $form.children('input[name="condition"]').val(downData.conditionList);
        $form.children('input[name="headList"]').val(downData.headList);
        $form.children('input[name="dataList"]').val(downData.dataList);
        $form.children('input[name="sumList"]').val(downData.SumDataList);
        $form.children('input[name="showTitle"]').val(downData.showTitle);
        $form.children('input[name="format"]').val(format);
    }

    //无数据时不传
    if(!downData.SumDataList){
        $form.children('input[name="sumList"]').remove();
    }
    if(!downData.conditionList){
        $form.children('input[name="condition"]').remove();
    }

    $form.submit();
};
//取得grid内的数据
downloadExcelPlugin.getDownDataPlus = function (domId, head, condition, showTitle, showSumData, tdVisible) {
    var headList = []; //表头数据
    var dataList = []; //表内数据
    var conditionList = {}; //查询条件
    var SumDataList = []; //合计数据
    var $grid = $('#gbox_' + domId);
    var $gridTable = $('#' + domId);
    var $tHeadThs = $grid.find('table>thead>tr>th:visible');
    var $tBodyRows = $gridTable.find('tbody>tr');
    var $tFootTds = $('.ui-jqgrid-sdiv').find('table>tbody>tr>td:visible');
    
    //生成表头数据
    if(head == undefined || head == ''){
        //简单一行表头时xuan
        $tHeadThs.each(function () {
            headList.push($(this).text().trim());
        });
    }else{
        //复杂表头时
        headList = head;
    }
    //生成表内数据
    $tBodyRows.each(function (i) {
        if (i == 0) {
            return;
        }
        var rowData = [];
        var tds;
        if (tdVisible) {
            tds = $(this).children('td:visible');
        } else {
            tds = $(this).children('td');
        }
        tds.each(function () {
            rowData.push($(this).text());
        });
        dataList.push(rowData);
    });
    //生成查询条件数据
    if(condition == undefined || condition == ''){
        conditionList = '';
    }else{
        conditionList = condition;
    }
    //显示标题
    showTitle = showTitle ? showTitle : false;
    //生成合计信息
    if(showSumData){
        $tFootTds.each(function () {
            SumDataList.push($.trim($(this).text()));
        });
    }else{
        SumDataList = undefined;
    }

    return {
        headList: JSON.stringify(headList),
        dataList: JSON.stringify(dataList),
        conditionList: JSON.stringify(conditionList),
        showTitle: showTitle,
        SumDataList: JSON.stringify(SumDataList)
    };
};

/**
 * 获取简单的复合表头
 * @param domId
 * @returns {Array}
 */
function getTableHead(domId) {
    var $grid = $('#gbox_' + domId);
    var $tHead = $grid.find('div[class="ui-jqgrid-hbox"]>table>thead');

    var $trs = $tHead.find("tr[role='rowheader']");

    var colIndexes = [];
    for (var i = 0; i < $trs.length; i++) {
        var $ths = $trs.eq(i).find('th:visible');
        var colIndex = [];
        for (var j = 0; j < $ths.length; j++) {
            colIndex.push(j);
        }
        colIndexes.push(colIndex);
    }

    var thObjArray = [];
    for (var k = 0; k < $trs.length; k++) {
        var ths = $trs.eq(k).find('th:visible');
        for (var l = 0; l < ths.length; l++) {
            var th = ths.eq(l);
            var thObj = {};
            var width = 1,
                height = 1,
                col = colIndexes[k][l];

            if (!!th.attr("colspan")) {
                width = parseInt(th.attr("colspan"));
            }
            if (!!th.attr("rowspan")) {
                height = parseInt(th.attr("rowspan"));
            }

            if (width > 1) {
                var colIdx1 = colIndexes[k];
                for (var m = l + 1; m < colIdx1.length; m++) {
                    colIdx1[m] = colIdx1[m] + width - 1;
                }
            }

            if (height > 1) {
                for (var n = 1; n < height; n++) {
                    var colIdx2 = colIndexes[k + n];
                    for (var p = 0; p < colIdx2.length; p++) {
                        var colInx = colIdx2[p];
                        if (colInx >= col) {
                            colIdx2[p] = colInx + width;
                        }
                    }
                }
            }

            thObj.row = k;
            thObj.col = col;
            thObj.width = width;
            thObj.height  = height;
            thObj.content  = th.text().trim();

            thObjArray.push(thObj);
        }
    }

    return thObjArray;
}