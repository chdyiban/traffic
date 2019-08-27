define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'traffic/project/index' + location.search,
                    add_url: 'traffic/project/add',
                    edit_url: 'traffic/project/edit',
                    del_url: 'traffic/project/del',
                    multi_url: 'traffic/project/multi',
                    table: 'traffic_project_list',
                }
            });

            var table = $("#table");

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
                        {field: 'ID', title: __('Id'), operate: false,},
                        {field: 'getywdwyhname.nickname', title: __('业务单位用户'),searchList: $.getJSON('traffic/project/getYwdwyhNicknameJson'),},
                        {field: 'XMBH', title: __('项目编号'), operate:'LIKE %...%',},
                        {field: 'XMJSMC', title: __('项目建设名称'), operate:'LIKE %...%',},
                        {field: 'XTMC', title: __('系统名称'), operate:'LIKE %...%',},
                        {field: 'KFDW', title: __('开发单位'), operate:'LIKE %...%',},
                        {field: 'JSDW', title: __('建设单位'), operate:'LIKE %...%',},
                        {field: 'GLWHDW', title: __('管理维护单位'), operate:'LIKE %...%',},
                        {field: 'JLDW', title: __('监理单位'), operate:'LIKE %...%',},
                        {field: 'getxmyhname.nickname', title: __('项目用户'),formatter:function(value,row){
							if(value == null) 
								return "未分配";
							else
								return value;
                        }, searchList: $.getJSON('traffic/project/getXmyhNicknameJson'),},
						{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
						buttons: [
							{name: 'uploda', text: __('资料管理'), classname: 'btn btn-xs btn-primary btn-success btn-upload  btn-dialog', url: 'traffic/project/upload', callback: function (data){}},      
						],  
						formatter: Table.api.formatter.operate}
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
        //上传文件界面
        upload: function () {
            Form.api.bindevent($("form#fileForm"));

            //点击上传按钮
            $(document).on('click', '.btn-file-submit', function () {
                var ID = $("#ID").val();
                var SJZD = $("#c-SJZD").val();
                var database = $("#c-database").val();
                if (database == "" || SJZD == "") {
                    alert("请上传文件");
                } else { 
                    $.ajax({
                        type: 'POST',
                        url: './traffic/project/upload?ids='+ID,
                        data: {
                            'ID'         : ID,
                            'SJZD'       : SJZD,
                            'database'   : database,
                        },
                        success: function(data) {
                            if (data == 1) {
                                Fast.api.close();
                                alert('上传成功');
                                window.parent.location.reload();  
                            } else {
                                alert('操作有误，请确认');
                            }
                        }
                    });
                }
            }); 
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});