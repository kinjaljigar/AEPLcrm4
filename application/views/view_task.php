<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?php echo $view_data['project']['p_name']; ?>: <?php echo $view_data['task']['t_title']; ?>
            <a href="<?php echo $view_data['return_url']; ?>" class="btn btn-primary pull-right">Back to Tasks</a>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-sm-4">
                <div class="box box-sbpink">
                    <div class="box-body">
                        <table class="table table-bordered table-hover responsive nowrap" width="100%">
                            <tr>
                                <th>Created Date:</th>
                                <td><?php echo convert_db2display($view_data['task']['t_createdate']); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><?php echo $view_data['task']['t_status']; ?></td>
                            </tr>
                            <tr>
                                <th>Estimated Hours:</th>
                                <td><?php echo $view_data['task']['t_hours']; ?></td>
                            </tr>
                            <?php if ($view_data['task']['t_parent']  == 0) { ?>
                                <tr>
                                    <th>Planned Hours:</th>
                                    <td><?php echo $view_data['task']['t_hours_planned']; ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <th>Hours Worked:</th>
                                <td>
                                    <?php
                                    $n = $view_data['task']['t_hours_total'];
                                    $whole = floor($n);      // 1
                                    $fraction = $n - $whole; // .25
                                    if ($fraction == '.75')
                                        $total_salary = str_replace($fraction, '.75', '.45');
                                    else if ($fraction == '.25')
                                        $total_salary = str_replace($fraction, '.25', '.15');
                                    else if ($fraction == '.50')
                                        $total_salary = str_replace($fraction, '.50', '.30');
                                    else
                                        $total_salary = $fraction;
                                    echo $whole + $total_salary; ?>
                                </td>
                            </tr>
                            <?php if (in_array($view_data['admin_session']['u_type'], ['Master Admin', 'Bim Head', 'Project Leader'])) { ?>
                                <tr>
                                    <th>Project Contacts:</th>
                                    <td><a href="<?php echo base_url("home/project_contacts/" . $view_data['p_id']); ?>"
                                            class="btn btn-primary pull-right">View</a></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="box box-sbpink">
                    <div class="box-body">
                        <table class="table table-bordered table-hover responsive nowrap" width="100%">
                            <tbody class="admin_list">
                                <tr>
                                    <th>Priority:</th>
                                    <td><?php echo $view_data['task']['t_priority']; ?></td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td><?php echo $view_data['task']['u_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Assigned To:</th>
                                    <td>
                                        <?php if (count($view_data['task']['assigns'])) { ?>
                                            <ul>
                                                <?php foreach ($view_data['task']['assigns'] as $file) { ?>
                                                    <li><?php echo $file['u_name']; ?></li>
                                                <?php } ?>
                                            </ul>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Team:</th>
                                    <td>
                                        <?php if (count($view_data['task']['team'])) { ?>
                                            <ul>
                                                <?php foreach ($view_data['task']['team'] as $file) { ?>
                                                    <li><?php echo $file['u_name']; ?></li>
                                                <?php } ?>
                                            </ul>
                                        <?php } ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="box box-sbpink">
                    <div class="box-body">
                        <b>Files:</b> <br /><br />
                        <?php if (count($view_data['task']['files'])) { ?>
                            <table class="table table-bordered table-hover responsive nowrap" width="100%">
                                <tbody class="admin_list">
                                    <?php foreach ($view_data['task']['files'] as $file) { ?>
                                        <tr>
                                            <th><?php echo $file['tf_title']; ?></th>
                                            <!--<td><?php echo $file['tf_file_name']; ?> <a href="<?php echo base_url('home/download/task/' . $file['tf_id']); ?>" target="_blank" class="btn btn-primary pull-right"><i class="fa fa-download"></i></a></td>-->
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-sbpink">
                    <div class="box-body">
                        <b>Task Details:</b><br /><br />
                        <?php echo $view_data['task']['t_description']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-sbpink">
                    <div class="box-body">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active" onclick="TabLogHours()"><a href="#tab_1" data-toggle="tab">Log
                                        Hours</a></li>
                                <li onclick="TabMessage()"><a href="#tab_2" data-toggle="tab">Message</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab_1">
                                    <div class="row ">
                                        <div class="col-lg-12">
                                            <table id="dataTableLogHour"
                                                class="table table-bordered table-hover responsive nowrap" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>User</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Comment</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="admin_list">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab_2">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <form name="tm_add_form" id="tm_add_form" method="post" />
                                            <div class="form-group">
                                                <label for="tm_text">Comments</label>
                                                <textarea class="form-control" id="tm_text" name="tm_text" value=""
                                                    placeholder="Comments"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="tm_file">Attachment</label>
                                                <input type="file" class="form-control" id="tm_file" name="tm_file"
                                                    value="" />
                                            </div>
                                            <div>
                                                <button type="button" id="main_add_button" onclick="saveTmessage();"
                                                    class="btn btn-primary margin">Submit</button>
                                            </div>

                                            <table id="dataTableTaskMessage"
                                                class="table table-bordered table-hover responsive nowrap"
                                                width="100% ">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Comment</th>
                                                        <th>Attachment</th>
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
                        </div>
                    </div>
                </div>
            </div>
    </section>

</div><!-- /.content-wrapper -->
<script>
    var STYPE = '';
    var dataTableLogHour = null;
    var dataTableTaskMessage = null;
    var t_p_id = '<?php echo $view_data['p_id']; ?>';
    var t_id = '<?php echo $view_data['t_id']; ?>';
    var sId = 1;
    var act_type = '<?php echo $view_data['act'] ?>';

    function document_ready() {
        jQuery(".edit_only").hide();
        jQuery(".edit_only_table").hide();
        TabLogHours();
    }

    function TabLogHours() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/tasks'); ?>",
                method: "post",
                data: {
                    "act": "t_loghours",
                    "t_id": t_id,
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
        if (dataTableLogHour != null) {
            dataTableLogHour.destroy();
        }
        dataTableLogHour = loadDataTable('#dataTableLogHour', dtConf);
    }

    function saveTmessage(sType) {
        var rules = {
            tm_text: {
                required: true
            },
        };
        var form = setValidation('#tm_add_form', rules);
        var isValid = form.valid();
        if (isValid == true) {
            var formData = new FormData();
            formData.append("act", "tm_add");
            formData.append("t_id", t_id);
            formData.append("tm_text", $("#tm_text").val());
            formData.append("tm_file", $("#tm_file")[0].files[0]);
            postForm('api/tasks', formData, function(res) {
                if (res.status == "pass") {
                    showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {},
                        function() {
                            jQuery("#tm_add_form")[0].reset();
                            dataTableTaskMessage.ajax.reload();
                        });
                } else {
                    if (res.type != 'undefined' && res.type == 'popup') {
                        showMessage(res.message, 'tm_add_form', 'error_message', 'danger', true);
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

    function TabMessage() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/tasks'); ?>",
                method: "post",
                data: {
                    "act": "tm_list",
                    "t_id": t_id,
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
        if (dataTableTaskMessage != null) {
            dataTableTaskMessage.destroy();
        }
        dataTableTaskMessage = loadDataTable('#dataTableTaskMessage', dtConf);

    }
</script>