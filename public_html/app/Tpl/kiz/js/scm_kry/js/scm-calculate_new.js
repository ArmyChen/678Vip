(function ($) {
    $.fn.dataGridCal = function (opts) {
        return new dataGridCal(this, opts);
    };

    function dataGridCal(element, opts) {
        return this.init(element, opts);
    }

    dataGridCal.prototype = {
        init: function (element, opts) {
            this.opts = $.extend({}, this.defaultOpts, opts || {});
            this.opts.gridId = element[0].id;
            //初始化所有的事件
            this.handleEvent();
        },
        /**
         * 事件管理，将所有的事件绑定都放在此处
         * @private
         */
        handleEvent: function () {
            var _this = this;
            var gridView = 'gview_' + _this.opts.gridId, $grid = $('#' + _this.opts.gridId);

            if ($grid.length == 0) {
                alert('未找到grid，请检查配置');
                return false;
            }
            //解析计算公式
            var isCalculate = _this.opts.formula.length != 0;
            if (!isCalculate) {
                return false;
            }
            var formulas = _this.parseFormula(_this.opts.formula);

            //keyup 计算
            /*$(document).delegate("#" + gridView + " :text", "keyup blur", function (event) {
                    //单元格计算
                    _this.cellCalculate(formulas, this, $grid);
                    //计算合计
                    _this.summaryCalculate(_this.opts, $grid);
                }
            );*/

            //change 计算
            $(document).delegate("#" + gridView + " :text", "input propertychange", function (event) {
                    //单元格计算
                    _this.cellCalculate(formulas, this, $grid);
                    //计算合计
                    _this.summaryCalculate(_this.opts, $grid);
                    //调用自定义函数
                    _this.customerFunc();
                }
            );
        },
        /**
         * 默认参数，将所有默认参数都放在此处
         */
        defaultOpts: {
            gridId: "",
            formula: [],//计算公式
            formulaElement: [],
            formulaLeft: '',
            currencySymbol: '￥',
            //汇总值
            summary: [
                {colModel: 'planMoveQty', objectId: 'qtySum', requirement: ''},
                {colModel: 'amount', objectId: 'amountSum', showCurrencySymbol: true}
            ]
        },

        /**
         * 解析计算公式
         * @param origFormula
         */
        parseFormula: function (origFormula) {
            var formulas = [];
            $.each(origFormula, function (index, object) {
                var formula = {};
                var temp = this.split(/[\+\-\*\/\=]/g);
                formula.operand = temp.slice(0, temp.length - 1);//计算量
                temp = this.split('=');
                //formula.expression = temp[0];//计算
                formula.result = temp[temp.length - 1];//计算结果
                formula.expression = math.compile(temp[0]);//编译表达式
                formulas.push(formula);
            });
            return formulas;
        },

        /**
         * 单元格计算
         * @Method cellCalculate
         * @itemObj
         */
        cellCalculate: function (formulas, cellObj, grid) {
            var mathRepair = this.mathRepair;

            //验收入库单与采购出库单，可输入总价格
            if(formulas[0].result == 'price'){
                if(cellObj.name == 'amount'){
                    formulas = [formulas[0],formulas[2]];
                }else{
                    formulas = [formulas[1],formulas[2]];
                }
            }

            $.each(formulas, function () {
                var formula = this;
                //获取计算列值
                var colModel = grid.jqGrid('getGridParam', 'colModel');

                //查找公式元素对应的列index
                var formulaElementIndex = [];
                $.each(formula.operand, function (index, object) {
                    $.each(colModel, function (colIndex, colObj) {
                        if (colObj.name == object) {
                            formulaElementIndex.push(colIndex);
                        }
                    });
                });
                //从表格获取公式元素的值
                var calObj = {};
                var rowId = cellObj.id.split('_')[0];
                $.each(formulaElementIndex, function (index, object) {
                    var cellValue = mathRepair(grid.jqGrid('getCell', rowId, object));
                    calObj[formula.operand[index]] = math.bignumber(cellValue == '' ? 0 : cellValue);
                });
                //计算列表值
                var result = {};
                result[formula.result] = formula.expression.eval(calObj).toString();
                //当计算结果为无穷大或者非数值时 设置为0
                if(result[formula.result] == 'Infinity' || result[formula.result] == 'NaN'){
                    result[formula.result] = 0;
                }
                //.toFixed(5)数值小数最多5位，四舍五入
                result[formula.result] = $.toFixed(result[formula.result]).toString();
                //将结果转为最多5位小数
                //result.amount = (Math.round((result.amount)*100000)/100000);
                grid.jqGrid('setRowData', rowId, result);
            });
        },

        /**
         * 合计
         * @Method summaryCalculate
         */
        summaryCalculate: function (opts, grid) {
            if (opts.summary.length == 0) {
                return false;
            }
            $.each(opts.summary, function (index, object) {
                var operate = 'sum';
                if (object.operate != '' && object.operate != undefined) {
                    operate = object.operate;
                }
                var showCurrencySymbol = object.showCurrencySymbol;
                var currencySymbol = showCurrencySymbol ? opts.currencySymbol : '';
                $('#' + object.objectId).html(currencySymbol + fmoney($.toFixed(grid.jqGrid('getCol', object.colModel, false, operate, this.requirement))));
            });
        },


        /**
         * 数字修复
         * @Method _mathRepair
         * @value {number/string}
         */
        mathRepair: function (value) {
            var number = 0;
            if (value === '') {
                number = 0;
            } else {//如果以.开始默认为0.;
                if ((value + '').indexOf(".") == 0) {
                    //number = Math.abs(0 + '' + value);
                    number = 0 + '' + value;
                } else {
                    //number = Math.abs(value * 1);
                    number = value;
                }
            }
            return number;
        },


        /**
         * 普通公式计算
         * @param originalFormula 计算公式
         * @param variables 等号左边的每一个变量的值组成的数组，按公式中变量出现的顺序
         * @returns {*}
         */
        normalCalculate: function(originalFormula, variables){

            var _this = this;
            var formula = _this.parseFormula(originalFormula);
            var calObj = {};
            var operand = $(formula).attr('operand');
            $.each(operand, function(index, oper){
                calObj[oper] = math.bignumber(variables[index] == '' ? 0 : variables[index]);
            });
            var result = $(formula).attr('expression').eval(calObj).toString();
            //将结果转为最多5位小数
            result = (Math.round((result)*100000)/100000);
            return result;
        },
        customerFunc : function () {
            if(this.opts.customerFunc && typeof this.opts.customerFunc == 'function'){
                this.opts.customerFunc.call();
            }
        }
    };

    //数值格式化为金额
    function fmoney(s) {
        //判断是否是负数
        var minus = '';
        s += ''; // 数值没有indexOf函数，此处要转化为字符串
        if(s.indexOf('-') >= 0){
            s = s.replace('-','')
            minus = '-';
        }
        s = parseFloat((s + "").replace(/[^\d\.-]/g, "")) + "";
        var l = s.split(".")[0].split("").reverse();
        var r = '';
        if(s.indexOf('.') >= 0){
            r = "." + s.split(".")[1];
        }
        t = "";
        for (i = 0; i < l.length; i++) {
            t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
        }
        return minus + t.split("").reverse().join("") + r;
    }
    //数值限制小数位数，默认5位数
    $.toFixed = function(n,s){
        s = s ? s : 5;
        n = parseFloat(n).toFixed(s);
        return parseFloat(n);
    }
})(jQuery);