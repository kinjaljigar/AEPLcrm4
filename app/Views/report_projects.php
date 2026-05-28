<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Project Report</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h4 class="box-title"></h4>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-md-12">
                        <select class="form-control" id="txt_p_cat" name="txt_p_cat" style="width:120px; display:inline-block; vertical-align:middle">
                            <option value="">All Categories</option>
                            <?php
                            foreach ($view_data['p_cat'] as $val) {
                                echo '<option value="' . $val . '">' . $val . '</option>';
                            }
                            ?>
                        </select>
                        <select class="form-control" id="txt_p_status" name="txt_p_status"
                            style="width:110px; display:inline-block; vertical-align:middle">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Hold">Hold</option>
                            <option value="Completed">Completed</option>
                        </select>
                        <select class="form-control project-select" id="t_p_id" name="t_p_id"
                            style="width:280px; display:inline-block; vertical-align:middle">
                            <option value="">-- All Projects --</option>
                            <?php foreach ($view_data['projects'] as $p) {
                                echo '<option value="' . $p['p_id'] . '">' . htmlspecialchars($p['p_name']) . ' (' . htmlspecialchars($p['p_number'] ?? '') . ')</option>';
                            } ?>
                        </select>

                        <button type="button" id="main_add_button" onclick="LoadData();"
                            class="btn btn-primary margin">Show Report</button>
                        <button type="button" onclick="ExportExcel();"
                            class="btn btn-success margin"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                    </div>
                </div>

                <!-- Hidden form for Excel export -->
                <form id="export_form" action="<?php echo base_url('home/export_project_report'); ?>" method="post" target="_blank" style="display:none;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="exp_txt_p_cat" name="txt_p_cat" />
                    <input type="hidden" id="exp_txt_p_status" name="txt_p_status" />
                    <input type="hidden" id="exp_t_p_id" name="t_p_id" />
                </form>

                <br />
                <table id="dataTable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Project Name</th>
                            <th>Project Number</th>
                            <!-- <th>Task Title</th>
                                <th>Priority</th>
                                <th>Posted Date</th>
                                -->
                            <!-- <th>Assigned To</th> --><?php if ($view_data['admin_session']['u_type'] == 'Master Admin') { ?>
                            <th>Project Value</th>
                            <?php } ?>
                            <th>Project Category</th>
                            <th>Status</th>
                            <th> Tasks</th>


                        </tr>
                    </thead>
                    <tbody class="admin_list">
                        <td colspan="4">Please Enter <b>Project Name</b> OR <b>Project Number</b> For Getting Data.</td>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</div><!-- /.content-wrapper -->

<script>
var dataTable = null;

function document_ready() {
    // LoadData();
}

function ExportExcel() {
    $('#exp_txt_p_cat').val($('#txt_p_cat').val());
    $('#exp_txt_p_status').val($('#txt_p_status').val());
    $('#exp_t_p_id').val($('#t_p_id').val());
    $('#export_form').submit();
}

function LoadData() {
    var dtConf = {
        "ajax": {
            url: "<?php echo base_url('api/reports'); ?>",
            method: "post",
            data: {
                "type": "projects",
                "txt_p_status": $("#txt_p_status").val(),
                "t_p_id": $("#t_p_id").val(),
                "txt_p_cat": $("#txt_p_cat").val(),
            }
        },
        "processing": true,
        serverSide: true,
        paging: false,
        //info: false,
        bLengthChange: false,
        pageLength: 10000,
        stripeClasses: ['r0', 'r1'],
        bSort: false,
        dom: 'lfrtip',
        "columnDefs": [
            /*{
                            "targets": [0],
                            "searchable": false,
                            'bSortable': true,
                            "orderable": true,
                        }, */
            // "targets": [0, 1 , 2, 3, 4 ,5],


        ],
        "oLanguage": {
            "sEmptyTable": "There is not any <b>Tasks</b> added with your criteria.",
        },
    };
    if (dataTable != null) {
        dataTable.destroy();
    }
    dataTable = loadDataTable('#dataTable', dtConf);
}
</script>