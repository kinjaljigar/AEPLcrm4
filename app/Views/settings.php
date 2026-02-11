<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Settings <?php ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Settings</h3>
                    </div>
                    <div class="col-md-5">
                        <?php if (in_array($view_data['admin_session']['u_type'], ['Master Admin', 'Bim Head'])) { ?>
                            <!-- <a onclick="return showAddEditForm()" href="javascript://"
                            class="btn btn-primary pull-right">Add New Message</a> -->
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
                                    <th>Title</th>
                                    <th>Key</th>
                                    <th>Value</th>
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
        <input type="hidden" name="id" id="id">
        <div class="form-group">
            <label for="s_value">Value</label>
            <textarea type="text" class="form-control" id="s_value" name="s_value" style="height:150px;"></textarea>
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
                url: "<?php echo base_url('api/settings'); ?>",
                method: "post",
                data: {
                    "act": "list",
                }
            },
            pageLength: 25,
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
                    "targets": [0, 1, 2],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }
            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Setting(s)</b> added with your criteria.",
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
            doAjax('api/settings', 'POST', {
                id: id,
                act: "list"
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    showModal('html', html, 'Edit Setting', 'modal', 'modal-md', function() {
                        $.each(record, function(key, value) {
                            $('#admin_add_form').find('#' + key).val(value);
                        });
                        setDatePicker("#admin_add_form .date-picker", {});
                        //$('#admin_add_form').find('#div_me_p_id').hide();
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        } else {
            showModal('html', html, 'Add New Message', 'modal', 'modal-md', function() {
                setDatePicker("#admin_add_form .date-picker", {});
            });
        }

    }

    function saveMain(sType) {
        STYPE = sType;
        var rules = {
            s_value: {
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
            doAjax('api/settings', 'post', formData, function(res) {
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
</script>