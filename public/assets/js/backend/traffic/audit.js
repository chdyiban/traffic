define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'traffic/audit/index' + location.search,
                    // add_url: 'traffic/audit/add',
                    // edit_url: 'traffic/audit/edit',
                    // del_url: 'traffic/audit/del',
                    multi_url: 'traffic/audit/multi',
                    table: 'traffic_apply_list',
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
                        {field: 'ID', title: __('ID'),operate:false,visible:false,},
                        {field: 'BZMC', title: __('标准名称'),operate:'LIKE %...%'},
                        {field: 'YDJB', title: __('宜定级别')},
                        {field: 'XDND', title: __('修订年度')},
                        {field: 'XDMD', title: __('修订目的'),operate:false,},
                        {field: 'XDYJ', title: __('修订依据'),operate:'LIKE %...%',},
                        {field: 'getadminname.nickname', title: __('申请人')},
                        {field: 'step', title: __('Step'), searchList: {"0":__('Step 0'),"1":__('Step 1'),"2":__('Step 2'),"3":__('Step 3'),"4":__('Step 4'),"5":__('Step 5')}, formatter: Table.api.formatter.normal},
                        {field: 'status_admin', title: __('是否待审'), searchList: {"0":__('status_admin 0'),"1":__('status_admin 1')}, formatter: Table.api.formatter.normal},
                        {field: 'status_user', title: __('是否待提交'), searchList: {"0":__('status_user 0'),"1":__('status_user 1')},  formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                        buttons: [
							{name: 'detail', text:"审核管理",classname: 'btn btn-xs btn-primary btn-success btn-detail  btn-dialog', url: 'traffic/audit/detail', callback: function (data){}},      
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

        detail: function () {
            Form.api.bindevent($("form#userForm"));

            $(".option").change(function(){ 
                var checkOpinion = $('input:radio:checked').val();
                if (checkOpinion == 0) {
                    $('#hiddenReason').addClass("hidden");
                    $('#hiddenAdvice').addClass("hidden");
                } else if (checkOpinion == 1) {
                    $('#hiddenReason').removeClass("hidden");
                    $('#hiddenAdvice').addClass("hidden");
                } else if (checkOpinion == 2) {
                    $('#hiddenReason').addClass("hidden");
                    $('#hiddenAdvice').removeClass("hidden");
                }
            });

            //这里需要手动为Form绑定上元素事件
            // Form.api.bindevent($("form#cxselectform"));
            //管理员提交
            $(document).on('click', '.btn-admin-submit', function () {
                var ids = $("#id").val();
                var step = $("#step").val();
                var reason = $("#refuseReason").val();
                var advise = $("#advise").val();
                var opinion = $('input:radio:checked').val();
                if (opinion == 1 && reason == "" || opinion == 2 && advise == "") {
                    alert("请填写有关情况说明");
                } else {
                    $.ajax({
                        type: 'POST',
                        url: './traffic/audit/submit',
                        data: {
                            'ID'         : ids,
                            'opinion'    : opinion,
                            'advise'     : advise,
                            'reason'     : reason,
                            'step'       : step,
                            'user'       : "admin",
                        },
                        success: function(data) {
                            if (data == 1) {
                                Fast.api.close();
                                alert('审核成功');
                                window.parent.location.reload();  
                            } else {
                                alert('操作有误，请确认');
                            }
                        }
                    });
                }
            }); 
            

            //用户在立项时提交
            
            $(document).on('click', '.btn-user-step1-submit', function () {
                //项目申报书
                var XMSBS = $("#c-XMSBS").val();
                //草案
                var CA = $("#c-CA").val();
                if (XMSBS == "" || CA == "") {
                    alert('请上传相关文件');
                } else {
                    var ids = $("#id").val();
                    var step = $("#step").val();
                    $.ajax({
                        type: 'POST',
                        url: './traffic/audit/submit',
                        data: {
                            'ID'         : ids,
                            'XMSBS'      : XMSBS,
                            'CA'         : CA,
                            'step'       : step,
                            'user'       : "user",
                        },
                        success: function(data) {
                            if (data == 1) {
                                Fast.api.close();
                                alert('提交成功，请等待审核');
                                window.parent.location.reload();  
                            } else {
                                alert('操作有误，请确认');
                            }
                        }
                    });
                }
            }); 


            //用户在征求意见阶段提交文件
            $(document).on('click', '.btn-user-step2-submit', function () {
                //征求意见稿
                var ZQYJG = $("#c-ZQYJG").val();
                //编制说明
                var BZSM = $("#c-BZSM").val();
                if (ZQYJG == "" || BZSM == "") {
                    alert('请上传相关文件');
                } else {
                    var ids = $("#id").val();
                    var step = $("#step").val();
                    $.ajax({
                        type: 'POST',
                        url: './traffic/audit/submit',
                        data: {
                            'ID'         : ids,
                            'ZQYJG'      : ZQYJG,
                            'BZSM'       : BZSM,
                            'step'       : step,
                            'user'       : "user",
                        },
                        success: function(data) {
                            if (data == 1) {
                                Fast.api.close();
                                alert('提交成功，请等待审核');
                                window.parent.location.reload();  
                            } else {
                                alert('操作有误，请确认');
                            }
                        }
                    });
                }
            }); 
            //用户在送审阶段提交文件
            $(document).on('click', '.btn-user-step3-submit', function () {
                //送审稿
                var SSG = $("#c-SSG").val();
                //编制说明
                var BZSM = $("#c-BZSM").val();
                if (SSG == "" || BZSM == "") {
                    alert('请上传相关文件');
                } else {
                    var ids = $("#id").val();
                    var step = $("#step").val();
                    $.ajax({
                        type: 'POST',
                        url: './traffic/audit/submit',
                        data: {
                            'ID'         : ids,
                            'SSG'        : SSG,
                            'BZSM'       : BZSM,
                            'step'       : step,
                            'user'       : "user",
                        },
                        success: function(data) {
                            if (data == 1) {
                                Fast.api.close();
                                alert('提交成功，请等待审核');
                                window.parent.location.reload();  
                            } else {
                                alert('操作有误，请确认');
                            }
                        }
                    });
                }
            }); 

            //用户在报批阶段提交文件
            $(document).on('click', '.btn-user-step4-submit', function () {
                //报批稿
                var BPG = $("#c-BPG").val();
                //编制说明
                var BZSM = $("#c-BZSM").val();
                if (BPG == "" || BZSM == "") {
                    alert('请上传相关文件');
                } else {
                    var ids = $("#id").val();
                    var step = $("#step").val();
                    $.ajax({
                        type: 'POST',
                        url: './traffic/audit/submit',
                        data: {
                            'ID'         : ids,
                            'BPG'        : BPG,
                            'BZSM'       : BZSM,
                            'step'       : step,
                            'user'       : "user",
                        },
                        success: function(data) {
                            if (data == 1) {
                                Fast.api.close();
                                alert('提交成功，请等待审核');
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