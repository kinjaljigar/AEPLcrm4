<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Contact Directory: <span id="p_name"><b><?php echo $view_data['project']['p_name']; ?></b></span>
            <a href="<?php echo site_url("home/projects"); ?>" class="btn btn-primary pull-right">Back to Project
                List</a>
        </h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Contact List</h3>
                    </div>
                    <div class="col-md-5">
                        <a onclick="return showAddEditForm()" href="javascript://"
                            class="btn btn-primary pull-right">Add New Contact</a>
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
                                    <th>Contact Name</th>
                                    <th>Designation</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
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
        <input type="hidden" name="pc_id" id="pc_id" />
        <input type="hidden" name="pc_p_id" id="pc_p_id" value="<?php echo $view_data['p_id']; ?>" />
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="pc_name">Name</label>
                    <input type="text" class="form-control" id="pc_name" name="pc_name" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="pc_mobile">Mobile</label>
                    <input type="text" class="form-control" id="pc_mobile" name="pc_mobile" value="" placeholder="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="pc_email">Email</label>
                    <input type="text" class="form-control" id="pc_email" name="pc_email" value="" placeholder="">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="pc_designation">Designation</label>
                    <input type="text" class="form-control" id="pc_designation" name="pc_designation" value=""
                        placeholder="">
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain();"
            class="btn btn-primary margin pull-right">Save</button>
    </div>

</div>
<script>
var STYPE = '';
var dataTable = null;
var pc_p_id = '<?php echo $view_data['p_id']; ?>';

function document_ready() {
    LoadData();
}

function LoadData() {
    var dtConf = {
        "ajax": {
            url: "<?php echo base_url('api/project_contacts'); ?>",
            method: "post",
            data: {
                "act": "list",
                "pc_p_id": pc_p_id,
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
        "oLanguage": {
            "sEmptyTable": "There is not any <b>Projects</b> added with your criteria.",
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
        doAjax('api/project_contacts', 'POST', {
            pc_id: id,
            pc_p_id: pc_p_id,
            act: "list"
        }, function(res) {
            if (res.status == 'pass') {
                var record = res.data;
                showModal('html', html, 'Edit Project Contact', 'modal', 'modal-lg', function() {
                    ///console.log(record);
                    $.each(record, function(key, value) {
                        //console.log(key);
                        $('#admin_add_form').find('#' + key).val(value);
                    });
                    $('#admin_add_form .edit_only').show();
                });
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    } else {
        showModal('html', html, 'Add New Project Contact', 'modal', 'modal-lg', function() {});
    }

}

function saveMain(sType) {
    var rules = {
        pc_name: {
            required: true
        },
        pc_mobile: {
            required: true,
            digits: true,
            minlength: 10,
            maxlength: 10,
        },
        pc_email: {
            email: true
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
        doAjax('api/project_contacts', 'post', formData, function(res) {
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
</script>