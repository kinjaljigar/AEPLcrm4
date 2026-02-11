<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Leave Report</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-12">

                        <b>Range:</b> <input type="text" class="form-control inline date-picker" id="rpt_start"
                            name="rpt_start" value="<?php echo $view_data['rpt_start']; ?>"
                            style="display:inline;width:90px;" readonly> to <input type="text"
                            class="form-control inline date-picker" id="rpt_end" name="rpt_end"
                            value="<?php echo $view_data['rpt_end']; ?>" style="display:inline;width:90px;" readonly>

                        <button type="button" class="btn btn-primary" onclick="LoadData()">Go</button>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        Filter: <input class="form-control" name="txt_search" id="txt_search"
                            style="width:150px; display:inline" placeholder="Employee Name" />
                        <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Employees</button>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Total Leaves ( With Half Days)</th>
                                    <th>Approved ( Taken ) ( With Half Days)</th>
                                    <th>Declined ( With Half Days)</th>
                                    <!-- <th>Employee Name</th>
                                    <th>Post Date</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Total Days</th>
                                    <th>Status</th>
                                    <th>Is Half Day ? ( First Half / Second Half)</th> -->
                                    <th>&nbsp;</th>
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
        <div class="row">
            <div class="col-sm-12">
                <table id="datatablePop" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Post Date</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Total Days</th>
                            <th>Status</th>
                            <th>Is Half Day ? ( First Half / Second Half)</th>
                            <th>Reason/Message</th>
                        </tr>
                    </thead>
                    <tbody class="admin_list">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    var dataTable = null;
    var datatablePop = null;
    var rpt_start = '<?php echo $view_data['rpt_start']; ?>';
    var rpt_end = '<?php echo $view_data['rpt_end']; ?>';

    function document_ready() {
        loadDateRange("#rpt_start", "#rpt_end");
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/reports'); ?>",
                method: "post",
                data: {
                    "type": "leave",
                    "txt_search": $("#txt_search").val(),
                    "rpt_start": $("#rpt_start").val(),
                    "rpt_end": $("#rpt_end").val(),
                }
            },
            //"bPaginate": false,
            //"bInfo": false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            dom: 'Blfrtip',
            "buttons": true,
            "columnDefs": [

                {
                    "targets": [0, 1, 2, 3],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }
            ],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Leaves</b> for your criteria.",
            },
        };
        if (dataTable != null) {
            dataTable.destroy();
        }
        dataTable = loadDataTable('#datatable', dtConf);
    }

    function showData(l_u_id, u_name, rpt_start, rpt_end) {
        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';
        if (parseInt(l_u_id) > 0) {
            showModal('html', html, u_name, 'modal', 'modal-lg', function() {
                var dtConf = {
                    "ajax": {
                        url: "<?php echo base_url('api/reports'); ?>",
                        method: "post",
                        data: {
                            "type": "leave_detail",
                            "l_u_id": l_u_id,
                            "rpt_start": rpt_start,
                            "rpt_end": rpt_end,
                        }
                    },
                    // "bPaginate": false,
                    //"bInfo": false,
                    pageLength: -1,
                    stripeClasses: ['r0', 'r1'],
                    bSort: false,
                    dom: 'Blfrtip',
                    "buttons": true,
                    "columnDefs": [{
                        "targets": [0, 1, 2, 3, 4, 5, 6],
                        "searchable": false,
                        'bSortable': false,
                        "orderable": false,
                    }],
                    "oLanguage": {
                        "sEmptyTable": "There is not any <b>Records</b> for your criteria.",
                    },
                };
                if (datatablePop != null) {
                    datatablePop.destroy();
                }
                datatablePop = loadDataTable('#admin_add_form #datatablePop', dtConf);
            });
        }
    }
</script>