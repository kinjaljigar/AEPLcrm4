<div class="content-wrapper">
    <section class="content-header">
        <h1>Employee Project Work Report</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h4 class="box-title"></h4>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-xs-12">
                        Filter:&nbsp;

                        <?php if (in_array($view_data['u_type'], ['Master Admin', 'Super Admin', 'Bim Head', 'Project Leader'])) { ?>
                            <select class="form-control" id="emp_u_id" name="emp_u_id" style="width:200px; display:inline-block; vertical-align:middle">
                                <option value="">-- All Employees --</option>
                                <?php foreach ($view_data['users'] as $u) {
                                    echo '<option value="' . $u['u_id'] . '">' . htmlspecialchars($u['u_name']) . '</option>';
                                } ?>
                            </select>
                        <?php } else { ?>
                            <input type="hidden" id="emp_u_id" value="<?php echo $view_data['admin_session']['u_id']; ?>" />
                        <?php } ?>

                        <select class="form-control project-select" id="p_id" name="p_id" style="width:250px; display:inline-block; vertical-align:middle">
                            <option value="">-- All Projects --</option>
                            <?php foreach ($view_data['projects'] as $p) {
                                echo '<option value="' . $p['p_id'] . '">' . htmlspecialchars($p['p_name']) . '</option>';
                            } ?>
                        </select>

                        <input type="text" id="rpt_start" name="rpt_start" class="form-control datepicker"
                            style="width:120px; display:inline" placeholder="From Date"
                            value="<?php echo $view_data['rpt_start']; ?>" />
                        <input type="text" id="rpt_end" name="rpt_end" class="form-control datepicker"
                            style="width:120px; display:inline" placeholder="To Date"
                            value="<?php echo $view_data['rpt_end']; ?>" />

                        <button type="button" onclick="LoadData();" class="btn btn-primary margin">
                            <i class="fa fa-search"></i> Show Report
                        </button>
                        <button type="button" onclick="ExportData();" class="btn btn-success margin">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </button>
                    </div>
                </div>

                <br />
                <div id="report_table_wrap">
                    <table id="dataTable" class="table table-bordered table-hover responsive nowrap" width="100%">
                        <thead>
                            <tr id="dataTable_head">
                                <th>Sr.</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Project Name</th>
                                <th>Task Name</th>
                                <th>Time</th>
                                <!-- <th>Hours</th> -->
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="7">Please select a <b>Project</b> (or Employee) and click <b>Show Report</b>. Date range is optional.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div><!-- /.content-wrapper -->

<script>
var dataTable = null;

function document_ready() {
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    });

    // auto-load for non-admin (employee sees own data)
    <?php if (!in_array($view_data['u_type'], ['Master Admin', 'Super Admin', 'Bim Head', 'Project Leader'])) { ?>
    LoadData();
    <?php } ?>
}


function ExportData() {
    var emp  = $('#emp_u_id').val();
    var proj = $('#p_id').val();
    if (!emp && !proj) { alert('Please select an Employee or a Project.'); return; }
    var rs = $('#rpt_start').val();
    var re = $('#rpt_end').val();
    var mode = (proj !== '') ? 'summary' : 'detail';
    var url = "<?php echo base_url('api/export_employee_work'); ?>"
        + "?emp_u_id=" + encodeURIComponent(emp)
        + "&p_id=" + encodeURIComponent(proj)
        + "&rpt_start=" + encodeURIComponent(rs)
        + "&rpt_end=" + encodeURIComponent(re)
        + "&mode=" + mode;
    window.open(url, '_blank');
}

function LoadData() {
    var emp  = $('#emp_u_id').val();
    var proj = $('#p_id').val();
    if (!emp && !proj) { alert('Please select an Employee or a Project.'); return; }
    var rs = $('#rpt_start').val();
    var re = $('#rpt_end').val();

    // Summary mode: any time a project is selected
    var isSummary = (proj !== '');
    var mode = isSummary ? 'summary' : 'detail';

    if (isSummary) {
        $('#dataTable_head').html('<th>Sr.</th><th>Task Name</th><th>Employee</th><th>Date</th><th>Time</th><!-- <th>Hours</th> --><th>Comment</th>');
    } else {
        $('#dataTable_head').html('<th>Sr.</th><th>Date</th><th>Employee</th><th>Project Name</th><th>Task Name</th><th>Time</th><!-- <th>Hours</th> --><th>Comment</th>');
    }

    var colCount = isSummary ? 6 : 7;
    var targets  = [];
    for (var c = 0; c < colCount; c++) targets.push(c);

    var dtConf = {
        "ajax": {
            url: "<?php echo base_url('api/reports'); ?>",
            method: "post",
            data: {
                "type": "employee_work",
                "emp_u_id": emp,
                "p_id": proj,
                "rpt_start": rs,
                "rpt_end": re,
                "mode": mode,
            }
        },
        "processing": true,
        serverSide: true,
        paging: false,
        bLengthChange: false,
        pageLength: 10000,
        stripeClasses: ['r0', 'r1'],
        bSort: false,
        dom: 'lfrtip',
        "columnDefs": [{"targets": targets, "searchable": false, "orderable": false}],
        "oLanguage": {
            "sEmptyTable": "No work records found for the selected criteria.",
        },
    };
    if (dataTable != null) {
        dataTable.destroy();
    }
    dataTable = loadDataTable('#dataTable', dtConf);
}
</script>
