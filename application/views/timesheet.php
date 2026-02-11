<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>TimeSheet</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <b>Select Date:</b>
                        <input type="text" class="form-control inline date-picker" id="at_start_sdate"
                            name="at_start_sdate" value="<?php echo date("d-m-Y"); ?>"
                            style="display:inline;width:90px;" readonly> to
                        <input type="text" class="form-control inline date-picker" id="at_sdate" name="at_sdate"
                            value="<?php echo date("d-m-Y"); ?>" style="display:inline;width:100px;" readonly>
                        <button type="button" class="btn btn-primary" onclick="LoadData()">Go</button>
                    </div>
                    <div class="col-md-5">
                        <a onclick="return showAddEditForm()" href="javascript://"
                            class="btn btn-primary pull-right">Add Time</a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">

                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Task</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Comment</th>
                                    <th width="120">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="total_hrs" style="float: right;margin-right: 85px;"></div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<div class="admin_add_modal" style="display: none;">
    <div class="box-body">
        <input type="hidden" name="at_id" id="at_id">

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
<script>
    var STYPE = '';
    var dataTable = null;

    function document_ready() {
        LoadData();
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                //'type': 'projects',
                'type': 'timesheetprojects',
                'leave': true
            }]
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                $("#at_p_id").html(record.timesheetprojects);
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
        setDatePicker(".date-picker", {});

        setcompletehrs();
        // $("#at_sdate").change(function(){
        //     LoadData();
        //
        // });

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
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                'type': 'tasks',
                'p_id': id,
                'id': t_id
            }]
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                $('#admin_add_form').find('#at_t_id').html(record.tasks);
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/timesheet'); ?>",
                method: "post",
                data: {
                    "act": "list",
                    "at_date": $("#at_sdate").val(),
                    "at_start_sdate": $("#at_start_sdate").val(),
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
                    "targets": [0, 1, 2, 3],

                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }
            ],
            // "createdRow": function( row, data, dataTable ) {
            //     console.log(data);

            // },

            "oLanguage": {
                "sEmptyTable": "There is not any <b>Time Record</b> added with your criteria.",
            }
        };
        if (dataTable != null) {
            dataTable.destroy();
        }
        dataTable = loadDataTable('#datatable', dtConf);

        setcompletehrs();

    }

    function showAddEditForm(id) {
        var PrevDay = new Date();
        PrevDay.setDate(PrevDay.getDate() - <?php echo $view_data['at_days_back']; ?>);
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
                        initProjectSelect2('#admin_add_form #at_p_id', $('.modal'));
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
                initProjectSelect2('#admin_add_form #at_p_id', $('.modal'));
            });
        }

    }

    function saveMain(sType) {
        STYPE = sType;
        var rules = {
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
                        dataTable.ajax.reload();
                        setcompletehrs();
                        showMessage(res.message, 'admin_add_form', 'error_message', 'success', true);
                        $('#sbModel').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else {
                        showModal('ok', res.message, 'Success!', 'modal-success', 'modal-sm', function() {},
                            function() {
                                form[0].reset();
                                dataTable.ajax.reload();
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

    function deleteRecord(id) {
        showModal('confirm', 'Are you sure, you want to delete this <b>Time Record</b>?', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/timesheet', 'POST', {
                    at_id: id,
                    act: "del"
                }, function(res) {
                    if (res.status == 'pass') {
                        showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                            function() {
                                dataTable.ajax.reload();
                                setcompletehrs();
                            });
                    } else {
                        showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                    }
                });
            });
    }
</script>