<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>TimeSheet</h1>
    </section>
    <section class="content">

        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">
                        <div class="col-md-7">


                            <div class="admin_add_modal" class="col-sm-6">
                                <div class="box-body">
                                    <input type="hidden" name="at_id" id="at_id">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="at_p_id">User </label>
                                                <select class="form-control" id="at_u_id" name="at_u_id" onchange="getProjects(this.value)">
                                                    <option value="">Select User</option>
                                                    <?php if (!empty($view_data['users'])): ?>
                                                        <?php foreach ($view_data['users'] as $user): ?>
                                                            <option value="<?= $user['u_id']; ?>"><?= htmlspecialchars($user['u_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="at_p_id">Project </label>
                                                <select type="text" class="form-control" id="at_p_id" name="at_p_id"
                                                    onchange="SelectProject(this.value, 0)"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="at_t_id">Tasks </label>
                                                <select type="text" class="form-control" id="at_t_id" name="at_t_id"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="at_date">Date </label>
                                                <input type="text" class="form-control date-picker" id="at_date" name="at_date" readonly />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="at_start">From </label>
                                                <select type="text" class="form-control" id="at_start" name="at_start">
                                                    <?php MakeTime(); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="at_end">To</label>
                                                <select type="text" class="form-control" id="at_end" name="at_end">
                                                    <?php MakeTime(); ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="at_comment">Comment </label>
                                                <textarea class="form-control" id="at_comment" name="at_comment" value="" placeholder=""></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <button type="button" class="btn btn-danger margin5 pull-right" data-dismiss="modal">Cancel</button>
                                    <button type="button" id="main_add_button" onclick="saveMain('NC');" class="btn btn-primary margin5 pull-right"
                                        style="display: none;">Save &amp; New</button>
                                    <button type="button" id="main_add_button" onclick="saveMain('C');"
                                        class="btn btn-primary margin5 pull-right">Save</button>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

</div><!-- /.content-wrapper -->

<script>
    var STYPE = '';
    var dataTable = null;

    function document_ready() {
        // showAddEditForm();
        //setDatePicker(".date-picker", {});
        // var PrevDay = new Date();
        // PrevDay.setDate(PrevDay.getDate() - <?php echo $view_data['at_days_back']; ?>);
        // setDatePicker("#admin_add_form .date-picker", {
        //     startDate: PrevDay,
        //     endDate: Date()
        // });
        setDatePicker("#admin_add_form .date-picker", {});

    }

    function getProjects(u_id) {
        if (!u_id) {
            $("#at_p_id").html('<option value="">Select Project</option>');
            return;
        }
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                'type': 'empprojects',
                'leave': true,
                'u_id': u_id
            }]
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                $("#at_p_id").html(record.empprojects);
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    }

    function setcompletehrs() {
        doAjax('api/timesheet', 'POST', {
            "at_date": $("#at_sdate").val(),
            "at_start_sdate": $("#at_start_sdate").val(),
            act: "total_time"
        }, function(res) {
            if (res.status == 'pass') {
                $('#total_hrs').html(res.total_hrs);
            } else {
                showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
            }
        });
    }

    function SelectProject(id, t_id) {
        if (!id) {
            $('#at_t_id').html('<option value="">Select Task</option>');
            return;
        }
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                'type': 'emptasks',
                'p_id': id,
                'id': t_id,
                'u_id': $("#at_u_id").val(),
            }]
        }, function(res) {
            if (res && res.status === 'pass' && res.data && res.data.emptasks) {
                $('#at_t_id').html(res.data.emptasks);
            } else {
                $('#at_t_id').html('<option value="">Select Task</option>');
                showModal('ok', res.message || 'No tasks found', 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    }

    function showAddEditForm(id) {
        var PrevDay = new Date();
        PrevDay.setDate(PrevDay.getDate() - <?php echo $view_data['at_days_back'] ?? 7; ?>);
        var id = id == 'undefined' ? 0 : id;
        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';
        if (parseInt(id) > 0) {
            doAjax('api/timesheet', 'POST', {
                at_id: id,
                act: "list"
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    var pe = res.pe;
                    showModal('html', html, 'Edit Time', 'modal', 'modal-sm', function() {
                        $.each(record, function(key, value) {
                            if (key == 'p_show_dashboard') {
                                if (value == "Yes") {
                                    $('#admin_add_form').find('#' + key).prop('checked', true);
                                }
                            } else {
                                $('#admin_add_form').find('#' + key).val(value);
                                if (key == 'at_p_id') {
                                    SelectProject(value, record.at_t_id);
                                }
                            }
                        });
                        setDatePicker("#admin_add_form .date-picker", {
                            startDate: PrevDay,
                            endDate: Date()
                        });
                        $('#admin_add_form .edit_only').show();
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        } else {
            showModal('html', html, 'Add New Time', 'modal', 'modal-sm', function() {
                setDatePicker("#admin_add_form .date-picker", {
                    startDate: PrevDay,
                    endDate: Date()
                });
            });
        }

    }

    function saveMain(sType) {
        STYPE = sType;
        var rules = {
            at_u_id: {
                required: true,
                min: 0
            },
            at_p_id: {
                required: true,
                min: 0
            },
            at_date: {
                required: true,
            },
        };
        var messages = {
            at_p_id: {
                min: "Please select project"
            },
            at_u_id: {
                min: "Please select Users"
            },
        };
        var form = setValidation('#admin_add_form', rules, messages);
        if ($("#admin_add_form #at_p_id").val() > 0) {
            $("#admin_add_form #at_t_id").rules("add", {
                required: true
            });
            $("#admin_add_form #at_comment").rules("add", {
                required: true
            });
        } else {
            $("#admin_add_form #at_t_id").rules("remove");
            $("#admin_add_form #at_comment").rules("remove");
        }

        var isValid = form.valid();
        if (isValid == true) {
            var formData = form.serializeArray();
            formData.push({
                name: "act",
                value: "add"
            });
            //console.log(formData);
            doAjax('api/timesheet', 'post', formData, function(res) {
                if (res.status == "pass") {
                    if (STYPE == 'NC') {
                        //form[0].reset();
                        $("#admin_add_form #at_id").val('');
                        $("#admin_add_form #at_p_id").val('');
                        $("#admin_add_form #at_t_id").val('');
                        $("#admin_add_form #at_date").val('');
                        $("#admin_add_form #at_start").val('');
                        $("#admin_add_form #at_end").val('');
                        $("#admin_add_form #at_comment").val('');
                        // dataTable.ajax.reload();
                        setcompletehrs();
                        showMessage(res.message, 'admin_add_form', 'error_message', 'success', true);
                        $('#sbModel').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else {
                        showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {},
                            function() {
                                form[0].reset();
                                //dataTable.ajax.reload();
                                setcompletehrs();
                            });
                    }
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
</script>