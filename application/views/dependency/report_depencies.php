<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Dependencies Report</h3>
    </div>

    <div class="box-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Status</label>
                <select id="status_filter" class="form-control">
                    <option value="Incomplete">Incomplete (Default)</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                    <option value="All">All</option>
                </select>
            </div>
            <div class="col-md-2 mt-4">
                <button class="btn btn-primary mt-2" id="btnFilter">Filter</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="dependenciesTable" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Project</th>
                        <th>Week Range</th>
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
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function loadDependencies() {
        var status = $('#status_filter').val();

        if ($.fn.DataTable.isDataTable('#dependenciesTable')) {
            $('#dependenciesTable').DataTable().destroy();
        }

        $('#dependenciesTable').DataTable({
            ajax: {
                url: "<?= base_url('api/reports'); ?>",
                method: "POST",
                data: {
                    type: "dependenciesReport",
                    status: status
                },
                dataSrc: function(res) {
                    if (res.status === 'pass') return res.data;
                    else return [];
                }
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excel',
                    title: 'Dependencies_Report'
                },
                {
                    extend: 'csv',
                    title: 'Dependencies_Report'
                },
                {
                    extend: 'pdf',
                    title: 'Dependencies_Report'
                },
                {
                    extend: 'print',
                    title: 'Dependencies Report'
                }
            ],
            columns: [{
                    data: null,
                    render: (data, type, row, meta) => meta.row + 1
                },
                {
                    data: 'project_name'
                },
                {
                    data: null,
                    render: d => `${d.week_from || '-'} to ${d.week_to || '-'}`
                },
                {
                    data: 'dependency_text'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'dependency_type'
                },
                {
                    data: 'assigned_to'
                },
                {
                    data: 'priority'
                },
                {
                    data: 'status',
                    render: d => `<span class="label ${d === 'Completed' ? 'label-success' : (d === 'In Progress' ? 'label-warning' : 'label-danger')}">${d}</span>`
                },
                {
                    data: 'completed_date',
                    render: d => d && d !== '0000-00-00' ? d : '-'
                },
                {
                    data: 'created_date'
                }
            ],
            responsive: true,
            order: [
                [10, 'desc']
            ],
            pageLength: 25,
        });
    }

    $('#btnFilter').on('click', loadDependencies);
    $(document).ready(loadDependencies);
</script>