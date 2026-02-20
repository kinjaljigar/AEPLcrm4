<div class="content-wrapper">
    <section class="content-header">
        <h1>Today Employees on Leave</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-9">
                        <h3 class="box-title">Employees on Leave Today (<?php echo date('d-m-Y'); ?>)</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table id="dataTableLeavesToday" class="table table-bordered table-hover responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Is Half Day?</th>
                            <th>Is Hourly?</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody class="admin_list">
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<script>
    var dataTableLeavesToday = null;

    function document_ready() {
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            ajax: {
                url: "<?php echo base_url('api/dashboard'); ?>",
                method: "post",
                data: {
                    "type": "leavestoday_all"
                }
            },
            bPaginate: true,
            bInfo: true,
            pageLength: 25,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            dom: 'Blfrtip',
            buttons: [
                'excelHtml5',
                'csvHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                'print'
            ],
            oLanguage: {
                "sEmptyTable": "No employees on leave today.",
            },
        };
        if (dataTableLeavesToday != null) {
            dataTableLeavesToday.destroy();
        }
        dataTableLeavesToday = loadDataTable('#dataTableLeavesToday', dtConf);
    }
</script>
