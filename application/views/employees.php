<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Employees</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Employees List</h3>
                    </div>
                    <div class="col-md-5">
                        <a onclick="return showAddEditForm()" href="javascript://"
                            class="btn btn-primary pull-right">Add New Employee</a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        Filter: <input class="form-control" name="txt_search" id="txt_search"
                            style="width:150px; display:inline" placeholder="Employee Name" />
                        <select class="form-control" id="txt_U_Type" name="txt_U_Type"
                            style="width:auto; display:inline">
                            <option value="">Select User Type</option>
                            <option value="Employee">Employee</option>
                            <option value="Project Leader">Project Leader</option>
                            <option value="Bim Head">Bim Head</option>
                            <option value="TaskCoordinator">TaskCoordinator</option>
                            <option value="MailCoordinator">MailCoordinator</option>
                        </select>
                        <select class="form-control" id="txt_U_Status" name="txt_U_Status"
                            style="width:auto; display:inline">
                            <option value="">Select User Status</option>
                            <option value="Active">Active</option>
                            <option value="Deactive">Deactive</option>
                        </select>
                        <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Employees</button>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Employee Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Salary/Hr.</th>
                                    <th>User Type</th>
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
        <input type="hidden" name="u_id" id="u_id">
        <img src="" id="u_photo_disp" style="max-width:100px;display:none" />
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_username">Username</label>
                    <input type="text" class="form-control" id="u_username" name="u_username" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_password">Password<span class="explain-tip edit_only">Keep blank if do not want to
                            change.</span></label>
                    <input type="password" class="form-control" id="u_password" name="u_password" value=""
                        placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_name">Employee Name </label>
                    <input type="text" class="form-control" id="u_name" name="u_name" value="" placeholder="">
                    <span class="explain-tip">&nbsp;</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_photo">Photo <span class="explain-tip edit_only">No need to select if keep existing as
                            it is.</span></label>
                    <input type="file" class="form-control" id="u_photo" name="u_photo" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_join_date">Joining Date </label>
                    <input type="text" class="form-control date-picker" id="u_join_date" name="u_join_date" value=""
                        placeholder="" readonly>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_leave_date">Leaving Date </label>
                    <input type="text" class="form-control date-picker" id="u_leave_date" name="u_leave_date" value=""
                        placeholder="" readonly>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_salary">Salary/hr </label>
                    <input type="text" class="form-control" id="u_salary" name="u_salary" value="" placeholder="">
                </div>
            </div>
            <div class="form-group col-lg-4 col-md-6 col-sm-12">
                <label for="u_app_auth">Active in Mobile App</label>
                <select class="form-control" name="u_app_auth" id="u_app_auth">
                    <option value="1">Active</option>
                    <option value="0" selected>Deactive</option>
                </select>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_mobile">Mobile </label>
                    <input type="text" class="form-control" id="u_mobile" name="u_mobile" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_email">Email </label>
                    <input type="text" class="form-control" id="u_email" name="u_email" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_address">Address </label>
                    <input type="text" class="form-control" id="u_address" name="u_address" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_qualification">Qualification </label>
                    <input type="text" class="form-control" id="u_qualification" name="u_qualification" value=""
                        placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_department">Department </label>
                    <select class="form-control" name="u_department" id="u_department">
                        <option value="Architecture">Architecture</option>
                        <option value="MEPF">MEPF</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_qualification">Status </label>
                    <select class="form-control" name="u_status" id="u_status">
                        <option value="Active">Active</option>
                        <option value="Deactive">Deactive</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_qualification">Employee Type </label>
                    <select class="form-control" name="u_type" id="u_type">
                        <option value="Bim Head">Bim Head</option>
                        <option value="Project Leader">Project Leader</option>
                        <option value="Employee">Employee</option>
                        <option value="TaskCoordinator">TaskCoordinator</option>
                        <option value="MailCoordinator">MailCoordinator</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="u_qualification">Project Leader </label>
                    <select class="form-control" name="u_leader" id="u_leader">
                        <!-- -->
                    </select>
                </div>
            </div>
        </div>
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
                $("#u_leader").html(record.team_leader);
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });

    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/employees'); ?>",
                method: "post",
                data: {
                    "act": "list",
                    "txt_search": $("#txt_search").val(),
                    "txt_U_Type": $("#txt_U_Type").val(),
                    "txt_U_Status": $("#txt_U_Status").val(),
                }
            },
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            "columnDefs": [
                /*{
                               "targets": [0],
                               "searchable": false,
                               'bSortable': true,
                               "orderable": true,
                           }, */
                {
                    //"targets": [0, 1, 2, 3, 4, 5, 6],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }
            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Employees</b> added with your criteria.",
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
            doAjax('api/employees', 'POST', {
                u_id: id,
                act: "list"
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    showModal('html', html, 'Edit Employee', 'modal', 'modal-lg', function() {
                        $.each(record, function(key, value) {
                            if (u_type == 'Master Admin') {
                                if (key == 'u_photo') {} else {
                                    $('#admin_add_form').find('#' + key).val(value);
                                }
                            } else {
                                if (key == 'u_photo' || key == 'u_salary') {} else {
                                    $('#admin_add_form').find('#' + key).val(value);
                                }

                            }

                        });
                        setDatePicker("#admin_add_form .date-picker", {
                            endDate: Date()
                        });
                        if (record.u_photo != "") {
                            $('#admin_add_form').find('#u_photo_disp').attr('src', record.u_photo).css(
                                'display', 'block');
                        }
                        $('#admin_add_form .edit_only').show();
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        } else {
            showModal('html', html, 'Add New Employee', 'modal', 'modal-lg', function() {
                setDatePicker("#admin_add_form .date-picker", {
                    endDate: Date()
                });
            });
        }

    }

    function saveMain(sType) {
        STYPE = sType;
        var rules = {
            u_username: {
                required: true
            },
            u_name: {
                required: true
            },
            // u_salary: {
            //   required: true,
            //  digits: true,
            //},
            u_mobile: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10,
            },
            u_email: {
                required: false,
                email: true,
            },
            u_address: {
                required: false
            },
            u_qualification: {
                required: false
            },
            u_department: {
                required: true
            },
            u_status: {
                required: true
            },
            u_type: {
                required: true
            },
            u_comments: {
                required: false
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
            formData.append("logo_file", $("#admin_add_form").find("#u_photo")[0].files[0]);
            postForm('api/employees', formData, function(res) {
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
        showModal('confirm', 'Are you sure , you want to delete this <b>Employee</b>?', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/employees', 'POST', {
                    u_id: id,
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

    function KType() {
        return false;
        if ($("#admin_add_form input[name='KType']:checked").val() == "Registered") {
            $("#admin_add_form #ForRegRow").show();
        } else {
            $("#admin_add_form #ForRegRow").hide();
        }
    }
</script>