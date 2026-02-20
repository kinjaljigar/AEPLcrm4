<style>
    .main-msg {
        background: #e9f6ff;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .reply-msg {
        background: #f9f9f9;
        padding: 8px;
        border-radius: 5px;
        margin-bottom: 8px;
        border-left: 3px solid #4CAF50;
    }
</style>
<?php
$users = $view_data['users'];
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Messages</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Messages</h3>
                    </div>
                    <div class="col-md-5">
                        <?php if (in_array($view_data['admin_session']['u_type'], ['Master Admin', 'Bim Head', 'MailCoordinator', 'Super Admin'])) {
                        ?>
                            <a onclick="return showAddMessageForm()" class="btn btn-primary pull-right">Add Message</a>
                        <?php  }
                        ?>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px;">
                    <div class="col-md-3">
                        <label>Project</label>
                        <select id="search_project" class="form-control project-select">
                            <!-- existing project options loaded via JS -->
                        </select>
                    </div>

                    <?php if (in_array($view_data['admin_session']['u_type'], ['Master Admin', 'Super Admin', 'Bim Head', 'TaskCoordinator', 'MailCoordinator'])): ?>
                    <div class="col-md-2">
                        <label>Leaders</label>
                        <select id="search_leader" class="form-control">
                            <option value="">All</option>
                            <?php foreach ($view_data['allLeaders'] as $leader): ?>
                                <option value="<?= $leader['u_id']; ?>"><?= htmlspecialchars($leader['u_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="col-md-2">
                        <label>Date</label>
                        <input type="date" id="search_date" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>Discipline</label>
                        <select id="search_discipline" class="form-control">
                            <option value="">ALL</option>
                            <option value="ARCH">ARCH</option>
                            <option value="MEPF">MEPF</option>
                            <option value="STR">STR</option>
                            <option value="OTHER">OTHER</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>&nbsp;</label><br>
                        <button class="btn btn-primary" onclick="LoadData()">Search</button>
                    </div>
                </div>
                <table id="datatable" class="table table-bordered table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Project</th>
                            <th>Message</th>
                            <th>Discipline</th>
                            <th>Replies</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody class="admin_list"></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<div id="addMessageModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-md">
        <form id="add_message_form">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Send Message</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body"><input type="hidden" id="pm_id" name="pm_id">
                    <div class="form-group" id="projectDiv"><label>Project</label><select class="form-control project-select" id="pm_p_id" name="pm_p_id"></select></div>
                    <!-- <div class="form-group"><label>Send To</label>
                            <select class="form-control" id="msg_type" name="msg_type">
                                <option value="project">Project Users</option>
                                <option value="broadcast">All Users</option>
                            </select>
                        </div> -->

                    <div class="form-group">
                        <label for="users">Send To</label>
                        <select name="u_ids[]" id="users" class="form-control" multiple required>
                            <?php foreach ($users as $user): ?>
                                <?php
                                $userTypeLabel = ($user['u_type'] === 'Employee') ? 'Employee' : $user['u_type'];
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


                    <div class="form-group" id="projectDiv">
                        <label>DISCIPLINE</label>
                        <select class="form-control" id="pm_descipline" name="pm_descipline">
                            <option value="ARCH">ARCH</option>
                            <option value="MEPF">MEPF</option>
                            <option value="STR">STR</option>
                            <option value="OTHER">OTHER</option>
                            <option value="ALL">ALL</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Message</label><textarea class="form-control" id="pm_text" name="pm_text" style="height:120px;"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="saveMessage()" class="btn btn-primary">Send</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="mainMessageModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 id="main_message_title" class="modal-title">Message</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form id="admin_main_form">
                <input type="hidden" name="pm_id" id="pm_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Project</label>
                        <select class="form-control" name="pm_proj_id" id="pm_proj_id"></select>
                        <small class="text-muted">Select General OR Project based message</small>
                    </div>

                    <div class="form-group">
                        <label>Message</label>
                        <textarea class="form-control" name="pm_text" id="pm_text" rows="5"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" id="main_save_btn" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="replyMessageModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-md">
        <form id="reply_form">
            <div class="modal-content"><button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Message Details</h4>
                </div>
                <div class="modal-body">
                    <div id="main_message_view"></div>
                    <hr>
                    <div id="all_replies_view" class="reply-box-area" style="max-height:300px;overflow-y:auto;"></div>
                    <div class="form-group"><label>Your Reply</label><textarea class="form-control" id="reply_text" name="reply_text"></textarea></div><input type="hidden" id="reply_msg_id">
                </div>
                <div class="modal-footer"><button type="button" onclick="saveReply()" class="btn btn-primary">Reply</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    var dataTable = null;

    var hasLeaderFilter = <?= in_array($view_data['admin_session']['u_type'], ['Master Admin', 'Super Admin', 'Bim Head', 'TaskCoordinator', 'MailCoordinator']) ? 'true' : 'false' ?>;
    // Store all leaders options for resetting when project is cleared
    var allLeadersHtml = '';

    function document_ready() {
        var base_url = "<?= base_url() ?>";
        LoadProjects();

        if (hasLeaderFilter && $('#search_leader').length) {
            allLeadersHtml = $('#search_leader').html();
        }

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
                        let options = "";
                        options += `<option value="ALL_PROJECT" selected='selected'>All Project Members</option>`;
                        users.forEach(user => {
                            let userTypeLabel = (user.u_type === 'Employee') ? 'Employee' : user.u_type;
                            options += `<option value="${user.u_id}">${user.u_name} - ${userTypeLabel}</option>`;
                        });
                        $("#users").html(options);
                    }
                });
            } else {
                $("#users").html(defaultUsersHtml);
            }
        });

        // When search project changes, update leaders list to show only assigned leaders
        $(document).on("change", "#search_project", function() {
            if (!hasLeaderFilter || !$('#search_leader').length) return;
            var projectId = $(this).val();
            if (projectId) {
                doAjax('api/projectmessages', 'POST', {
                    act: 'get_project_leaders',
                    project_id: projectId
                }, function(res) {
                    if (res.status == 'pass' && res.data) {
                        var options = '<option value="">All</option>';
                        res.data.forEach(function(leader) {
                            options += '<option value="' + leader.u_id + '">' + leader.u_name + '</option>';
                        });
                        $('#search_leader').html(options);
                    }
                });
            } else {
                // Reset to all leaders
                $('#search_leader').html(allLeadersHtml);
            }
        });
    }

    function LoadProjects() {
        doAjax('api/drop_get', 'POST', {
                dropobjs: [{
                        'type': 'Leaderassignprojects',
                        'active_only': true
                    }

                ]
            }

            ,
            function(res) {
                if (res.status == 'pass') {
                    $("#pm_p_id").html(res.data.Leaderassignprojects);
                    $("#search_project").html('<option value="">All Projects</option>' + res.data.Leaderassignprojects);
                }
            });
    }

    function LoadData() {
        if (dataTable != null) dataTable.destroy();
        var logged_role = "<?php echo $view_data['admin_session']['u_type']; ?>";
        var canExport = (logged_role === 'Master Admin' || logged_role === 'Bim Head' || logged_role === 'MailCoordinator');
        var exportOpts = { columns: [0, 1, 2, 3, 4] }; // exclude col 5 (Action buttons)
        var exportButtons = canExport ? [
            { extend: 'excelHtml5', title: 'Messages', exportOptions: exportOpts },
            { extend: 'csvHtml5',   title: 'Messages', exportOptions: exportOpts },
            { extend: 'pdfHtml5',   title: 'Messages', orientation: 'landscape', pageSize: 'A3', exportOptions: exportOpts },
            { extend: 'print',      title: 'Messages', exportOptions: exportOpts }
        ] : [];
        var ajaxData = {
            act: "list",
            project_id: $("#search_project").val(),
            search_date: $("#search_date").val(),
            search_discipline: $("#search_discipline").val()
        };
        if (hasLeaderFilter && $('#search_leader').length && $('#search_leader').val()) {
            ajaxData.leader_id = $('#search_leader').val();
        }
        dataTable = loadDataTable("#datatable", {
            "ajax": {
                url: "<?php echo base_url('api/projectmessages'); ?>",
                type: "POST",
                data: ajaxData
            },
            pageLength: 25,
            columnDefs: [
                {
                    "targets": [0, 1, 2, 3, 4],
                    "orderable": false
                },
                {
                    "targets": 1,
                    "width": "210px",
                    "render": function(data, type, row) {
                        if (type === 'display' && data) {
                            var safe = String(data).replace(/"/g, '&quot;');
                            return '<span title="' + safe + '" style="white-space:normal;word-break:break-word;display:block;max-width:210px;">' + data + '</span>';
                        }
                        return data || '';
                    }
                },
                {
                    "targets": 2,
                    "width": "320px",
                    "render": function(data, type, row) {
                        if (type === 'display' && data) {
                            var safe = String(data).replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                            if (data.length > 70) {
                                var short = data.substr(0, 70);
                                return '<span style="white-space:normal;word-break:break-word;">' + short +
                                    '<span data-toggle="tooltip" data-placement="top" data-container="body" title="' + safe + '" style="color:#337ab7;cursor:pointer;font-weight:600;"> ...more</span></span>';
                            }
                            return '<span style="white-space:normal;word-break:break-word;">' + data + '</span>';
                        }
                        return data || '';
                    }
                }
            ],
            "drawCallback": function() {
                $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
            },
            "dom": canExport ? 'Blfrtip' : 'lfrtip',
            "buttons": exportButtons,
        });
    }

    function showAddMessageForm(pm_id = "") {
        $("#pm_id").val(pm_id);
        $("#add_message_form")[0].reset();
        $("#addMessageModal").modal("show");
        setTimeout(function() {
            initProjectSelect2('#addMessageModal .project-select', $('#addMessageModal'));
        }, 200);
    }

    function saveMessage() {
        var data = $("#add_message_form").serializeArray();

        data.push({
            name: "act",
            value: "add",
        });

        doAjax("api/projectmessages", 'POST', data, function(res) {
            showModal('ok', res.message);

            if (res.status == "pass") {
                $("#addMessageModal").modal("hide");
                LoadData();
            }
        });
    }

    function showThreadModal(pm_id) {

        $("#reply_msg_id").val(pm_id);

        doAjax("api/projectmessages", "POST", {
            act: "thread",
            pm_id: pm_id
        }, function(res) {
            console.log(res.data);
            if (!res) {
                showModal('ok', 'No response from server', 'Error', 'modal-danger', 'modal-sm');
                return;
            }

            if (res.status && res.status === 'pass' && res.data) {

                var headerHtml = res.data.header || res.data.message_html || '';
                var repliesHtml = res.data.replies_html || res.data.replies || '';

                $("#main_message_view").html(headerHtml);
                $("#all_replies_view").html(repliesHtml);

                $("#replyMessageModal").modal("show");
            } else {
                var msg = (res.message) ? res.message : 'Unable to load thread';
                showModal('ok', msg, 'Error', 'modal-danger', 'modal-sm');
            }
        }, function(err) {

            console.error('Thread AJAX error', err);
            showModal('ok', 'Server error while fetching thread', 'Error', 'modal-danger', 'modal-sm');
        });
    }

    function saveReply() {
        var pm_id = $("#reply_msg_id").val();
        var rep_text = $("#reply_text").val();

        if (!pm_id || $.trim(rep_text) === '') {
            showModal('ok', 'Please enter reply text', 'Validation', 'modal-danger', 'modal-sm');
            return;
        }

        doAjax("api/projectmessages", "POST", {
            act: "reply",
            pm_id: pm_id,
            rep_text: rep_text
        }, function(res) {
            if (res && res.status === 'pass') {

                $("#reply_text").val('');

                showThreadModal(pm_id);
                $('#replyMessageModal').modal('hide');

                if (typeof dataTable !== 'undefined' && dataTable != null) dataTable.ajax.reload(null, false);
            } else {
                var msg = (res && res.message) ? res.message : 'Failed to add reply';
                showModal('ok', msg, 'Error', 'modal-danger', 'modal-sm');
            }
        }, function(err) {
            console.error('Reply AJAX error', err);
            showModal('ok', 'Server error while saving reply', 'Error', 'modal-danger', 'modal-sm');
        });
    }

    function deleteProjectMessage(id) {
        showModal('confirm', 'Delete this message?', 'Confirm', 'modal-default', 'modal-sm', function() {
            doAjax("api/projectmessages", "POST", {
                    act: "del",
                    pm_id: id
                }

                ,
                function(res) {
                    showModal("ok", res.message);
                    LoadData();
                });
        });
    }

    function showMessageModal(messageId) {

        $('#reply_message_id').val(messageId);

        $('#reply_text').val('');

        $('#replyMessageModal').modal('show');
    }

    function closeMessageModal() {
        $('#replyMessageModal').modal('hide');
    }
</script>