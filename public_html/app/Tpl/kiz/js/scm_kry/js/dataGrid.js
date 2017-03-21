(function ($) {
    $.fn.dataGrid = function (opts) {
        return new dataGrid(this, opts);
    };

    //刷新表格
    $.fn.refresh = function (toPage) {
        //如果datatype为local修改为默认的json
        if (toPage == undefined || toPage == '') {
            toPage = -1;
        }
        this.jqGrid('setGridParam', {page: toPage, datatype: 'json'}).trigger('reloadGrid');
    };

    function dataGrid(element, opts) {
        return this._init(element, opts);
    }

    dataGrid.prototype = {
        _init: function (element, opts) {
            var _this = this;

            this.opts = $.extend(true, {}, this.defaultOpts, opts || {});
            if (this.opts.showOperate) {
            	var buttonGroupCode = $("#button_group_code").val();
            	if(buttonGroupCode){
            		this.opts.buttonGroupCode = buttonGroupCode.substring(1,buttonGroupCode.length-1).split(",");
            	}
                this.getActionOrder(opts);
                _this.setOperateColumns(element);
            }

            /* this.opts.gridComplete = function () {
             _this.gridComplete(_this.opts, this);
             };*/


            /*            this.opts.beforeRequest = function () {
             _this.beforeRequest(_this.opts, this);
             };
             this.opts.beforeProcessing = function () {
             _this.beforeProcessing(_this.opts, this);
             };*/
            return $(element).jqGrid(this.opts).navGrid(this.opts.pager, this.opts.navGrid);
        },
        defaultOpts: {
            // 基础设置 begin
            width: 900,
            height: 435,
            autowidth: true,
            gridview: true,
            altRows: true,
            showEmptyGrid: false,
            shrinkToFit: true,
            // 基础设置 end

            // 数据加载 begin
            formId: '',
            datatype: 'json',
            mtype: 'post',
            jsonReader: {
                root: 'dataList',
                repeatitems: false,
                id: 'id'
            },
            localReader: {
                repeatitems: false,
                id: 'id'
            },
            // 数据加载 end

            // 列设置 begin
            colNames: [],
            colModel: [],
            // showOperate: true,
            // operateColName: '操作',
            // operateColumns: {
            //     cloName: '',
            //     model: {
            //         name: 'ACTION',
            //         width: 130,
            //         title: false,
            //         align: "center",
            //         sortable: false,
            //         formatter: null,
            //         actionParam: null
            //     }
            // },
            //默认的操作状态,根据定义的顺序显示
            actionParam: {
                delete: {
                    url: '', code: '', title: '删除'
                },
                view: {
                    url: '', code: '', title: '查看'
                },
                confirm: {
                    url: '', code: '', title: '确认'
                },
                editor: {
                    url: '', code: '', title: '编辑'
                },
                clock: {
                    url: '', code: '', title: '停用'
                },
                unlock: {
                    url: '', code: '', title: '启用'
                },
                print: {
                    url: '', code: '', title: '打印'
                },
                export: {
                    url: '', code: '', title: '导出'
                },
                purchase: {
                    url: '', code: '', title: '转采购', disabledTitle: '已转采购'
                },
                state: {
                    url: '', code: '', title: '查看状态'
                },
                withdraw: {
                    url: '', code: '', title: '反确认'
                },
                copy: {
                	url: '', code: '', title: '复制'
                },
                receipt:{
                    url: '', code: '', title: '收款'
                },
                adjust:{
                    url: '', code: '', title: '调账'
                }
            },
            // 列设置 end

            // 分页 begin
            rowNum: 10,
            rowList: [10, 20, 30, 50, 80],
            page: 1,
            pager: '',
            multiselectWidth: 49,//选择框宽度
            rownumbers: true,
            rownumWidth: 40,//行号宽度
            viewrecords: true,
            // 分页 end
            //工具栏
            navGrid: {
                search: false, edit: false, add: false, del: false
            },
            // 排序 begin
            sortname: '',
            sortorder: "desc",
            // 排序 end

            //事件回调 begin
            beforeRequestCallback: '',
            serializeGridDataCallback: '',
            //事件回调 end

            //子表 begin
            subGrid: false,
            subGridOptions: {
                "plusicon"  : "ui-icon-triangle-1-e",
                "minusicon" : "ui-icon-triangle-1-s",
                "openicon"  : "ui-icon-arrowreturn-1-e"
            },
            //子表 end

            //事件
            beforeRequest: function () {
                var $grid = $(this);

                var $gridDom = $('#gbox_' + $grid.jqGrid('getGridParam', 'id'));
                var $gridDivDom = $gridDom.parent();
                var $notSearch = $gridDom.parent().find(".notSearchContent");

                if ($notSearch.length > 0) {
                    $notSearch.hide();
                    $gridDom.show();
                    $gridDivDom.show();
                }

                //执行回调处理表单数据
                var callback = $grid.jqGrid('getGridParam', 'beforeRequestCallback');
                if ($.isFunction(callback)) {
                    return callback();
                }
            },
            serializeGridData: function (postData) {
                var $grid = $(this);
                var formId = $grid.jqGrid('getGridParam', 'formId');
                if (formId != '' && formId != undefined) {
                    //加入表单数据
                    postData = $.extend(true, {}, postData, $("#" + formId).getFormData() || {});
                }
                var callback = $grid.jqGrid('getGridParam', 'serializeGridDataCallback');
                if ($.isFunction(callback)) {
                    postData = callback(postData);
                }
                return $.param(objectToArray(postData));
            },
            loadComplete: function (data) {

                var $gridObj = $(this);
                //控制是否显示空表头
                if ($gridObj.jqGrid('getGridParam', 'showEmptyGrid')) {
                    return;
                }
                var rowData = $gridObj.getDataIDs();
                var $gridDom = $('#gbox_' + $gridObj.jqGrid('getGridParam', 'id'));
                var $gridDivDom = $gridDom.parent();
                var $notSearch = $gridDom.parent().find(".notSearchContent");

                //后台出现QueryException
                if (!data.status) {
                    layer.alert(data.resMsg || data.message, {offset: '30%'}, function (index) {
                        layer.close(index);
                    });
                    $gridDom.hide();
                    $gridDivDom.hide();
                    return false;
                }

                if (rowData.length > 0) {
                    //表格有数据，显示表格元素，并隐藏无数据提示元素
                    $gridDivDom.show();
                    $gridDom.show();
                    if ($notSearch.length > 0) {
                        $notSearch.hide();
                    }
                    $(".ui-jqgrid-bdiv").scroll();//fix bug 5636
                } else {
                    //表格无数据，隐藏表格自身元素，显示无数据提示元素
                    $gridDom.hide();
                    $gridDivDom.show();
                    if ($notSearch.length > 0) {
                        $notSearch.show();
                    } else {
                        var notData = bkeruyun.notQueryData("没有查到数据，试试其他查询条件吧！");
                        $gridDivDom.append(notData);
                    }
                }
            },
            //显示子表
            subGridRowExpanded: function(subgrid_id, row_id) {
                var $grid = $(this);
                var showSubTable = $grid.jqGrid('getGridParam', 'showSubTable');
                var subgrid_table_id, pager_id;
                subgrid_table_id = subgrid_id+"_t";
                pager_id = "p_"+subgrid_table_id;
                $("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>");

                //增加子表
                if($.isFunction(showSubTable)){
                    var rowData = $grid.jqGrid('getRowData', row_id);
                    showSubTable(subgrid_table_id, pager_id, rowData);
                }
            },
            onSelectAll: function (rowids, status) {
                var $grid = $(this);
                if(status){
                    // uncheck "protected" rows
                    var cbs = $("tr.jqgrow > td > input.cbox:hidden,:disabled", $grid[0]);
                    cbs.removeAttr("checked");

                    //modify the selarrrow parameter
                    $grid[0].p.selarrrow = $grid.find("tr.jqgrow:has(td > input.cbox:checked)")
                        .map(function() { return this.id; }) // convert to set of ids
                        .get(); // convert to instance of Array

                    //deselect disabled rows
                    $grid.find("tr.jqgrow:has(td > input.cbox:hidden,:disabled)")
                        .attr('aria-selected', 'false')
                        .removeClass('ui-state-highlight');
                }
            }
        },
        getActionOrder: function (opts) {
            var index = [], actionParam = this.opts.actionParam;
            for (var item in opts.actionParam) {
                index.push(item);
            }
            for (var item in actionParam) {
                if (index.indexOf(item) === -1) {
                    index.push(actionParam);
                }
            }
            var tempActionParam = {};
            for (var p = 0; p < index.length; p++) {
                var obj = index[p];
                if (actionParam[obj]) {
                    tempActionParam[obj] = actionParam[obj]
                }
            }
            this.opts.actionParam = tempActionParam;
        },
        actionFormatter: function (cellvalue, options, rowObject, element) {
            var actionCol = this.opts.actionParam, str = '';
            var buttonGroupCode = this.opts.buttonGroupCode;//获取用户拥有的按钮权限组集合
            
            for (var col in actionCol) {
                var colVal = actionCol[col];
                var title = colVal.title ? colVal.title : col;

                var render = 0;
                if (colVal.render == undefined || colVal.render == '') {
                    render = 0;
                } else if (colVal.render) {
                    render = colVal.render(rowObject);
                }

                //----------------Jqgrid按钮权限 BEGIN------------------
                /**
                 * 说明：
                 * 1.权限优先级高于数据状态优先级，如果不是隐藏(render=3)则都需要先进行权限判断
                 * 2.防止abc:edit和abc:edit:123同时被indexOf匹配，验证前增加分隔符
                 * 3.理论上没有权限是可以渲染为默认置灰即render=2,但为了定制化显示特增加render=5表示没有权限点击
                 * */
                if(render!=3&&colVal.code&&buttonGroupCode){
                	if(buttonGroupCode.indexOf(colVal.code)==-1) render=5;// 见renderEnum
                }
                //----------------Jqgrid按钮权限  END--------------------
                
                switch (render) {
                    case 0:
                    case 1:
                        //添加操作前回调
                        var callback = actionCol[col].beforeCallback, callbackStr = '';
                        if (callback) {
                            callbackStr = ',beforeCallback:"' + callback + '"';
                        }
                        var id = col + "_" + rowObject.id;
                        var redirectUrl = colVal.redirectUrl ? "\",redirectUrl:\"" + colVal.redirectUrl : '';
                        str += "<a onfocus='this.blur();' href='javascript:void(0);' title='" + actionCol[col].title + "' " +
                            "function='$.do" + UpperFirstLetter(col) + "' " + "id='" + id + "' " +
                            "args='{url:\"" + colVal.url + redirectUrl + "\",dataGridId:\"" + element[0].id + "\",postData:{id:\"" + rowObject.id + "\"}" + callbackStr + ",domId:\"#" + id + "\"}' " +
                            "action='" + col + "' class='icon-" + col + "'>" + actionCol[col].title + "</a>";

                        break;
                    case 2:
                        var title = actionCol[col].disabledTitle ? actionCol[col].disabledTitle : actionCol[col].title;
                        str += "<a title='" + title + "' class='icon-" + col + " icon-" + col + "-disable'>" + title + "</a>";

                        break;
                    case 4:
                        var id = rowObject.id;
                        str += '<a onclick="$.showTracks(' + id + ')" title="查看状态" class="icon-state">查看状态</a>';

                        break;
                    case 5:
                        var title = actionCol[col].authorizeTitle ? actionCol[col].authorizeTitle : actionCol[col].title;
                        str += "<a title='没有" + title + "权限' class='icon-" + col + " icon-" + col + "-disable'>" + title + "</a>";
                        break;
                    default:
                        break;
                }
            }

            return str;
        },
        setOperateColumns: function (element) {
            var _this = this,
                operateColName = _this.opts.operateColName;
            _this.opts.operateColumns.model.formatter = function (cellvalue, options, rowObject) {
                return _this.actionFormatter(cellvalue, options, rowObject, element);
            };
            this.opts.operateColumns.model.actionParam = _this.opts.actionParam;
            if (!operateColName) {
                operateColName = this.opts.operateColumns.cloName;
            }
            this.opts.colNames.push(operateColName);
            this.opts.colModel.push(this.opts.operateColumns.model);
        },
        gridComplete: function (opts, gridElement) {

        }

    }
})(jQuery);

var renderEnum = {
    normal: 1,     //可操作的图标
    disabled: 2,   //置灰的图标
    hidden: 3,     //不显示图标
    state: 4 ,     //显示状态
    permission: 5  //无权限图标(只有为可操作的图标时才进行权限验证，不通过替换为5)
};