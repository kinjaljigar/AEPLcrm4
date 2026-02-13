<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Holidays <?php ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Holiday List</h3>
                    </div>
                    <div class="col-md-5">
                    <?php if(($view_data['admin_session']['u_type'] ?? '') == 'Master Admin') { ?>
                            <a onclick="return showAddEditForm()" href="javascript://" class="btn btn-primary pull-right">Add New Holiday</a>
                    <?php } ?>
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
                                <th>Day</th>
                                    <th>Date</th>
                                    <th>Holiday</th>
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
        <input type="hidden" name="h_id" id="h_id">
        <div class="form-group">
            <label for="h_date">Date</label>
            <input type="text" class="form-control date-picker" id="h_date" name="h_date" value="" placeholder="">
        </div>
        <div class="form-group">
            <label for="h_title">Title</label>
            <input type="text" class="form-control" id="h_title" name="h_title" value="" placeholder="">
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain('C');" class="btn btn-primary margin pull-right">Save</button>
    </div>

</div>
<script>
    var STYPE = '';
    var dataTable = null;

    function document_ready() {
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/holidays'); ?>",
                method: "post",
                data: {
                    "act": "list",
                }
            },
            pageLength: -1,
            stripeClasses: [ 'r0', 'r1' ],
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
                    "targets": [0, 1, 2, 3],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }
            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Holidays</b> added with your criteria.",
            },
        };
        if (dataTable != null) {
            dataTable.destroy();
        }
        dataTable = loadDataTable('#datatable', dtConf);
    }

    function showAddEditForm(id) {
        var id = id == 'undefined' ? 0 : id;
        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';
        if (parseInt(id) > 0) {
            doAjax('api/holidays', 'POST', {
                h_id: id,
                act:"list"
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    showModal('html', html, 'Edit Holiday', 'modal', 'modal-md', function() {
                        $.each(record, function(key, value) {
                                $('#admin_add_form').find('#' + key).val(value);
                        });
                        setDatePicker("#admin_add_form .date-picker", {});
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        } else {
            showModal('html', html, 'Add New Holiday', 'modal', 'modal-md', function() {
                setDatePicker("#admin_add_form .date-picker", { });
            });
        }

    }

    function saveMain(sType) {
        STYPE = sType;
        var rules = {
            h_date: {
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
            formData.push({ name: "act", value: "add" });
            console.log(formData);
            doAjax('api/holidays', 'post', formData, function(res) {
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
        showModal('confirm', 'Are you sure , you want to delete this <b>Holiday</b>?', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/holidays', 'POST', {
                    h_id: id,
                    act:"del"
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
        if ($("#admin_add_form input[name='KType']:checked").val() == "Registered") {
            $("#admin_add_form #ForRegRow").show();
        } else {
            $("#admin_add_form #ForRegRow").hide();
        }
    }
</script>