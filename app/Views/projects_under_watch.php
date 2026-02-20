<div class="content-wrapper">
    <section class="content-header">
        <h1>Projects Under Watch</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-9">
                        <h3 class="box-title">All Projects Under Watch</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table id="dataTableWatch" class="table table-bordered table-hover responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Start Date</th>
                            <th>Total Cost</th>
                            <th>Expenses</th>
                            <th>Profit/Loss</th>
                            <th>Status</th>
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
    var dataTableWatch = null;

    function document_ready() {
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            ajax: {
                url: "<?php echo base_url('api/dashboard'); ?>",
                method: "post",
                data: {
                    "type": "under_watch_all"
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
                "sEmptyTable": "No projects under watch.",
            },
        };
        if (dataTableWatch != null) {
            dataTableWatch.destroy();
        }
        dataTableWatch = loadDataTable('#dataTableWatch', dtConf);
    }
</script>
