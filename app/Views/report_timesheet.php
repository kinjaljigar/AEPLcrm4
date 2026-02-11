<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?php echo $view_data['page_title'] ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <input type="button" class="dt-buttons btn btn-primary" value="Back"
                            onclick="openpagewithdate('<?php echo $view_data['rpt_start']; ?>','<?php echo $view_data['rpt_end']; ?>',
                            '<?php echo $view_data['txt_search']; ?>','<?php echo $view_data['leader_id']; ?>');">
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Project</th>
                                    <th>Task</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Comment</th>
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

<script>
    var STYPE = '';
    var dataTable = null;
    var rpt_start = '<?php echo $view_data['rpt_start']; ?>';
    var rpt_end = '<?php echo $view_data['rpt_end']; ?>';
    //var rpt_end = '<?php echo $view_data['rpt_end']; ?>';
    var type = '<?php echo $view_data['type']; ?>';
    var leader_id = '<?php echo $view_data['leader_id']; ?>';

    function openpagewithdate(sdate, edate = null, txt_search = null, leader_id = null) {

        const params = new URLSearchParams();
        params.set("rpt_start", sdate);
        if (type === "daily") {
            if (txt_search && txt_search.trim() !== "") {
                params.set("txt_search", txt_search.trim());
            }
            window.location.href = "report_daily?" + params.toString();
        } else if (type === "Leaderempattendance") {
            if (edate) {
                params.set("rpt_end", edate);
            }
            if (txt_search && txt_search.trim() !== "") {
                params.set("txt_search", txt_search.trim());
            }
            if (leader_id && leader_id.trim() !== "") {
                params.set("leader_id", leader_id.trim());
            }
            window.location.href = "report_leader_employee?" + params.toString();
        } else {
            if (edate) {
                params.set("rpt_end", edate);
            }
            if (txt_search && txt_search.trim() !== "") {
                params.set("txt_search", txt_search.trim());
            }
            window.location.href = "report_employee?" + params.toString();
        }

    }

    function document_ready() {
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/reports'); ?>",
                method: "post",
                data: {
                    "type": "timesheet",
                    "rpt_start": rpt_start,
                    "rpt_end": rpt_end,
                    "u_id": <?php echo $view_data['u_id']; ?>,
                    "sub_type": '<?php echo $view_data['type']; ?>',
                }
            },
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            dom: 'Blfrtip',
            "buttons": true,
            "columnDefs": [{
                "targets": [0, 1, 2, 3, 4],
                "searchable": true,
                'bSortable': false,
                "orderable": false,
            }],
            "oLanguage": {
                "sEmptyTable": "There is not any <b>Time Record</b> added with your criteria.",
            },
        };
        if (dataTable != null) {
            dataTable.destroy();
        }
        dataTable = loadDataTable('#datatable', dtConf);
    }
</script>