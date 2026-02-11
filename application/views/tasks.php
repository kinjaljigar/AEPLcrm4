<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Tasks</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h4 class="box-title"></h4>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-xs-12">
                        <div class="col-md-3">
                            <select class="form-control project-select" id="txt_projects" name="txt_projects"
                                style="width:auto; display:inline">
                                <option value="">Select Project</option>
                                <?php
                                foreach ($view_data['projects'] as $val) {
                                    echo '<option value="' . $val['p_name'] . '">' . $val['p_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <?php if (in_array($view_data['u_type'], ['Master Admin', 'Bim Head'])) { ?><div class="col-md-3">
                                <select class="form-control" id="txt_employee" name="txt_employee">
                                    <option value="">Select Employees</option>
                                    <?php
                                    foreach ($view_data['users'] as $val) {
                                        echo '<option value="' . $val['u_id'] . '">' . $val['u_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php } ?>

                        <div class="col-md-3">
                            <!-- <input class="form-control" name="txt_search" id="txt_search" style="width:150px; display:inline" placeholder="Employee Name" />  -->
                            <select class="form-control" id="txt_status" name="txt_status">
                                <option value="">Select Status</option>
                                <option value="New">New</option>
                                <option value="Inprogress">Inprogress</option>
                                <option value="Completed">Completed</option>
                                <option value="Hold">Hold</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="main_add_button" onclick="LoadData();"
                                class="btn btn-primary">Show Tasks</button>
                        </div></div>
                    </div>
                </div>

                <br />
                <table id="dataTable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Project</th>
                            <th>Task Title</th>
                            <th>Priority</th>
                            <th>Posted Date</th>
                            <th>Posted By</th>
                            <th>Assigned To</th>
                            <th>Status</th>
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

<script>
    var dataTable = null;

    function document_ready() {
        LoadData();
    }

    function LoadData() {
        var dtConf = {
            "ajax": {
                url: "<?php echo base_url('api/tasks'); ?>",
                method: "post",
                data: {
                    "act": "list",
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

    function deleteRecord(id, p_id) {
        showModal('confirm',
            'Are you sure , you want to delete this <b>Task</b>?<br/><br/>Deleting this task will delete all files and messages related to this task.',
            'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/tasks', 'POST', {
                    t_id: id,
                    act: "del"
                }, function(res) {
                    if (res.status == 'pass') {
                        showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                            function() {
                                eval('dataTable' + p_id + '.ajax.reload();');
                            });
                    } else {
                        showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                    }
                });
            });
    }
</script>