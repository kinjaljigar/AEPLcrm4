<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Tasks</h1>
    </section>
    <section class="content">
        <div class="box-group" id="accordion">
            <?php foreach ($view_data['projects'] as $project) { ?>
                <div class="panel box box-primary">
                    <div class="box-header with-border" id="dataTableH<?php echo $project['p_id'] ?>" onclick="Load<?php echo $project['p_id'] ?>()">
                        <h4 class="box-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne<?php echo $project['p_id'] ?>" aria-expanded="false" class="collapsed">
                                <?php echo $project['p_name'] ?> <span class="pull-right hide"><?php echo $project['p_created'] ?></span>
                            </a>
                        </h4>
                    </div>
                    <div id="collapseOne<?php echo $project['p_id'] ?>" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                        <div class="box-body">
                            <table id="dataTable<?php echo $project['p_id'] ?>" class="table table-bordered table-hover responsive nowrap" width="100% ">
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
                </div>
            <?php } ?>
        </div>
    </section>

</div><!-- /.content-wrapper -->

<script>
    var dtConf = {
        "ajax": {
            url: "<?php echo base_url('api/tasks'); ?>",
            method: "post",
            data: {
                "act": "list",
                "t_p_id":3,
            }
        },
        "processing": false,
        "serverSide": false,                
        //paging: false,
        //info: false,
        //bLengthChange: false,
        pageLength: 10,
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
    <?php foreach ($view_data['projects'] as $project) { ?>
        var dataTable<?php echo $project['p_id'] ?> = null;
    <?php } ?>
    <?php foreach ($view_data['projects'] as $project) { ?>
        function Load<?php echo $project['p_id'] ?>()
        {
            if(!$("#dataTableH<?php echo $project['p_id'] ?>").hasClass('done'))
            {
                dtConf.ajax.data.t_p_id = <?php echo $project['p_id'] ?>;
                if (dataTable<?php echo $project['p_id'] ?> != null) {
                    dataTable<?php echo $project['p_id'] ?>.destroy();
                }
                dataTable<?php echo $project['p_id'] ?> = loadDataTable('#dataTable<?php echo $project['p_id'] ?>', dtConf);
                $("#dataTableH<?php echo $project['p_id'] ?>").addClass('done');
            }
        }
    <?php } ?>


    function deleteRecord(id, p_id) {
        showModal('confirm', 'Are you sure , you want to delete this <b>Task</b>?<br/><br/>Deleting this task will delete all files and messages related to this task.', 'Confirm', 'modal-default',
            'modal-sm',
            function() {
                doAjax('api/tasks', 'POST', {
                    t_id: id,
                    act: "del"
                }, function(res) {
                    if (res.status == 'pass') {
                        showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                            function() {
                                eval('dataTable'+p_id+'.ajax.reload();');
                            });
                    } else {
                        showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                    }
                });
            });
    }
</script>