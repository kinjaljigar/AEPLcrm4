<style>
    /* Fix DataTables print line breaks */
    @media print {
        td {
            white-space: pre-wrap !important;
        }
    }
</style>
<div class="content-wrapper">

    <section class="content-header">
        <h1>Mail Link Report</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Project Mail Link Details</h3>
            </div>

            <div class="box-body">

                <!-- Filters -->
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-md-4">
                        <select id="filter_project" class="form-control project-select">
                            <option value="">All Projects</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="date" id="filter_date" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <select id="filter_discipline" class="form-control">
                            <option value="">ALL</option>
                            <option value="ARCH">ARCH</option>
                            <option value="MEPF">MEPF</option>
                            <option value="STR">STR</option>
                            <option value="OTHER">OTHER</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary" onclick="loadMessageReport()">Search</button>
                    </div>
                </div>

                <!-- Table -->
                <table id="messageReportTable" class="table table-bordered table-hover nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Message Date</th>
                            <th>Message</th>
                            <th>Discipline</th>
                            <th>Replies</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
    </section>
</div>
<script>
    var reportTable;

    function document_ready() {
        loadProjects();
        loadMessageReport();
    }

    function loadProjects() {
        doAjax('api/drop_get', 'POST', {
            dropobjs: [{
                type: 'Leaderassignprojects',
                active_only: true
            }]
        }, function(res) {
            if (res.status === 'pass') {
                $("#filter_project").html('<option value="">All Projects</option>' + res.data.Leaderassignprojects);
            }
        });
    }

    function loadMessageReport() {

        if (reportTable) reportTable.destroy();

        reportTable = $('#messageReportTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ordering: false,
            ajax: {
                url: "<?= base_url('api/message_report'); ?>",
                type: "POST",
                data: {
                    project_id: $("#filter_project").val(),
                    search_date: $("#filter_date").val(),
                    discipline: $("#filter_discipline").val()
                }
            },
            columnDefs: [{
                targets: 4, // Replies column index
                render: function(data, type) {
                    if (type === 'display') {
                        return data ? data.replace(/\n/g, '<br>') : '';
                    }
                    return data; // export / print stays raw
                }
            }],

            dom: 'Blfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    title: 'Message Report'
                },
                {
                    extend: 'csvHtml5',
                    title: 'Message Report'
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Message Report',
                    orientation: 'landscape'
                },
                {
                    extend: 'print',
                    title: 'Message Report'
                }
            ]
        });
    }
</script>