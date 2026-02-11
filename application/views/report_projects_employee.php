<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Project Employee Report</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h4 class="box-title"></h4>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-xs-12">
                        Filter:
                        <select class="form-control project-select" id="txt_projects" name="txt_projects" style="width:auto; display:inline">
                            <option value="">Select Project</option>
                            <?php
                            foreach ($view_data['projects'] as $val) {
                                echo '<option value="' . $val['p_name'] . '">' . $val['p_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <?php if (in_array($view_data['u_type'], ['Master Admin', 'Bim Head'])) { ?>
                            <select class="form-control" id="txt_employee" name="txt_employee" style="width:auto; display:inline">
                                <option value="">Select Employees</option>
                                <?php
                                foreach ($view_data['users'] as $val) {
                                    echo '<option value="' . $val['u_id'] . '">' . $val['u_name'] . '</option>';
                                }
                                ?>
                            </select>
                        <?php } ?>
                        <!-- <input class="form-control" name="txt_search" id="txt_search" style="width:150px; display:inline" placeholder="Employee Name" />  -->
                        <select class="form-control" id="txt_status" name="txt_status" style="width:auto; display:inline">
                            <option value="">Select Status</option>
                            <option value="New">New</option>
                            <option value="Inprogress">Inprogress</option>
                            <option value="Completed">Completed</option>
                            <option value="Hold">Hold</option>
                        </select>
                        <button type="button" id="main_add_button" onclick="LoadData();" class="btn btn-primary margin">Show Tasks</button>
                    </div>
                </div>

                <br />
                <table id="dataTable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Project</th>
                            <th>Task Title</th>
                            <th>Main Task/ Sub Task</th>
                            <th>Priority</th>
                            <th>Posted Date</th>
                            <th>Posted By</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Hours Worked</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="admin_list">
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</div><!-- /.content-wrapper -->
<div class="admin_add_modal" style="display: none;">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <table id="dataTableLogHoureport" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <thead>
                        <tr>
                            <th>User</th>
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
</div>
<script>
    var dataTable = null;
    var dataTableLogHoureport = null;

    function document_ready() {
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/reports'); ?>",
                method: "post",
                data: {
                    "type": "projects_employee",
                    "txt_projects": $("#txt_projects").val(),
                    "txt_status": $("#txt_status").val(),
                    "txt_employee": $("#txt_employee").val(),
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
            "columnDefs": [
                /*{
                                "targets": [0],
                                "searchable": false,
                                'bSortable': true,
                                "orderable": true,
                            }, */
                {
                    "targets": [0, 1, 2, 3, 4],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }
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

    function showData(t_id, t_p_id, p_name, t_name, emp_id = null) {
        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';
        if (parseInt(t_id) > 0) {
            showModal('html', html, p_name + ' - ' + t_name, 'modal', 'modal-lg', function() {
                var dtConf = {
                    "ajax": {
                        url: "<?php echo base_url('api/tasks'); ?>",
                        method: "post",
                        data: {
                            "act": "t_loghours",
                            "t_id": t_id,
                            "emp_id": emp_id,
                            "p_name": p_name,
                            "t_name": t_name,
                            "callfrom": 'report',
                        },
                    },
                    // "bPaginate": false,
                    //"bInfo": false,
                    pageLength: -1,
                    stripeClasses: ['r0', 'r1'],
                    bSort: false,
                    dom: 'Blfrtip',
                    "buttons": true,
                    "columnDefs": [{
                        "targets": [0, 1, 2, 3, 4],
                        "searchable": false,
                        'bSortable': false,
                        "orderable": false,
                    }],
                    "oLanguage": {
                        "sEmptyTable": "There is not any <b>Records</b> for your criteria.",
                    },
                };
                if (dataTableLogHoureport != null) {
                    dataTableLogHoureport.destroy();
                }
                dataTableLogHoureport = loadDataTable('#admin_add_form #dataTableLogHoureport', dtConf);
            });
        }
    }
</script>