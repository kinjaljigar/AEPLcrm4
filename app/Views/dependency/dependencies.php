<?php
$projects = $view_data['projects'];
$leaders = $view_data['leaders'];
?><style>
    /* tr.bg-warning {
        background-color: #fff3cd !important;
    } */
    tr.bg-warning {
        font-weight: 500;
    }

    #dependencies_table .dt-ellipsis {
        max-width: 350px;
    }

    .dt-ellipsis {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    /* When expanded */
    .dt-ellipsis.open {
        white-space: normal;
        overflow: visible;
        text-overflow: unset;
        max-width: 100%;
        background: #fff;
        padding: 6px;
        border-radius: 4px;
        box-shadow: 0 0 6px rgba(0, 0, 0, 0.15);
        z-index: 5;
        position: relative;
    }

    /* Legend styles */
    .dependency-legend {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px 15px;
        margin-bottom: 15px;
    }

    .legend-item {
        display: inline-block;
        margin-right: 20px;
        margin-bottom: 5px;
    }

    .legend-box {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 3px;
        margin-right: 5px;
        vertical-align: middle;
        border: 1px solid #ddd;
    }

    .legend-box.assigned {
        background-color: #fff3cd;
    }

    .legend-box.completed-by-assigned {
        background-color: #d9edf7;
        border-left: 4px solid #31708f;
    }
</style>
<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <h1>Dependencies</h1>
    </section>

    <section class="content">
        <!-- Legend -->
        <div class="dependency-legend">
            <strong>Legend:</strong>
            <div class="legend-item">
                <span class="legend-box assigned"></span>
                <span>Assigned to you (not created by you)</span>
            </div>
            <div class="legend-item">
                <span title="Completed by assigned user" style="display:inline-block;width:8px;height:8px;background: #17a2b8;border-radius:50%;margin-left:6px;"></span>
                <span>Completed by assigned user</span>
            </div>
            <!-- <div class="legend-item">
                <i class="fa fa-user-check text-info" style="font-size: 14px;"></i>
                <span>Completed by assigned user (icon in status)</span>
            </div> -->
        </div>
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row mb-12">
                            <div class="col-md-2">
                                <label>Project</label>
                                <select id="filter_project" class="form-control project-select">
                                    <option value="">All</option>
                                    <?php foreach ($projects as $p): ?>
                                    <option value="<?= $p['p_id']; ?>"><?= $p['p_number'] . " - " . $p['p_name']; ?></option>
                                <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if (in_array($view_data['admin_session']['u_type'], ['Master Admin', 'Bim Head', 'TaskCoordinator'])): ?>
                                <div class="col-md-2">
                                    <label>Leaders</label>
                                    <select id="filter_created_by" class="form-control">
                                        <option value="">All</option>
                                        <?php foreach ($leaders as $l): ?>
                                            <?php
                                            $displayName = $l['u_name'];
                                            if (in_array($l['u_type'], ['Master Admin', 'Bim Head', 'TaskCoordinator'])) {
                                                $displayName .= ' - ' . $l['u_type'];
                                            }
                                            ?>
                                            <option value="<?= $l['u_id']; ?>"><?= htmlspecialchars($displayName); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- <div class="col-md-2">
                                    <label>Assigned To</label>
                                    <select id="filter_assigned_to" class="form-control">
                                        <option value="">All</option>
                                        <?php foreach ($leaders as $l): ?>
                                            <option value="<?= $l['u_id']; ?>"><?= $l['u_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div> -->
                            <?php endif; ?>

                            <?php if (in_array($view_data['admin_session']['u_type'], ['Project Leader', 'TaskCoordinator'])): ?>
                                <!-- <div class="col-md-1">
                                    <label>Dependencies</label>
                                    <select id="filter_viewtype" class="form-control">
                                        <option value="My">My</option>
                                        <option value="All">All</option>
                                    </select>
                                </div> -->
                                <div class="col-md-1">
                                    <label>Dependencies</label>
                                    <select id="filter_createdby" class="form-control">
                                        <option value="myall"> My All</option>
                                        <option value="own">Own Created</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="all">All</option>
                                    </select>
                                </div>
                            <?php endif; ?>



                            <div class="col-md-1">
                                <label>Status</label>
                                <select id="filter_status" class="form-control">
                                    <!-- <option value="">Open</option> -->
                                    <option value="Pending">Pending</option>
                                    <!-- <option value="In Progress">In Progress</option> -->
                                    <option value="Completed">Completed</option>
                                    <option value="All">All</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label>Type</label>
                                <select id="filter_type" class="form-control">
                                    <option value="">Type</option>
                                    <option value="Internal">Internal</option>
                                    <option value="External">External</option>
                                </select>
                            </div>

                            <div class="col-md-1">
                                <label>Priority</label>
                                <select id="filter_priority" class="form-control">
                                    <option value="">Priority</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>


                            <!-- <div class="col-md-2">
                                <label>From Date</label>
                                <input type="date" id="filter_from_date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>To Date</label>
                                <input type="date" id="filter_to_date" class="form-control">
                            </div> -->
                            <div class="col-md-2" style="margin-top:25px;">
                                <button type="button" class="btn btn-primary" onclick="LoadData()">Search</button>
                                <button type="button" class="btn btn-default" onclick="ResetFilters()">Reset</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="box-body">


                <table id="dependencies_table" class="table table-bordered table-hover responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Created Date</th>
                            <th>Project</th>
                            <th>Dependency</th>
                            <th>Created By</th>
                            <th>Assigned To</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Targated Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="box-footer"></div>
        </div>
    </section>
</div>

<!-- Dependency Modal -->
<div class="modal fade" id="depModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Dependencies</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="depModalBody">Loading...</div>
        </div>
    </div>
</div>

<!-- Edit Dependency Modal -->
<div class="modal fade" id="editDepModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Dependency</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editDepForm">
                    <input type="hidden" id="edit_wd_id" name="wd_id">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Dependency Text <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_dependency_text" name="dependency_text" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_dependency_type" name="dependency_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Internal">Internal</option>
                                    <option value="External">External</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_priority" name="priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Assigned To <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_dep_leader_ids" name="dep_leader_ids[]" multiple required>
                                    <?php foreach ($leaders as $l): ?>
                                        <option value="<?= $l['u_id']; ?>"><?= htmlspecialchars($l['u_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple leaders</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Targate Date </label>
                                <input type="date" class="form-control" id="edit_dep_target_date" name="dep_target_date" />
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveDepBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    var dataTable = null;


    function document_ready() {
        LoadData();
        $(document).on('click', '.dt-ellipsis', function(e) {
            e.stopPropagation();
            $(this).toggleClass('open');
        });
        $(document).on('click', '.complete-dep', function() {
            let wd_id = $(this).data('id');
            let btn = $(this);
            if (!confirm('Are you sure you want to complete this dependency?')) {
                return;
            }
            btn.prop('disabled', true).text('Processing...');
            $.ajax({
                url: "<?php echo base_url('api/weeklywork'); ?>",
                type: "POST",
                dataType: "json",
                data: {
                    act: "complete_dependency",
                    wd_id: wd_id
                },
                success: function(res) {
                    if (res.status === 'success') {
                        //$('#dependencies_table').DataTable().ajax.reload(null, false);
                        if ($.fn.DataTable.isDataTable('#dependencies_table')) {
                            $('#dependencies_table').DataTable().ajax.reload(null, false);
                        }
                    } else {
                        alert(res.message);
                    }
                }
            });
        });

        // Handle assigned user checkbox completion
        $(document).on('change', '.assigned-complete-checkbox', function() {
            let wd_id = $(this).data('id');
            let checkbox = $(this);

            if (!confirm('Are you sure you want to mark this dependency as complete?')) {
                checkbox.prop('checked', false);
                return;
            }

            $.ajax({
                url: "<?php echo base_url('api/weeklywork'); ?>",
                type: "POST",
                dataType: "json",
                data: {
                    act: "assigned_complete_dependency",
                    wd_id: wd_id
                },
                success: function(res) {
                    if (res.status === 'success') {
                        alert(res.message || 'Dependency marked as complete. Email notification sent to creator.');
                        $('#dependencies_table').DataTable().ajax.reload(null, false);
                    } else {
                        checkbox.prop('checked', false);
                        alert(res.message || 'Failed to complete dependency');
                    }
                },
                error: function() {
                    checkbox.prop('checked', false);
                    alert('Error occurred while completing dependency');
                }
            });
        });

        // Handle edit button click
        $(document).on('click', '.edit-dep', function() {
            let wd_id = $(this).data('id');
            loadDependencyForEdit(wd_id);
        });

        // Handle save dependency changes
        $('#saveDepBtn').on('click', function() {
            saveDependencyChanges();
        });

        $(document).on('click', '.reverse-dep', function() {
            var wd_id = $(this).data('id');

            if (!confirm('Are you sure you want to reverse this dependency?')) {
                return;
            }

            $.ajax({
                url: "<?php echo base_url('api/weeklywork'); ?>",
                type: "POST",
                data: {
                    act: "reverse_dependency",
                    wd_id: wd_id
                },
                dataType: "json",
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Dependency reversed successfully');
                        //LoadData(); // reload table
                        $('#dependencies_table').DataTable().ajax.reload(null, false);
                    } else {
                        alert(res.message || 'Failed to reverse dependency');
                    }
                }
            });
        });

    }

    // Load dependency data for editing
    function loadDependencyForEdit(wd_id) {
        $.ajax({
            url: "<?php echo base_url('api/weeklywork'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                act: "get_dependency",
                wd_id: wd_id
            },
            success: function(res) {
                if (res.status === 'success') {
                    let dep = res.data;
                    // Populate form fields
                    $('#edit_wd_id').val(dep.wd_id);
                    $('#edit_dependency_text').val(dep.dependency_text);
                    $('#edit_dependency_type').val(dep.dependency_type);
                    $('#edit_priority').val(dep.priority);
                    $('#edit_status').val(dep.status);
                    $('#edit_dep_target_date').val(dep.target_date);
                    // Set selected assigned leaders
                    let assignedIds = dep.dep_leader_ids ? dep.dep_leader_ids.split(',') : [];
                    $('#edit_dep_leader_ids').val(assignedIds);

                    // Show modal
                    $('#editDepModal').modal('show');
                } else {
                    alert(res.message || 'Failed to load dependency details');
                }
            },
            error: function() {
                alert('Error loading dependency details');
            }
        });
    }

    // Save dependency changes
    function saveDependencyChanges() {
        let form = $('#editDepForm');

        // Validate form
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        // Get form data
        let formData = {
            act: "update_dependency",
            wd_id: $('#edit_wd_id').val(),
            dependency_text: $('#edit_dependency_text').val(),
            dependency_type: $('#edit_dependency_type').val(),
            priority: $('#edit_priority').val(),
            status: $('#edit_status').val(),
            dep_target_date: $('#edit_dep_target_date').val(),
            dep_leader_ids: $('#edit_dep_leader_ids').val() // Array of selected IDs
        };

        // Disable save button
        $('#saveDepBtn').prop('disabled', true).text('Saving...');

        $.ajax({
            url: "<?php echo base_url('api/weeklywork'); ?>",
            type: "POST",
            dataType: "json",
            data: formData,
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message || 'Dependency updated successfully');
                    $('#editDepModal').modal('hide');
                    $('#dependencies_table').DataTable().ajax.reload(null, false);
                } else {
                    alert(res.message || 'Failed to update dependency');
                }
            },
            error: function() {
                alert('Error updating dependency');
            },
            complete: function() {
                $('#saveDepBtn').prop('disabled', false).text('Save Changes');
            }
        });
    }

    function formatDateDMY(dateStr) {
        if (!dateStr || dateStr === '0000-00-00' || dateStr === '0000-00-00 00:00:00') {
            return '';
        }

        var datePart = dateStr.split(' ')[0]; // remove time if exists
        var parts = datePart.split('-'); // YYYY-MM-DD

        if (parts.length === 3) {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        return dateStr;
    }

    function renderEllipsisWithToggle(text, maxLength = 70) {
        if (!text) return '';

        let safeText = String(text)
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        let shortText = safeText.length > maxLength ?
            safeText.substring(0, maxLength) + '...' :
            safeText;

        return `<span class="dt-ellipsis" title="${safeText}">${shortText}</span>`;
    }

    function LoadData() {

        var project_id = $('#filter_project').val();
        var status = $('#filter_status').val();
        var type = $('#filter_type').val();
        var priority = $('#filter_priority').val();
        var leader = $('#filter_created_by').val();
        var createdby = $('#filter_createdby').val();
        var assigned_to = $('#filter_assigned_to').val();
        var from_date = $('#filter_from_date').val();
        var to_date = $('#filter_to_date').val();
        var logged_id = "<?php echo $view_data['admin_session']['u_id']; ?>";
        var logged_role = "<?php echo $view_data['admin_session']['u_type']; ?>";
        var viewtype = $('#filter_viewtype').val();
        if (dataTable != null) {
            dataTable.destroy();
        }
        var exportButtons = [];
        if (logged_role === 'Master Admin' || logged_role === 'Bim Head' || logged_role === 'TaskCoordinator') {
            exportButtons = ['excelHtml5', 'csvHtml5', 'pdfHtml5', 'print'];
        }
        dataTable = $('#dependencies_table').DataTable({
            "ajax": {
                url: "<?php echo base_url('api/weeklywork'); ?>",
                method: "POST",
                dataSrc: "data",
                data: {
                    act: "dependencies_list",
                    project_id: project_id,
                    status: status,
                    createdby: createdby,
                    assigned_to: assigned_to,
                    from_date: from_date,
                    to_date: to_date,
                    priority: priority,
                    type: type,
                    leader: leader,
                    viewtype: viewtype,
                }
            },
            scrollX: true,
            autoWidth: false,
            responsive: false,
            "columns": [{
                    "data": "#"
                }, {
                    "data": "created_date",
                    "render": function(data, type) {
                        if (type === 'display' || type === 'filter') {
                            return formatDateDMY(data);
                        }
                        return data;
                    }
                }, {
                    "data": "project_name"
                },
                {
                    "data": "dependency_text",
                    "render": function(data, type) {
                        if (type === 'display') {
                            return renderEllipsisWithToggle(data, 70);
                        }
                        return data;
                    }
                },

                {
                    "data": "created_by"
                },
                {
                    "data": "assigned_to"
                },
                {
                    "data": "dependency_type"
                },
                {
                    "data": "priority"
                },
                {
                    "data": "status",
                    "render": function(data, type, row) {

                        var labelClass = (data === 'Completed') ? 'label-success' :
                            (data === 'In Progress') ? 'label-warning' :
                            'label-danger';

                        var statusHtml = '<span class="label ' + labelClass + '">' + data + '</span>';

                        if (row.completed_by_assigned && row.completed_by_assigned != '0') {

                            statusHtml += '<span title="Completed by assigned user" ' +
                                'style="display:inline-block;width:8px;height:8px;' +
                                'background:#17a2b8;border-radius:50%;margin-left:6px;"></span>';

                            statusHtml += '<br/><span>' + formatDateDMY(row.completed_assign_date) + '</span>';

                            if (row.completed_day_diff !== null) {
                                var diffText = (row.completed_day_diff > 0) ?
                                    '+' + row.completed_day_diff + ' days' :
                                    row.completed_day_diff + ' days';

                                var diffColor = (row.completed_day_diff > 0) ? 'red' : 'green';

                                statusHtml += '<br/><span style="color:' + diffColor + ';font-weight:600;">' +
                                    diffText + '</span>';
                                statusHtml += '<br/><span style="color:' + diffColor + ';font-weight:600;">' +
                                    row.completed_assign_status + '</span>';
                            }
                        }

                        return statusHtml;
                    }
                }, {
                    "data": "target_date",
                    "render": function(data, type) {
                        if (type === 'display' || type === 'filter') {
                            return formatDateDMY(data);
                        }
                        return data;
                    }
                }, {
                    "data": null,
                    "orderable": false,
                    "render": function(data, type, row) {
                        var dep_leader_ids = row.dep_leader_ids ? row.dep_leader_ids.split(',') : [];
                        var isAssignedToMe = dep_leader_ids.includes(logged_id);
                        var isCreatedByMe = (row.created_by_id == logged_id);

                        /* ---------- CREATOR ACTIONS ---------- */
                        if (isCreatedByMe) {

                            // Already fully completed
                            if (row.status === 'Completed') {
                                return '<span class="text-success"><i class="fa fa-check-circle"></i> Completed</span>';
                            }

                            let html = '';

                            // Reverse allowed only if assigned user already completed
                            if (row.completed_by_assigned && row.completed_by_assigned != '0') {
                                html += `
                                <button class="btn btn-warning btn-xs reverse-dep"
                                    data-id="${row.wd_id}">
                                    Reverse
                                </button>
                                <br/>
                            `;
                            }

                            // Creator actions
                            html += `
                                <button class="btn btn-success btn-xs complete-dep"
                                    data-id="${row.wd_id}">
                                    Complete
                                </button>
                                <button class="btn btn-primary btn-xs edit-dep"
                                    data-id="${row.wd_id}">
                                    Edit
                                </button>
                            `;

                            return html;
                        }

                        /* ================= ASSIGNED USER ================= */
                        if (isAssignedToMe && !isCreatedByMe) {

                            // Fully completed
                            if (row.status === 'Completed') {
                                return '<span class="text-success"><i class="fa fa-check-circle"></i> Completed</span>';
                            }

                            // Already marked complete → waiting
                            if (row.completed_by_assigned && row.completed_by_assigned != '0') {
                                return '<span class="text-info"><i class="fa fa-clock-o"></i> Waiting for approval</span>';
                            }

                            // Mark complete option
                            return `
                                <label style="margin:0;">
                                    <input type="checkbox"
                                        class="assigned-complete-checkbox"
                                        data-id="${row.wd_id}"
                                        style="transform:scale(1.3);cursor:pointer;">
                                    <span style="margin-left:5px;">Mark Complete</span>
                                </label>
                            `;
                        }


                        return '-';
                    }
                }
            ],
            "order": [
                [8, "desc"]
            ], // Sort by created_date descending
            "pageLength": 25,
            "bSort": false,
            "dom": exportButtons.length ? 'Blfrtip' : 'lfrtip',
            "buttons": [
                'excelHtml5',
                'csvHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                        format: {
                            body: function(data) {
                                if (data === null || data === undefined) {
                                    return '';
                                }
                                return String(data).replace(/<[^>]*>/g, '');
                            }
                        }
                    },
                    customize: function(doc) {
                        doc.defaultStyle.fontSize = 5;
                        doc.styles.tableHeader.fontSize = 8;
                        doc.pageMargins = [10, 10, 10, 10];

                        let columnCount = doc.content[1].table.body[0].length;
                        var widths = [];
                        widths.push('5%'); // first column small

                        for (var i = 1; i < columnCount; i++) {
                            widths.push('*');
                        }

                        doc.content[1].table.widths = widths;
                    }
                },
                'print'
            ],

            "oLanguage": {
                "sEmptyTable": "No dependencies found for your criteria."
            },
            "initComplete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            "createdRow": function(row, data, dataIndex) {
                try {
                    var dep_leader_ids = data.dep_leader_ids ? data.dep_leader_ids.split(',') : [];
                    var created_by_id = data.created_by_id;

                    var isAssignedToMe = dep_leader_ids.includes(logged_id);
                    var isCreatedByMe = (created_by_id == logged_id);

                    $(row).removeAttr('style');

                    // Highlight for assigned users (yellow background)
                    if (isAssignedToMe && !isCreatedByMe) {
                        $(row).addClass('bg-warning text-dark');
                    }

                    // Special highlight for creator when dependency completed by assigned user (light blue background)
                    // if (isCreatedByMe && data.status === 'Completed' && data.completed_by_assigned && data.completed_by_assigned != '0') {
                    //     $(row).css({
                    //         'background-color': '#d9edf7',
                    //         'border-left': '4px solid #31708f'
                    //     });
                    // }
                    if (data.completed_by_assigned && data.completed_by_assigned != '0') {

                        var diff = parseInt(data.completed_day_diff, 10);
                        var bgColor = '';
                        var borderColor = '';
                        var textColor = '';

                        if (diff < 0) {
                            bgColor = '#d9edf7'; // blue (early)
                            borderColor = '#31708f';
                            textColor = '#31708f';
                        } else if (diff === 0) {
                            bgColor = '#d4edda'; // green (on time)
                            borderColor = '#28a745';
                            textColor = '#28a745';
                        } else if (diff === 1) {
                            bgColor = '#fff3cd'; // orange (1 day delay)
                            borderColor = '#fd7e14';
                            textColor = '#fd7e14';
                        } else if (diff > 1) {
                            bgColor = '#f8d7da'; // red (delay)
                            borderColor = '#dc3545';
                            textColor = '#dc3545';
                        }

                        /* ================= ASSIGNED USER ================= */
                        if (isAssignedToMe && !isCreatedByMe) {
                            $(row).css({
                                'background-color': bgColor,
                                'border-left': '5px solid ' + borderColor
                            });
                        }

                        /* ================= CREATOR ================= */
                        if (isCreatedByMe) {
                            $(row)
                                .find('td')
                                .eq(8) // STATUS column
                                .css({
                                    'color': textColor,
                                    'font-weight': '600'
                                });
                        }
                    }
                } catch (e) {
                    console.error('Row highlight error:', e);
                }
            }
        });
    }

    function ResetFilters() {
        $('#filter_project').val('');
        $('#filter_status').val('');
        $('#filter_created_by').val('');
        $('#filter_createdby').val('');
        $('#filter_assigned_to').val('');
        $('#filter_from_date').val('');
        $('#filter_to_date').val('');
        $('#filter_type').val('');
        $('#filter_priority').val('');
        $('#filter_viewtype').val('My');
        LoadData();

    }
</script>