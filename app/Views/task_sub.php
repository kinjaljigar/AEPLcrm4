<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Sub Tasks for: <?php echo $view_data['task']['t_title']; ?></h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="box-title">Subtask List</h3>
                        <a href="<?php echo base_url('home/tasks'); ?>" class="btn btn-primary pull-right" style="margin-left:10px;">Back</a>
                        <a href="<?php echo base_url('home/task/add/' . $view_data['t_p_id'] . '/' . $view_data['t_id']); ?>" class="btn btn-primary pull-right">Add Sub Task</a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row ">
                    <div class="col-lg-12">
                        <table id="dataTableTask" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Project</th>
                                    <th>Task Title</th>
                                    <th>Priority</th>
                                    <th>Posted Date</th>
                                    <th>Posted By</th>
                                    <th>Assigned To</th>
                                    <th>Esti. Hours</th>
                                    <th>Status</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div><!-- /.content-wrapper -->

<script>
    var dataTableTask = null;
    function document_ready() {
        TabTask();
    }
    function TabTask()
    {
        var dtConf = {
                "ajax": {
                    url: "<?php echo base_url('api/tasks'); ?>",
                    method: "post",
                    data: {
                        "act": "list",
                        "t_parent": <?php echo $view_data['t_id'] ?>,
                    },
                },
                "processing": false,
                "serverSide": false,
                pageLength: 10,
                stripeClasses: ['r0', 'r1'],
                bSort: false,
                "columnDefs": [{
                    "targets": [0, 1, 2],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }, ],
                "oLanguage": {
                    "sEmptyTable": "There is not any <b>sub tasks</b> added here.",
                },
            };
            if (dataTableTask != null) {
                dataTableTask.destroy();
            }
            dataTableTask = loadDataTable('#dataTableTask', dtConf);
    }


    function deleteRecord(id, p_id) {
        showModal('confirm', 'Are you sure , you want to delete this <b>Task</b>?', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/tasks', 'POST', {
                    t_id: id,
                    act: "del"
                }, function(res) {
                    if (res.status == 'pass') {
                        showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                            function() {
                                dataTableTask.ajax.reload();
                            });
                    } else {
                        showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                    }
                });
            });
    }
</script>
