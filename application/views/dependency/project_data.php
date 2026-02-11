<?php
$projects = $view_data['projects'];
$monday = date('Y-m-d', strtotime('monday this week'));
$friday = date('Y-m-d', strtotime('friday this week'));
?>
<style>
    .inactive-option {
        color: red;
    }
</style>
<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <h1>Project Weekly Data</h1>
    </section>

    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-12">
                        <b>Leader:</b>
                        <select id="leader_id" class="form-control inline" style="width:200px;">
                            <option value="">-- Select Leader --</option>
                            <?php foreach ($view_data['leaders'] as $leader): ?>
                                <option value="<?= $leader['u_id']; ?>"
                                    class="<?= ($leader['u_status'] === 'Deactive') ? 'inactive-option' : '' ?>">
                                    <?= htmlspecialchars($leader['u_name']) ?>
                                    <?= ($leader['u_status'] === 'Deactive') ? ' - Deactive' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <b>Projects:</b>
                        <select id="project_id" class="form-control inline project-select" style="width:200px;">
                            <option value="">All Projects</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['p_id'] ?>"><?= $p['p_number'] . " - " . $p['p_name']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <b>Range:</b>
                        <select id="filter_status" class="form-control inline" style="width:200px;">
                            <option value="All">All</option>
                            <option value="PAUSE">PAUSE</option>
                            <option value="WIP">WIP</option>
                            <option value="COMPLETED">COMPLETED</option>
                            <option value="HOLD">HOLD</option>
                        </select>


                        <b>Range:</b>
                        <input type="date" id="from_date" value="<?php print $monday; ?>" class="form-control inline" style="width:150px;">
                        <input type="date" id="to_date" value="<?php print $friday; ?>" class="form-control inline" style="width:150px;">
                        <button type="button" class="btn btn-primary" onclick="LoadData()">Search</button>
                        <button type="button" class="btn btn-default" onclick="ResetFilters()">Reset</button>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <table id="project_table" class="table table-bordered table-hover responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Leader</th>
                                    <th>Team Assigned</th>
                                    <th>No. Of Persons</th>
                                    <th>Assigned Users</th>
                                    <th>No. Of Projects</th>
                                    <th>Project</th>
                                    <th>Week</th>
                                    <th>Work Summary</th>
                                    <th>Submission Date</th>
                                    <th>Status</th>
                                    <th>Dependencies</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
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

<script>
    var dataTable = null;


    function document_ready() {
        LoadData();
        $(document).on('click', '.view-dep-btn', function() {
            var w_id = $(this).data('wid');
            var type = $(this).data('type');
            const modalTitle = type === 'incomplete' ? 'Incomplete Dependencies' : 'All Dependencies';

            $.ajax({
                url: '<?= base_url("api/weeklywork"); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    act: "dependencies",
                    w_id: w_id,
                    type: type
                },
                success: function(res) {
                    if (res.status === 'pass') {
                        if (!res.data || res.data.length === 0) {
                            showModal('ok', 'No dependencies found.', 'Info', 'modal-success', 'modal-sm');
                            return;
                        }
                        const projectName = res.data[0].project_name || '-';
                        const weekFrom = res.data[0].week_from || '-';
                        const weekTo = res.data[0].week_to || '-';

                        let html = `
            <div class="mb-3">
                <strong>Project:</strong> ${projectName}<br>
                <strong>Week:</strong> ${weekFrom} to ${weekTo}
            </div>

            <div class="table-responsive">
                <table id="depTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Dependency Text</th>
                            <th>Created By</th>
                            <th>Type</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Completed Date</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

                        res.data.forEach((d, i) => {
                            html += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${d.dependency_text || '-'}</td>
                    <td>${d.created_by || '-'}</td>
                    <td>${d.dependency_type || '-'}</td>
                    <td>${d.assigned_to || '-'}</td>
                    <td>${d.priority || '-'}</td>
                    <td>
                        <span class="label ${d.status === 'Completed' ? 'label-success' : (d.status === 'In Progress' ? 'label-warning' : 'label-danger')}">
                            ${d.status || '-'}
                        </span>
                    </td>
                    <td>${d.target_date && d.target_date !== '0000-00-00' ? d.target_date : '-'}</td>
                    <td>${d.created_date || '-'}</td>
                </tr>
            `;
                        });

                        html += '</tbody></table></div>';

                        showModal('html', html, modalTitle, 'modal', 'modal-lg');

                        // Initialize DataTable with export options
                        setTimeout(() => {
                            $('#depTable').DataTable({
                                dom: 'Bfrtip',
                                buttons: [{
                                        extend: 'excel',
                                        title: `${projectName}_Dependencies`
                                    },
                                    {
                                        extend: 'csv',
                                        title: `${projectName}_Dependencies`
                                    },
                                    {
                                        extend: 'pdf',
                                        title: `${projectName}_Dependencies`
                                    },
                                    {
                                        extend: 'print',
                                        title: `${projectName} - Dependencies (${weekFrom} to ${weekTo})`
                                    }
                                ],
                                pageLength: 25,
                                responsive: true
                            });
                        }, 400);
                    } else {
                        showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                    }
                },
                error: function() {
                    showModal('ok', 'Error fetching dependencies.', 'Error', 'modal-danger', 'modal-sm');
                }
            });
        });
    }

    function LoadData() {
        var leader_id = $('#leader_id').val();
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();
        var projectId = $('#project_id').val();
        var filter_status = $('#filter_status').val();
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/reports'); ?>",
                method: "post",
                data: {
                    leader_id: leader_id,
                    from_date: from_date,
                    to_date: to_date,
                    project_id: projectId,
                    filter_status: filter_status,
                    "type": "projectData",
                }
            },

            // "bPaginate": false,
            // "bInfo": false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            dom: 'Blfrtip',
            "buttons": true,
            //"searching": true,
            "columnDefs": [

                {
                    "targets": [0, 8],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                    "targets": 6,
                    "render": function(data, type, row) {
                        if (type === 'display' && data) {
                            let shortText = data.length > 40 ? data.substr(0, 40) + '...' : data;
                            return '<span title="' + data.replace(/"/g, '&quot;') + '">' + shortText + '</span>';
                        }
                        return data;
                    }
                }
            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Time Record</b> for your criteria.",
            },
        };
        if (dataTable != null) {
            dataTable.destroy();
        }
        dataTable = loadDataTable('#project_table', dtConf);
        //       dataTable = $('#project_table').DataTable(dtConf);
    }

    function ResetFilters() {
        $('#leader_id').val('');
        $('#from_date').val('');
        $('#to_date').val('');
        LoadData();
    }
</script>