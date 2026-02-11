<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Leaves <?php ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Leave List</h3>
                    </div>
                    <div class="col-md-5">
                        <a onclick="return showAddEditForm()" href="javascript://"
                            class="btn btn-primary pull-right">Add New Leave</a>
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
                                    <th>Employee</th>
                                    <th>Post Dt.</th>
                                    <th>From Dt.</th>
                                    <th>To Dt.</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Is Half Day ?</th>
                                    <th>Is Hourly leave ?</th>
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
        <input type="hidden" name="l_id" id="l_id">
        <div class="form-group l_is_halfday">
            <input type="checkbox" id="l_is_halfday" name="l_is_halfday" value="Yes"
                onchange="checkboxclick(this)">&nbsp;
            <label>Half Day</label>

        </div>
        <div class="l_halfday_time" style="display: none;">
            <div class="form-group">
                <label class="check_container inline">First Half
                    <input type="radio" id="l_halfday_time_1" name="l_halfday_time" value="first" checked>
                    <span class="checkmark"></span>
                </label>
                <label class="check_container inline">Second Half
                    <input type="radio" id="l_halfday_time_2" name="l_halfday_time" value="second">
                    <span class="checkmark"></span>
                </label> <br />

            </div>
        </div>
        <div class="form-group">
            <input type="checkbox" id="l_is_hourly" name="l_is_hourly" value="Yes"
                onchange="checkboxHourclick(this)">&nbsp;
            <label>Hourly leave</label>

        </div>
        <div class="l_hourly_time" style="display: none;">
            <div class="form-group">
                <label class="check_container inline">Late Coming
                    <input type="radio" id="l_hourly_time_1" name="l_hourly_time" value="late coming" checked>
                    <span class="checkmark"></span>
                </label>
                <label class="check_container inline">Lunch Break Extend
                    <input type="radio" id="l_hourly_time_2" name="l_hourly_time" value="lunch breack extend">
                    <span class="checkmark"></span>
                </label>
                <label class="check_container inline">Early Going
                    <input type="radio" id="l_hourly_time_3" name="l_hourly_time" value="early going">
                    <span class="checkmark"></span>
                </label> <br /> <br />
            </div>

            <div class="form-group">
                <label for="l_from_date">Total Hour ( Add only Hours which affect... for ex : 0.15 , 0.30 , 0.45 , 1 ,
                    ...)</label>
                <input type="text" class="form-control" id="l_hourly_time_hour" name="l_hourly_time_hour" value=""
                    placeholder="">
            </div>

        </div>

        <div class="form-group">
            <label for="l_from_date">Start Date</label>
            <input type="text" class="form-control date-picker" id="l_from_date" name="l_from_date" value=""
                placeholder="">
        </div>
        <div class="form-group">
            <label for="l_to_date">To Date</label>
            <input type="text" class="form-control date-picker" id="l_to_date" name="l_to_date" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="l_message">Message</label>
            <textarea type="text" class="form-control" id="l_message" name="l_message"></textarea>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain('C');"
            class="btn btn-primary margin pull-right">Save</button>
    </div>

</div>
<div class="leave_form" style="display: none;">
    <div class="box-body">
        <input type="hidden" name="l_id" id="l_id" value="">
        <h4 id="u_name"><b>Name</b></h4>
        <div class="row">
            <div class="col-sm-6">
                <b>Mobile:</b> <span id="u_mobile">Mobile</span><br />
                <b>Email:</b> <span id="u_email">Email</span><br />
            </div>
            <div class="col-sm-3">
                <b>Active Projects:</b> <span id="u_active">Mobile</span><br />
                <b>Onhand Tasks:</b> <span id="u_tasks">Email</span><br />
            </div>
            <div class="col-sm-3">
                <img src="" class="img_logo" id="img_url" />
            </div>
        </div>
        <div>
            <h4><b>Leave Request:</b></h4>
            <span id="l_message"></span>
        </div>
        <hr />
        <div class="form-group">
            <label for="l_reply">Reply</label>
            <textarea type="text" class="form-control" id="l_reply" name="l_reply"></textarea>
        </div>
        <div class="form-group">
            <label class="check_container inline">Apporve
                <input type="radio" id="l_status_a" name="l_status" value="Approve">
                <span class="checkmark"></span>
            </label>
            <label class="check_container inline">Decline
                <input type="radio" id="l_status_d" name="l_status" value="Decline">
                <span class="checkmark"></span>
            </label> <br />
            <label id="l_status-error" class="has-error" for="l_status" style="display:none;">This field is
                required.</label>
        </div>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveLeave();"
            class="btn btn-primary margin pull-right">Save</button>
    </div>
</div>

<script>
var STYPE = '';
var dataTable = null;

function document_ready() {
    LoadData();
}

function checkboxHourclick(obj) {
    if ($(obj).is(":checked")) {
        $(".l_hourly_time").show();
        $('#admin_add_form').find('#l_is_halfday').prop('checked', false);
        $(".l_halfday_time").hide();
    } else {
        $(".l_hourly_time").hide();
    }
}

function checkboxclick(obj) {
    if ($(obj).is(":checked")) {
        $(".l_halfday_time").show();
        $('#admin_add_form').find('#l_is_hourly').prop('checked', false);
        $(".l_hourly_time").hide();
    } else {
        $(".l_halfday_time").hide();
    }
}

function LoadData() {
    var dtConf = {
        "ajax": {
            url: "<?php echo base_url('api/leaves'); ?>",
            method: "post",
            data: {
                "act": "list",
            }
        },
        pageLength: -1,
        stripeClasses: ['r0', 'r1'],
        aaSorting: [
            [0, 'asc']
        ],
        "columnDefs": [
            /*{
                           "targets": [0],
                           "searchable": false,
                           'bSortable': true,
                           "orderable": true,
                       }, */
            {
                "targets": [0, 1, 2, 3, 4, 5, 6, 7],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }
        ],
        "oLanguage": {
            "sEmptyTable": "There is not any <b>Leave</b> added with your criteria.",
        },
    };
    if (dataTable != null) {
        dataTable.destroy();
    }
    dataTable = loadDataTable('#datatable', dtConf);
}

function showAddEditForm(id) {

    if ($('.l_hourly_time').css('display') == 'none') {
        $('#l_is_hourly').removeAttr('checked');
    } else {
        $('#l_is_hourly').attr('checked', 'checked');
    }
    if ($('.l_halfday_time').css('display') == 'none') {
        $('#l_is_halfday').removeAttr('checked');
    } else {
        $('#l_is_halfday').attr('checked', 'checked');
    }

    var id = id == 'undefined' ? 0 : id;
    var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
    html += $('.admin_add_modal').html();
    html += '</form>';
    if (parseInt(id) > 0) {
        doAjax('api/leaves', 'POST', {
            l_id: id,
            act: "list"
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                showModal('html', html, 'Edit Leave', 'modal', 'modal-md', function() {
                    $.each(record, function(key, value) {
                        if (key == 'l_is_halfday') {
                            if (value == "Yes") {
                                $('#admin_add_form').find('#' + key).prop('checked', true);
                                $(".l_halfday_time").show();
                            } else {
                                $(".l_halfday_time").hide();
                            }
                        } else if (key == 'l_halfday_time') {
                            if (value == "first") {
                                $('#admin_add_form').find('#' + key + "_1").prop('checked',
                                    true);
                            }
                            if (value == "second") {
                                $('#admin_add_form').find('#' + key + "_2").prop('checked',
                                    true);
                            }
                        } else if (key == 'l_is_hourly') {
                            if (value == "Yes") {
                                $('#admin_add_form').find('#' + key).prop('checked', true);
                                $(".l_hourly_time").show();
                            } else {
                                $(".l_hourly_time").hide();
                            }
                        } else if (key == 'l_hourly_time') {
                            if (value == "late coming") {
                                $('#admin_add_form').find('#' + key + "_1").prop('checked',
                                    true);
                            }
                            if (value == "lunch breack extend") {
                                $('#admin_add_form').find('#' + key + "_2").prop('checked',
                                    true);
                            }
                            if (value == "early going") {
                                $('#admin_add_form').find('#' + key + "_3").prop('checked',
                                    true);
                            }
                        } else {
                            $('#admin_add_form').find('#' + key).val(value);
                        }
                        //$('#admin_add_form').find('#' + key).val(value);
                    });
                    var SDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
                    //setDatePicker("#admin_add_form .date-picker", {});
                    loadDateRange("#admin_add_form #l_from_date", "#admin_add_form #l_to_date", SDate);
                });
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    } else {
        var SDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        showModal('html', html, 'Add New Leave', 'modal', 'modal-md', function() {
            //setDatePicker("#admin_add_form .date-picker", { });
            loadDateRange("#admin_add_form #l_from_date", "#admin_add_form #l_to_date", SDate)
        });
    }

}

function saveMain(sType) {
    STYPE = sType;
    var rules = {
        l_from_date: {
            required: true
        },
        h_title: {
            required: true
        },

    };
    var form = setValidation('#admin_add_form', rules);
    var isValid = form.valid();
    if (isValid == true) {
        var formData = form.serializeArray();
        formData.push({
            name: "act",
            value: "add"
        });
        doAjax('api/leaves', 'post', formData, function(res) {
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

function saveLeave() {
    var rules = {
        l_reply: {
            required: true
        },
        l_status: {
            required: true
        },

    };
    var form = setValidation('#leave_form', rules);
    var isValid = form.valid();
    if (isValid == true) {
        var formData = form.serializeArray();
        formData.push({
            name: "act",
            value: "Approve"
        });
        doAjax('api/leaves', 'POST', formData, function(res) {
            if (res.status == 'pass') {
                showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                    function() {
                        dataTable.ajax.reload();
                    });
            } else {
                if (res.type != 'undefined' && res.type == 'popup') {
                    showMessage(res.message, 'leave_form', 'error_message', 'danger', true);
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
    showModal('confirm', 'Are you sure , you want to delete this <b>Leave</b>?', 'Confirm', 'modal-default',
        'modal-sm',
        function() {
            doAjax('api/leaves', 'POST', {
                l_id: id,
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

function Approve(id, act) {
    var id = id == 'undefined' ? 0 : id;
    var html = '<form class="formclass" id="leave_form" name="leave_form" enctype="multipart/form-data">';
    html += $('.leave_form').html();
    html += '</form>';
    if (parseInt(id) > 0) {
        doAjax('api/leaves', 'POST', {
            l_id: id,
            act: "loadinfo"
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                showModal('html', html, 'Manage Leave', 'modal', 'modal-md', function() {
                    $('#leave_form').find('#l_id').val(record.l_id);
                    $.each(record, function(key, value) {
                        $('#leave_form').find('#' + key).html(value);
                    });
                    $('#leave_form').find('#img_url').attr("src", res.img_url);
                });
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    }
}

function ApproveOld(id, act) {

    showModal('confirm', 'Are you sure , you want to <b>' + act + '</b> this <b>Leave</b>?', 'Confirm',
        'modal-default',
        'modal-md',
        function() {
            doAjax('api/leaves', 'POST', {
                l_id: id,
                act: act
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
</script>