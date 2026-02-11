<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Projects</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Projects List</h3>
                    </div>
                    <div class="col-md-5">
                        <?php if ($view_data['admin_session']['u_type'] == 'Super Admin' || $view_data['admin_session']['u_type'] == 'Master Admin' || $view_data['admin_session']['u_type'] == 'Bim Head') { ?>
                        <a onclick="return showAddEditForm()" href="javascript://"
                            class="btn btn-primary pull-right">Add New Project</a>
                            <?php 
                        } ?>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        Filter: <input class="form-control" name="txt_search" id="txt_search"
                            style="width:150px; display:inline" placeholder="Project No" />
                        <select class="form-control" id="txt_p_cat" name="txt_p_cat" style="width:auto; display:inline">
                            <?php
                            foreach ($view_data['p_cat'] as $val) {
                                echo '<option value="' . $val . '">' . $val . '</option>';
                            }
                            ?>
                        </select>
                        <select class="form-control" id="txt_p_status" name="txt_p_status"
                            style="width:auto; display:inline">
                            <option value="Active">Active</option>
                            <option value="Hold">Hold</option>
                            <option value="Completed">Completed</option>
                        </select>
                        <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
                            <select class="form-control" name="txt_p_leader" id="txt_p_leader" style="width:auto; display:inline">
                                <option value="">Select Project Handle By</option>
                                <?php
                                foreach ($view_data['p_leader'] as $val) {
                                    echo '<option value="' . $val['u_id'] . '">' . htmlspecialchars($val['u_name']) . '</option>';
                                }
                                ?>
                            </select>
                        <?php } ?>
                        <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Projects</button>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Project Number</th>
                                    <th>Project Name</th>
                                    <th>Address</th>
                                    <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
                                        <th>Cost</th>
                                        <th>Expense</th>
                                        <th>Profit/Loss</th>
                                    <?php } ?>
                                    <th>Status</th>
                                    <th>Project Lead</th>
                                    <th width="120">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<div class="admin_add_modal" style="display: none;">
    <div class="box-body">
        <input type="hidden" name="p_id" id="p_id">
        <img src="" id="u_photo_disp" style="max-width:100px;display:none" />
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="p_number">Project Number </label>
                    <input type="text" class="form-control" id="p_number" name="p_number" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="p_name">Project Name</label>
                    <input type="text" class="form-control" id="p_name" name="p_name" value="" placeholder="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="p_file">Project Picture <span class="explain-tip edit_only">No need to select if keep
                            existing as it is.</span></label>
                    <input type="file" class="form-control" id="p_file" name="p_file" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="p_value">Project Value </label>
                    <input type="text" class="form-control" id="p_value" name="p_value" value="" placeholder="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="p_contact">Contact Number </label>
                    <input type="text" class="form-control" id="p_contact" name="p_contact" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="p_cat">Project Category</label>
                    <select class="form-control" name="p_cat" id="p_cat">
                        <?php
                        foreach ($view_data['p_cat'] as $val) {
                            echo '<option value="' . $val . '">' . $val . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="p_status">Project Status</label>
                    <select class="form-control" name="p_status" id="p_status">
                        <option value="Active">Active</option>
                        <option value="Hold">Hold</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="p_address">Project Address </label>
                    <input type="text" class="form-control" id="p_address" name="p_address" value="" placeholder="">
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="p_leader">Leader </label>
                    <select class="form-control" name="p_leader[]" id="p_leader" multiple style="width: 500px; height: 150px;">
                        <!-- -->
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="p_scope">Project Scope </label>
                    <textarea class="form-control" id="p_scope" name="p_scope" value="" placeholder=""></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="p_show_dashboard">Show on dashboard</label> &nbsp;
                    <label class="check_container">Yes
                        <input type="checkbox" id="p_show_dashboard" name="p_show_dashboard" value="Yes">
                        <span class="checkmark"></span>
                    </label>
                </div>
            </div>
        </div>
        <?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
            <div class="box box-sbpink">
                <div class="box-header">
                    <div class="row">
                        <div class="col-md-7">
                            <h3 class="box-title">PROJECT EXPENSES</h3>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <div id="expense_row">
                        <div class="row" id="f_row">
                            <div class="col-xs-5" data-type="lbl"><input type="text" class="form-control" name="pe_lbl[]"
                                    value="" placeholder="Expense Label"></div>
                            <div class="col-xs-5" data-type="val"><input type="text" class="form-control" name="pe_val[]"
                                    value="" placeholder="Expense Value"></div>
                            <div class="col-xs-2"><a href="javascript://" class="btn btn-primary btn-md btn_remove_me"><i
                                        class="fa fa-minus"></i></a></div>
                        </div>
                    </div><br />
                    <a href="javascript://" class="btn btn-primary btn-md btn_plus"><i class="fa fa-plus"></i></a>
                </div>
            </div>
        <?php } ?>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain('C');"
            class="btn btn-primary margin pull-right">Save</button>
    </div>

</div>
<script>
    var STYPE = '';
    var dataTable = null;

    function document_ready() {
        LoadData();
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                'type': 'team_leader'
            }]
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                $("#p_leader").html(record.team_leader);
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });

    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/projects'); ?>",
                method: "post",
                data: {
                    "act": "list",
                    "txt_search": $("#txt_search").val(),
                    "txt_p_cat": $("#txt_p_cat").val(),
                    "txt_p_status": $("#txt_p_status").val(),
                    "txt_p_leader": $("#txt_p_leader").val(),
                }
            },
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            dom: 'Blfrtip',
            "buttons": true,
            bSort: false,
            "columnDefs": [
                /*{
                               "targets": [0],
                               "searchable": false,
                               'bSortable': true,
                               "orderable": true,
                           }, */

            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Projects</b> added with your criteria.",
            },
        };
        if (dataTable != null) {
            dataTable.destroy();
        }
        dataTable = loadDataTable('#datatable', dtConf);
    }

    function showAddEditForm(id, u_type) {
        var id = id == 'undefined' ? 0 : id;
        var u_type = u_type == 'undefined' ? 0 : u_type;
        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';
        if (parseInt(id) > 0) {
            doAjax('api/projects', 'POST', {
                p_id: id,
                act: "list"
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    var pe = res.pe;
                    showModal('html', html, 'Edit Project', 'modal', 'modal-lg', function() {
                        $.each(record, function(key, value) {
                            if (key == 'p_show_dashboard') {
                                if (value == "Yes") {
                                    $('#admin_add_form').find('#' + key).prop('checked', true);
                                }
                            } else if (key === 'p_leader') {
                                // Load Leaders dynamically with selected IDs
                                doAjax('api/drop_get', 'POST', {
                                    dropobjs: [{
                                        'type': 'team_leader',
                                        'id': value
                                    }]
                                }, function(res2) {
                                    if (res2.status === 'pass') {
                                        $('#admin_add_form').find('#p_leader').html(res2.data.team_leader);
                                    } else {
                                        showModal('ok', res2.message, 'Error!', 'modal-danger', 'modal-sm');
                                    }
                                });
                            } else {

                                if (u_type == 'Master Admin')
                                    $('#admin_add_form').find('#' + key).val(value);
                                else
                                if (key == 'p_value') {} else
                                    $('#admin_add_form').find('#' + key).val(value);
                            }

                        });
                        setDatePicker("#admin_add_form .date-picker", {});
                        if (record.photo != "") {
                            $('#admin_add_form').find('#u_photo_disp').attr('src', record.photo).css(
                                'display', 'block');
                        }
                        $('#admin_add_form .edit_only').show();
                        ExpenseClick(pe);
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        } else {
            showModal('html', html, 'Add New Project', 'modal', 'modal-lg', function() {
                setDatePicker("#admin_add_form .date-picker", {});
                ExpenseClick('');
            });
        }

    }

    function saveMain(sType) {
        STYPE = sType;
        var rules = {
            p_number: {
                required: true
            },
            p_name: {
                required: true
            },
            // p_value: {
            //     required: true,
            //     digits: true,
            //     max: 1000000000,
            // },
            p_contact: {
                digits: true,
                maxlength: 16,
            },
        };
        var form = setValidation('#admin_add_form', rules);
        var isValid = form.valid();
        if (isValid == true) {
            var postData = form.serializeArray();
            var formData = new FormData();
            $.each(postData, function(i, d) {
                formData.append(d.name, d.value);
            });
            formData.append("act", "add");
            formData.append("logo_file", $("#admin_add_form").find("#p_file")[0].files[0]);
            postForm('api/projects', formData, function(res) {
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

    function deleteRecord(id) {
        showModal('confirm', 'Are you sure , you want to delete this <b>Project</b>?', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/projects', 'POST', {
                    p_id: id,
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
    /* Hide me [PENDING]*/
    function ExpenseClick(pe) {
        jQuery("#admin_add_form #f_row").hide();
        jQuery("#admin_add_form .btn_plus").click(function() {
            jQuery("#admin_add_form #f_row").clone().appendTo($("#admin_add_form #expense_row")).show().removeAttr(
                'id');
            jQuery("#admin_add_form .btn_remove_me").click(function() {
                jQuery(this).parent().parent().remove();
            });
        });
        var reset_min = false;
        $.each(pe, function(key, value) {
            var Obj = jQuery("#admin_add_form #f_row").clone().appendTo($("#admin_add_form #expense_row")).show()
                .removeAttr('id');
            Obj.children("[data-type=lbl]").children().val(value.pe_lbl);
            Obj.children("[data-type=val]").children().val(value.pe_val);
            reset_min = true;
        });
        if (reset_min) {
            jQuery("#admin_add_form .btn_remove_me").click(function() {
                jQuery(this).parent().parent().remove();
            });
        }
    }
</script>