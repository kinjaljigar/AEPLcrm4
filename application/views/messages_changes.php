<?php
$users = $view_data['users'];
?><div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Messages <?php ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Messages</h3>
                    </div>
                    <div class="col-md-5">
                        <?php if (in_array($view_data['admin_session']['u_type'], ['Master Admin', 'Bim Head', 'Project Leader'])) { ?>
                            <a onclick="return showAddEditForm()" href="javascript://" class="btn btn-primary pull-right">Add New Message</a>
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
                                    <th>Date</th>
                                    <th>Project</th>
                                    <th>Message</th>
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
        <input type="hidden" name="me_id" id="me_id">
        <div class="form-group" id="div_me_p_id">
            <!-- <label for="me_p_id">Project</label>
            <select class="form-control" name="me_p_id" id="me_p_id">
            </select> -->
            <input type="hidden" id="pm_id" name="pm_id">
            <div class="form-group" id="projectDiv"><label>Project</label><select class="form-control" id="pm_p_id" name="pm_p_id"></select></div>
        </div>
        <div class="form-group">
            <label for="users">Send To</label>
            <select name="u_ids[]" id="users" class="form-control" multiple required>
                <?php foreach ($users as $user): ?>
                    <?php
                    $userTypeLabel = ($user['u_type'] === 'Employee') ? 'Team Member' : $user['u_type'];
                    ?>
                    <option value="<?= $user['u_id']; ?>">
                        <?= $user['u_name'] . " - " . $userTypeLabel; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">
                Hold down Ctrl (Windows) / Command (Mac) to select multiple users.
            </small>
        </div>
        <div class="form-group">
            <label for="me_text">Message</label>
            <textarea type="text" class="form-control" id="me_text" name="me_text" style="height:150px;"></textarea>
        </div>
    </div>
    <!-- /.box-body -->

    <!-- <div class="box-footer">
        
        <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
        <button type="button" id="main_add_button" onclick="saveMain('C');" class="btn btn-primary margin pull-right">Save</button>
    </div> -->
    <div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button><button type="button" onclick="saveMessage()" class="btn btn-primary">Send</button></div>

</div>
<script>
    var STYPE = '';
    var dataTable = null;

    function document_ready() {
        var base_url = "<?= base_url() ?>";
        LoadProjects();
        LoadData();
        let defaultUsersHtml = `<?php foreach ($users as $user): ?>
    <option value="<?= $user['u_id']; ?>"><?= $user['u_name'] . " - " . $user['u_type']; ?></option>
<?php endforeach; ?>`;
        $(document).on("change", "#pm_p_id", function() {
            var projectId = $(this).val();
            if (projectId) {
                $.ajax({
                    url: base_url + "meeting/getProjectUsers/" + projectId,
                    type: "GET",
                    dataType: "json",
                    success: function(users) {
                        let options = `<option value="ALL_PROJECT">All Project Members</option>`;

                        users.forEach(user => {
                            let userTypeLabel = (user.u_type === 'Employee') ? 'Team Member' : user.u_type;
                            options += `<option value="${user.u_id}">${user.u_name} - ${userTypeLabel}</option>`;
                        });

                        $("#admin_add_form #users").empty().append(options);
                    }
                });
            } else {
                // reset if no project selected
                //$("#users").html('<option value="">Select Users</option>');
                $("#users").html(defaultUsersHtml);
            }
        });

        // doAjax('api/drop_get', 'POST', {
        //     dropobjs: [{
        //         'type': 'projects',
        //         'active_only': true,
        //     }]
        // }, function(res) {
        //     if (res.status == 'pass') {
        //         var record = res.data;
        //         $("#me_p_id").html(record.projects);
        //     } else {
        //         showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
        //     }
        // });

    }

    function LoadProjects() {
        doAjax('api/drop_get', 'POST', {
                dropobjs: [{
                        'type': 'projects',
                        'active_only': true
                    }

                ]
            }

            ,
            function(res) {
                if (res.status == 'pass') {
                    $("#pm_p_id").html(res.data.projects);
                }
            });
    }


    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/messages'); ?>",
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
                "sEmptyTable": "There is not any <b>Message(s)</b> added with your criteria.",
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
            doAjax('api/messages', 'POST', {
                me_id: id,
                act: "list"
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    showModal('html', html, 'Edit Message', 'modal', 'modal-md', function() {
                        $.each(record, function(key, value) {
                            $('#admin_add_form').find('#' + key).val(value);
                        });
                        setDatePicker("#admin_add_form .date-picker", {});
                        $('#admin_add_form').find('#div_me_p_id').hide();
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

    function saveMessage() {
        var data = $("#admin_add_form").serializeArray();

        data.push({
            name: "act",
            value: "add",
        });

        doAjax("api/projectmessages", 'POST', data, function(res) {
            showModal('ok', res.message);

            if (res.status == "pass") {
                $("#admin_add_form").modal("hide");
                LoadData();
            }
        });
    }


    function saveMain(sType) {
        STYPE = sType;
        var rules = {
            me_text: {
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
            doAjax('api/messages', 'post', formData, function(res) {
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
        showModal('confirm', 'Are you sure , you want to delete this <b>Message</b>?', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/messages', 'POST', {
                    me_id: id,
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