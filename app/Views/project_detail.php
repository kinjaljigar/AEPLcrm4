<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Project: <span id="p_name"><b><?php echo $view_data['project']['p_name']; ?></b></span>
            <a href="<?php echo site_url("home/projects"); ?>" class="btn btn-primary pull-right">Back to Project
                List</a>
        </h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Project Detail</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <?php
                    $file_name = "assets/logos/plogo_" . $view_data['p_id'] . ".jpg";
                    if (!file_exists($file_name)) {
                        $file_name = "assets/logos/plogo_0.jpg";
                    }
                    ?>
                    <div class="col-md-3 col-sm-5 col-xs-12"><img src="<?php echo base_url($file_name); ?>"
                            style="width: 100%;" />
                    </div>
                    <div class="col-md-9 col-sm-7 col-xs-12">
                        <b>Project Name:</b> <?php echo $view_data['project']['p_name']; ?><br />
                        <b>Address:</b> <?php echo $view_data['project']['p_address']; ?><br />
                        <b>Call:</b> <?php echo $view_data['project']['p_contact']; ?><br /><br />
                        <b>Project Details:</b><br />
                        <?php echo $view_data['project']['p_scope']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active" onclick="TabTask()"><a href="#tab_1" data-toggle="tab">Tasks</a></li>
                <li onclick="TabTeam()"><a href="#tab_2" data-toggle="tab">Team</a></li>
                <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
                    <li onclick="TabAccount()"><a href="#tab_3" data-toggle="tab">Accounts</a></li>
                <?php } ?>
                <li onclick="TabVCom()"><a href="#tab_4" data-toggle="tab">Verbal Communication</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <?php if ($view_data['admin_session']['u_type'] == 'Super Admin' || $view_data['admin_session']['u_type'] == 'Master Admin' || $view_data['admin_session']['u_type'] == 'Bim Head') { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <a href="<?php echo site_url("home/task/add/" . $view_data['project']['p_id']); ?>"
                                    class="btn btn-primary pull-right">Add New Task</a>
                            </div>

                        </div> <?php } ?>
                    <div class="row ">
                        <div class="col-lg-12">
                            <table id="dataTableTask" class="table table-bordered table-hover responsive nowrap"
                                width="100% ">
                                <thead>
                                    <tr>
                                        <th>Sr. No</th>
                                        <th>Task Title</th>
                                        <th>Priority</th>
                                        <th>Posted Date</th>
                                        <th>Posted By</th>
                                        <th>Assigned To</th>
                                        <th>Status</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody class="admin_list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab_2">
                    <div class="row ">
                        <div class="col-lg-12">
                            <table id="dataTableTeam" class="table table-bordered table-hover responsive nowrap"
                                width="100% ">
                                <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>Employee Name</th>
                                        <th>Email</th>
                                        <th class="align-right">Hours Worked</th>
                                        <th class="align-right">Salary/hr (As Now)</th>
                                        <th class="align-right">Total Salary</th>
                                    </tr>
                                </thead>
                                <tbody class="admin_list">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="align-right">Total: </th>
                                        <th id="total_sal">0</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <div>
                                <button type="button" id="main_add_button" onclick="sendEmail();"
                                    class="btn btn-primary margin">Send Email</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
                    <div class="tab-pane" id="tab_3">
                        <div class="row ">
                            <div class="col-md-6 col-sm-10 col-xs-12 col-md-offset-3 col-sm-offset-1">
                                <table id="dataTableAccount" class="table table-bordered table-hover responsive nowrap"
                                    width="100% ">
                                    <thead>
                                        <tr>
                                            <th>Detail</th>
                                            <th>Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="admin_list">
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <!-- <th>Net Profit/Loss</th> -->
                                            <th>Total Expense</th>
                                            <th id="total_val" class="align-right" style="padding-right:10px;">0</th>
                                        </tr>
                                    </thead>

                                    <thead>
                                        <tr>
                                            <th>Net Profit/Loss</th>
                                            <th id="total_ProfitLoss" class="align-right" style="padding-right:10px;">0</th>
                                        </tr>
                                    </thead>

                                </table>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="tab-pane" id="tab_4">
                    <div class="row">
                        <div class="col-lg-12">
                            <form name="pv_add_form" id="pv_add_form" method="post" />
                            <div class="form-group">
                                <label for="pv_text">Comments</label>
                                <textarea class="form-control" id="pv_text" name="pv_text" value=""
                                    placeholder="Comments"></textarea>
                            </div>
                            <div>
                                <button type="button" id="main_add_button" onclick="saveVbc();"
                                    class="btn btn-primary margin">Submit</button>
                            </div>

                            <table id="dataTableVerbal" class="table table-bordered table-hover responsive nowrap"
                                width="100% ">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Comment</th>
                                        <th>Posted By</th>
                                    </tr>
                                </thead>
                                <tbody class="admin_list">
                                </tbody>
                            </table>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.tab-content -->
            </div>
            <!-- nav-tabs-custom -->

    </section>

</div><!-- /.content-wrapper -->
<div class="admin_add_modal" style="display: none;">
    <div class="box-body">
        <input type="hidden" name="email_list" id="email_list" />
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="email_subject">Email Subject</label>
                    <input type="text" class="form-control" id="email_subject" name="email_subject"
                        placeholder="Email Subject">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="email_message">Message</label>
                    <textarea type="text" class="form-control" id="email_message" name="email_message"
                        placeholder="Enter your Message" style="height:150px"></textarea>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain();" class="btn btn-primary margin pull-right">Send
            Now</button>
    </div>
</div>
<script>
    var STYPE = '';
    var dataTableTask = null;
    var dataTableTeam = null;
    var dataTableAccount = null;
    var dataTableVerbal = null;
    var p_id = '<?php echo $view_data['p_id']; ?>';
    //var t_id = '<?php echo $view_data['p_id']; ?>';

    function document_ready() {
        TabTask();
    }

    function TabTask() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/tasks'); ?>",
                method: "post",
                data: {
                    "act": "list",
                    "t_p_id": p_id,
                },
            },
            "processing": false,
            "serverSide": false,
            //paging: false,
            //info: false,
            //bLengthChange: false,
            pageLength: 10,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            "columnDefs": [{
                //"targets": [3, 4, 5],
                //"className":"align-right",
            }, ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>records</b>",
            },
        };
        if (dataTableTask != null) {
            dataTableTask.destroy();
        }
        dataTableTask = loadDataTable('#dataTableTask', dtConf);
    }

    function TabTeam() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/projects'); ?>",
                method: "post",
                data: {
                    "act": "teams",
                    "p_id": p_id,
                },
            },
            fnInitComplete: function(oSettings, json) {
                $("#total_sal").html(json.total_val);
            },
            paging: false,
            info: false,
            bLengthChange: false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            "columnDefs": [{
                "targets": [3, 4, 5],
                "className": "align-right",
            }, ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>records</b>",
            },
        };
        if (dataTableTeam != null) {
            dataTableTeam.destroy();
        }
        dataTableTeam = loadDataTable('#dataTableTeam', dtConf);
    }
    <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>

        function numberWithCommas(number) {
            var parts = number.toString().split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return parts.join(".");
        }

        function TabAccount() {
            var cost = "<?php echo $view_data['project']['p_value']; ?>";
            var dtConf = {
                "ajax": {
                    url: "<?php echo base_url('api/projects'); ?>",
                    method: "post",
                    data: {
                        "act": "accounts",
                        "p_id": p_id,
                    },
                },
                fnInitComplete: function(oSettings, json) {
                    $("#total_val").html(json.total_val);
                    var Num = parseFloat(json.total_val.replace(/,/g, ''));
                    var ProfitLoss = cost - Num;
                    ProfitLoss = ProfitLoss.toFixed(2);
                    ProfitLoss = numberWithCommas(ProfitLoss);
                    $("#total_ProfitLoss").html(ProfitLoss);

                },
                paging: false,
                info: false,
                bLengthChange: false,
                pageLength: -1,
                stripeClasses: ['r0', 'r1'],
                bSort: false,
                "columnDefs": [{
                        "targets": [0, 1],
                        "searchable": false,
                        'bSortable': false,
                        "orderable": false,
                    },
                    {
                        "targets": [1],
                        "class": 'align-right',
                    }
                ],
                "oLanguage": {
                    "sEmptyTable": "There is not any <b>records</b>",
                },
            };
            if (dataTableAccount != null) {
                dataTableAccount.destroy();
            }
            dataTableAccount = loadDataTable('#dataTableAccount', dtConf);

        }
    <?php } ?>

    function TabVCom() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/projects'); ?>",
                method: "post",
                data: {
                    "act": "vcom_list",
                    "pv_p_id": p_id,
                },
            },
            paging: false,
            info: false,
            bLengthChange: false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            "columnDefs": [{
                "targets": [0, 1, 2],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }, ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>records</b>",
            },
        };
        if (dataTableVerbal != null) {
            dataTableVerbal.destroy();
        }
        dataTableVerbal = loadDataTable('#dataTableVerbal', dtConf);

    }

    function saveVbc(sType) {
        var rules = {
            pv_text: {
                required: true
            },
        };
        var form = setValidation('#pv_add_form', rules);
        var isValid = form.valid();
        if (isValid == true) {
            var formData = form.serializeArray();
            formData.push({
                name: "act",
                value: "vcom_add",
            });
            formData.push({
                name: "pv_p_id",
                value: p_id,
            });
            doAjax('api/projects', 'post', formData, function(res) {
                if (res.status == "pass") {
                    showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {},
                        function() {
                            form[0].reset();
                            dataTableVerbal.ajax.reload();
                        });
                } else {
                    if (res.type != 'undefined' && res.type == 'popup') {
                        showMessage(res.message, 'admin_add_form', 'error_message', 'danger', true);
                        $('#sbModel').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else {
                        showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                    }
                }
            });
        }
    }

    function sendEmail() {
        var str = '';
        $(".teammet").each(function() {
            if ($(this).is(":checked")) {
                str = str + $(this).val() + ',';
            }
        });
        if (str == '') {
            showModal('ok', "Kindly select team member.", 'Error!', 'modal-danger', 'modal-sm');
            return false;
        }

        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';

        showModal('html', html, 'Send Email', 'modal', 'modal-lg', function() {
            $('#admin_add_form').find('#email_list').val(str);
        });
    }

    function saveMain() {
        var rules = {
            email_subject: {
                required: true
            },
            email_message: {
                required: true
            },

        };
        var form = setValidation('#admin_add_form', rules);
        var isValid = form.valid();
        if (isValid == true) {
            var formData = form.serializeArray();
            formData.push({
                name: "act",
                value: "email"
            });
            doAjax('api/projects', 'post', formData, function(res) {
                if (res.status == "pass") {
                    showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {},
                        function() {
                            form[0].reset();
                            dataTable.ajax.reload();
                        });
                } else {
                    if (res.type != 'undefined' && res.type == 'popup') {
                        showMessage(res.message, 'admin_add_form', 'error_message', 'danger', true);
                        $('#sbModel').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else {
                        showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                    }
                }
            });
        }
    }

    function deleteRecord(id, p_id) {
        showModal('confirm',
            'Are you sure , you want to delete this <b>Task</b>?<br/><br/>Deleting this task will delete all files and messages related to this task.',
            'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/tasks', 'POST', {
                    t_id: id,
                    act: "del"
                }, function(res) {
                    if (res.status == 'pass') {
                        showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                            function() {
                                eval('dataTable' + p_id + '.ajax.reload();');
                            });
                    } else {
                        showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                    }
                });
            });
    }
</script>