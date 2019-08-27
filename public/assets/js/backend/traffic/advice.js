define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'traffic/advice/index' + location.search,
                    add_url: 'traffic/advice/add',
                    edit_url: 'traffic/advice/edit',
                    del_url: 'traffic/advice/del',
                    multi_url: 'traffic/advice/multi',
                    table: 'advice_list',
                }
            });

            var table = $("#table");

            table.on('load-success.bs.table', function (e,value,data) {
                valueList = {}
                $.each(value.rows,function (i,v) {
                    valueList[i] = v["content"];
                    // $(this).find("td:eq(2)").html(1);
                })
                $("#table tbody tr").each(function(i,v){
                    console.log(valueList[i]);
                    $(this).find("td:eq(4)").html(valueList[i]);
                    $(this).find("td:eq(4)").attr("style","text-align:left");
                });
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'ID',
                sortName: 'ID',
				showToggle: false,
                // showColumns: false,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'ID', title: __('Id'),operate:false},
                        {field: 'BZMC', title: __('标准名称'),operate: 'LIKE %...%'},
                        {field: 'ZTBH', title: __('章条编号'),operate: 'LIKE %...%'},
                        {field: 'content', title: __('修改意见或内容'),operate:false, formatter:function(value,row){
								return value;
                        }},
                        {field: 'getadminname.nickname', title: __('提出人'),searchList: $.getJSON('traffic/advice/getAdminJson'),},
                        {field: 'getgroupname.name', title: __('组别'), searchList: $.getJSON('traffic/advice/getGroupJson')},
                        {field: 'status', title: __('类别'),searchList: {"0":__('修改'),"1":__('提议') ,"2":__('新增'),"3":__('废止')},formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});