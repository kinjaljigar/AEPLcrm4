<?php
    $ismain =false;
    if($view_data['act'] == "add") { 
        if($view_data['t_id'] > 0) { $ismain = false; } else { $ismain = true; }
    }
    else {
        if($view_data['task']['t_parent'] > 0 ) { $ismain = false; } else { $ismain = true; }
    }
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php if($view_data['act'] == "add") { ?>
            <?php if($view_data['t_id'] > 0) { ?>
            ADDING SUB TASK FOR: <span id="p_name"><b><?php echo $view_data['task']['t_title']; ?></b></span>
            <?php } else { ?>
            ADDING TASK FOR: <span id="p_name"><b><?php echo $view_data['project']['p_name']; ?></b></span>
            <?php } ?>
            <?php } else { ?>
            EDITING TASK: <span id="p_name"><b><?php echo $view_data['task']['t_title']; ?></b></span>
            <?php } ?>
            <a href="<?php echo $view_data['return_url'] ?>" class="btn btn-primary pull-right">Back to Task List</a>
        </h1>
    </section>
    <section class="content">
        <form name="add_form" id="add_form" method="post" enctype="multipart/form-data">
            <div class="box box-sbpink">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12">
                        </div>
                    </div><br />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="t_title">Task Title</label>
                                <input type="text" class="form-control" id="t_title" name="t_title" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="t_priority">Priority</label>
                                <select class="form-control" id="t_priority" name="t_priority">
                                    <?php
                                    foreach ($view_data['priorities'] as $val) {
                                        echo '<option value="' . $val . '">' . $val . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="t_hours">Estimated Hours</label>
                                <input type="text" class="form-control" id="t_hours" name="t_hours" />
                            </div>
                            <div class="form-group">
                                <?php if($ismain) { ?>
                                <label for="t_hours_planned">Planned Hours</label>
                                <input type="text" class="form-control" id="t_hours_planned" name="t_hours_planned" />
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="t_status">Status</label>
                                <select class="form-control" id="t_status" name="t_status">
                                    <option value="New">New</option>
                                    <option value="Inprogress">Inprogress</option>
                                    <option value="Hold">Hold</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="t_description">Task Description</label>
                                <textarea class="form-control" id="t_description" name="t_description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="t_dependancy">Task Dependency</label>
                                <textarea class="form-control" id="t_dependancy" name="t_dependancy"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <table id="user_table" class="table table-bordered table-hover responsive nowrap"
                                width="100% ">
                                <thead>
                                    <tr>
                                        <th width="30"> </th>
                                        <th>Employee Name</th>
                                        <th>Active Projects</th>
                                        <th>On Hand Tasks</th>
                                        <th>On Leave</th>
                                    </tr>
                                </thead>
                                <div class="col-xs-12">
                                    Filter: <input class="form-control" name="txt_search" id="txt_search"
                                        style="width:150px; display:inline" placeholder="Employee Name" />
                                    <button type="button" id="main_add_button" onclick="LoadData();"
                                        class="btn btn-primary margin">Show Employees</button>
                                </div>
                                <tbody class="admin_list" id="employee_list">
                                    <?php
                                    foreach ($view_data['employees'] as $employee) {
                                    ?>
                                    <tr>
                                        <td>
                                            <label class="check_container">
                                                <input type="checkbox" id="u_ids_<?php echo $employee['u_id']; ?>"
                                                    name="u_id[]" value="<?php echo $employee['u_id']; ?>">
                                                <span class="checkmark assigns"></span>
                                            </label>
                                        </td>
                                        <td><?php echo $employee['u_name']; ?></td>
                                        <td><?php echo $view_data['active_projects'][$employee['u_id']]??0; ?></td>
                                        <td><?php echo $view_data['active_tasks'][$employee['u_id']]??0; ?></td>
                                        <td><?php 
                                                    if(!empty($view_data['leaves'][$employee['u_id']]))
                                                    {
                                                        //print_r($view_data['leaves'][$employee['u_id']]);
                                                        foreach($view_data['leaves'][$employee['u_id']] as $val){
                                                            echo $val."<br />";
                                                        }
                                                    }
                                            ?></td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row edit_only_table">
                        <div class="col-lg-12">
                            <b>Files add Previously</b>
                            <table id="files_table" class="table table-bordered table-hover responsive nowrap"
                                width="100% ">
                                <thead>
                                    <tr>
                                        <th>Location</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="admin_list"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="files_row">
                                <div class="row" id="f_row">
                                    <div class="col-xs-10">
                                        <div class="form-group">
                                            <label for="t_tf_lblfile">File Location</label>
                                            <input type="text" class="form-control tf_lbl" id="tf_lbl"
                                                name="tf_lbl[]" />
                                        </div>
                                    </div>
                                    <!--
                                    <div class="col-xs-5">
                                        <div class="form-group">
                                            <label for="tf_file">Attach File</label>
                                            <input type="file" class="form-control tf_file" id="tf_file" name="tf_file[]" />
                                        </div>                                    
                                    </div>
                                    -->
                                    <div class="col-xs-2"><label for="">&nbsp;</label><br /><a href="javascript://"
                                            class="btn btn-primary btn-md btn_remove_me"><i class="fa fa-minus"></i></a>
                                    </div>
                                </div>
                            </div><br />
                            <a href="javascript://" class="btn btn-primary btn-md btn_plus"><i
                                    class="fa fa-plus"></i></a>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="button" id="main_add_button" onclick="saveMain('C');"
                        class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </section>

</div><!-- /.content-wrapper -->
<script>
var STYPE = '';
var t_parent;
var dataTable = null;
var t_p_id = '<?php echo $view_data['p_id']; ?>';
var t_id = '<?php echo $view_data['t_id']; ?>';
var sId = 1;
var act_type = '<?php echo $view_data['act']?>';

if (act_type == "add") {
    t_parent = t_id;
    t_id = 0;
} else
    t_parent = <?php echo $view_data['task']['t_parent']??0; ?>;

function document_ready() {
    jQuery("#files_row #f_row").hide();
    jQuery(".btn_plus").click(function() {
        jQuery("#files_row #f_row").clone().appendTo($("#files_row")).show().removeAttr('id').attr('id',
            'file_' + sId).addClass('file_row');
        sId = sId + 1;
        jQuery("#files_row .btn_remove_me").click(function() {
            jQuery(this).parent().parent().remove();
        });
    });

    jQuery(".btn_plus").trigger('click');
    jQuery(".edit_only").hide();
    jQuery(".edit_only_table").hide();

    assign_users();

}

function LoadData() {
    doAjax('api/employees', 'POST', {
        txt_search: $("#txt_search").val(),
        act: "list_task"
    }, function(res) {
        var record = res.data;
        $('#employee_list').empty();
        html = '';
        $.each(record, function(key, value) {
            Uid = value[0];
            UName = value[2];
            ActPrj = res.active_projects[Uid] ?? 0;
            ActTask = res.active_tasks[Uid] ?? 0;
            Leaves = res.leaves[Uid] ?? '';
            html = html + '<tr>';
            html = html + '<td><label class="check_container"><input type="checkbox" id="u_ids_' + Uid +
                '" name="u_id[]" value="' + Uid + '">';
            html = html + '<span class="checkmark assigns"></span></label></td>';
            html = html + '<td>' + UName + '</td>';
            html = html + '<td>' + ActPrj + '</td>';
            html = html + '<td>' + ActTask + '</td>';
            html = html + '<td>' + Leaves + '</td>';
            html = html + '</tr>';

        });
        if (html != '') {
            $('#employee_list').html(html);
            assign_users();
        } else {
            $('#employee_list').html(
                "<tr><td colspan='3'>There is not any <b>Employees</b> added with your criteria.</td></tr>");
        }
    });
}

function assign_users() {
    if (act_type == 'edit') {
        $(".assigns").click(function() {
            //$('#add_form').find('#u_ids_' + value.tu_u_id).prop('checked', true);

            var ele = $(this).parent().children("input");
            var u_id = ele.val();
            var act_sub = '';
            if (ele.prop('checked') == false) // False means checked
            {
                act_sub = 'add';
            } else {
                act_sub = 'remove';
            }
            doAjax('api/tasks', 'POST', {
                t_id: t_id,
                t_p_id: t_p_id,
                u_id: u_id,
                act: "assigns",
                act_sub: act_sub,
            }, function(res) {
                if (res.status == 'pass') {
                    showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm');
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        });

        doAjax('api/tasks', 'POST', {
            t_id: t_id,
            act: "list"
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                $.each(record, function(key, value) {
                    if (key == 'p_show_dashboard') {
                        if (value == "Yes") {
                            //$('#add_form').find('#' + key).prop('checked', true);
                        }
                    } else {
                        $('#add_form').find('#' + key).val(value);
                    }
                });
                $.each(res.assigns, function(key, value) {
                    $('#add_form').find('#u_ids_' + value.tu_u_id).prop('checked', true);
                });
                $.each(res.files, function(key, value) {
                    var tr_html = '';
                    tr_html = tr_html + '<tr id="row_tf_' + value.tf_id + '">';
                    tr_html = tr_html + '<td>' + value.tf_title + '</td>';
                    //tr_html = tr_html + '<td>' + value.tf_file_name + '</td>';
                    tr_html = tr_html + '<td>';
                    //                            tr_html = tr_html + '<td><a href="<?php echo base_url('home/download/task/'); ?>' + value.tf_id + '" target="_blank" class="btn btn-primary">Download</a> &nbsp; ';
                    tr_html = tr_html +
                        '<button type="button" class="btn btn-primary" onClick="RemoveFile(' + value
                        .tf_id + ')">Remove</button></td>';
                    tr_html = tr_html + '</tr>';
                    $('#files_table tbody').append(tr_html);
                    jQuery(".edit_only_table").show();
                });
                $('.edit_only').show();
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                //[PENDING] Go aBack
            }
        });
    }
}

function saveMain(sType) {
    var rules = {
        t_title: {
            required: true
        },
        t_hours: {
            number: true,
            required: true,
            step: 0.25
        },
        t_hours_planned: {
            number: true,
            step: 0.25
        }
    };
    var form = setValidation('#add_form', rules);
    var isValid = form.valid();
    // [PENDING Validation for files]
    /*var fd_file = form.find('input[name=fd_file]')[0].files[0];
                if (fd_file != undefined) {
                    form_data.append("fd_file", fd_file);
                }
                */

    $(".file_row").each(function() {
        var tf_lbl = $(this).find(".tf_lbl").val();
        var tf_file = $(this).find(".tf_file").val();

        $(this).find("label.has-error").remove();
        $(this).find('.form-group').removeClass('has-error');

        if (tf_lbl == "" && tf_file != "") {
            //isValid = false;
            //$(this).find(".tf_lbl").parent().addClass('has-error').append('<label id="tf_lbl-error" class="has-error" for="tf_lbl" style="display: inline-block;">This field is required.</label>');
        } else if (tf_lbl != "" && tf_file == "") {
            //isValid = false;
            //$(this).find(".tf_file").parent().addClass('has-error').append('<label id="tf_file-error" class="has-error" for="tf_file" style="display: inline-block;">This field is required.</label>');
        } else if (tf_lbl != "" && tf_file != "") {
            // Validate for file extentions. [PENDING]
        }
    });

    if (isValid == true) {
        var postData = form.serializeArray();
        var formData = new FormData();
        $.each(postData, function(i, d) {
            formData.append(d.name, d.value);
        });
        formData.append("act", "add");
        formData.append("t_p_id", t_p_id);
        formData.append("t_id", t_id);
        formData.append("t_parent", t_parent);

        $(".file_row").each(function() {
            var tf_lbl = $(this).find(".tf_lbl").val();
            var tf_file = $(this).find(".tf_file").val();
            if (tf_lbl != "" && tf_file != "") {
                //formData.append("tf_file[]", $(this).find(".tf_file")[0].files[0]);
            }
        });
        postForm('api/tasks', formData, function(res) {
            if (res.status == "pass") {
                showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {},
                    function() {
                        window.location.href = '<?php echo $view_data['return_url'] ?>';
                    });
            } else {
                if (res.type != 'undefined' && res.type == 'popup') {
                    showMessage(res.message, 'add_form', 'error_message', 'danger', true);
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

function RemoveFile(id) {
    showModal('confirm', 'Are you sure, you want to delete this <b>File</b>?', 'Confirm', 'modal-default',
        'modal-sm',
        function() {
            doAjax('api/tasks', 'POST', {
                tf_id: id,
                act: "file_del"
            }, function(res) {
                if (res.status == 'pass') {
                    showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                        function() {
                            $("#row_tf_" + id).remove();
                        });
                } else {
                    showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                }
            });
        });
}

function deleteRecord(id) {
    showModal('confirm', 'Are you sure , you want to delete this <b>Project Contact</b>?', 'Confirm', 'modal-default',
        'modal-sm',
        function() {
            doAjax('api/project_contacts', 'POST', {
                pc_id: id,
                pc_p_id: pc_p_id,
                act: "del"
            }, function(res) {
                if (res.status == 'pass') {
                    showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                        function() {
                            dataTable.ajax.reload();
                        });
                } else {
                    showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                }
            });
        });
}

function assign($t_id, $u_ids) {

}
</script>