<?php $tasks = $view_data['tasks']; ?>

<h1>Task List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Tasks</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Task List</h3>
                    </div>
                    <div class="col-md-5">
                        <?php if ($view_data['admin_session']['u_app_auth'] == '1') {
                        ?><a href="<?php echo site_url('usertask/add'); ?>"
                                class="btn btn-primary pull-right">Add New Task</a><?php
                                                                                } ?>


                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <form id="taskSearchForm" action="<?php echo site_url('usertask'); ?>" method="post">
                            Filter: <input class="form-control" name="search" id="search"
                                style="width:350px; display:inline" value="<?= $view_data['search'] ?>" placeholder="Search with Title Or Date Or Time Or Creator Name " />
                            <select name="data" id="data" class="form-control" style="width:auto; display:inline">
                                <option value="active" <?php echo ($view_data['dataURL'] == 'active') ? 'selected' : ''; ?>>Completed</option>
                                <option value="pending" <?php echo ($view_data['dataURL'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            </select>
                            <!-- <button type="button" id="main_add_button" onclick="LoadData();"
                                class="btn btn-primary margin">Show Conferences</button> -->
                            <!-- <button type="submit" class="btn btn-primary">Show Tasks</button> -->
                        </form>
                    </div>
                </div><br />
                <div id="loadingImage" style="display: none; text-align:center;">
                    <img src="<?= base_url('assets/images/loading.gif') ?>" height="200" width="200" alt="Loading..." />
                </div>

                <div class="row">
                    <div class="col-md-12" id="taskResults">
                        <table id=" datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>date</th>
                                    <th>time</th>
                                    <th>Created By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                                <?php if (!empty($tasks['data'])) {
                                    if ($tasks['status'] == 'error') {
                                        print $tasks['message'];
                                    } else {
                                        $CI = &get_instance();
                                        $uid = $view_data['admin_session']['u_id'];
                                ?>
                                        <ul>
                                            <?php foreach ($tasks['data'] as $task): ?>
                                                <?php
                                                // check in aa_project_task_users if task is completed by this user
                                                $CI->db->select('task_completed');
                                                $CI->db->from('aa_project_task_users');
                                                $CI->db->where('task_id', $task['id']);
                                                $CI->db->where('u_id', $uid);
                                                $query = $CI->db->get();
                                                $task_user_row = $query->row_array();

                                                $user_task_completed = isset($task_user_row['task_completed']) && $task_user_row['task_completed'] == '1';
                                                ?>

                                                <tr>
                                                    <td><?= $task['title'] ?></td>
                                                    <td><?= date('d-m-Y', strtotime($task['date'])) ?></td>
                                                    <td><?= $task['time'] ?></td>
                                                    <td><?= $task['creator_name'] ?></td>
                                                    <td>
                                                        <?php if ($view_data['dataURL'] != 'archived' && $task['edit'] == 1) { ?>
                                                            <a href="<?php echo site_url('usertask/edit/' . $task['id'] . "?data=" . $view_data['dataURL']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i></a>
                                                            <a href="<?php echo site_url('usertask/view/' . $task['id'] . "?data=" . $view_data['dataURL']); ?>" class="btn btn-success btn-md"><i class="fa fa-eye"></i><a>
                                                                <?php
                                                            } else {
                                                                ?><a href="<?php echo site_url('usertask/view/' . $task['id'] . "?data=" . $view_data['dataURL']); ?>" class="btn btn-success btn-md"><i class="fa fa-eye"></i><a>
                                                                            <?php if (!$user_task_completed && ($task['u_id'] != $view_data['admin_session']['u_id'])) { ?>
                                                                                <a onclick="return showAddEditForm('<?php echo $task['id'] ?>')" href="javascript://" class="btn btn-primary">Complete</a>
                                                                            <?php } ?>
                                                                        <?php
                                                                    }
                                                                    if ($task['delete'] == 1) { ?>
                                                                            <a class="btn btn-danger btn-md" href="<?php echo site_url('usertask/delete/' . $task['id']); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a><?php } ?>

                                                                                <!-- <a href="<?php echo site_url('usertask/status/' . $task['id']); ?>" class="btn btn-success btn-md"><i class="fa fa-comment"></i><a> -->

                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </ul> <?php }
                                        } else {
                                                ?><tr>
                                        <td colspan="3">No Data Found</td>
                                    </tr>
                                <?php
                                        }
                                ?>
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
        <input type="hidden" name="task_id" id="task_id" value="">
        <div class="form-group">
            <label for="t_comment">Comment</label>
            <textarea class="form-control" id="t_comment" name="t_comment"></textarea>
        </div>
        <div class="box-footer">
            <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="saveTask();" class="btn btn-primary margin pull-right">Complete</button>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let typingTimer;
    const typingInterval = 800;

    function fetchTasks() {
        let formData = $('#taskSearchForm').serialize();

        $.ajax({
            url: "<?= site_url('usertask/fetchTasks') ?>", // AJAX endpoint
            type: "POST",
            data: formData,
            beforeSend: function() {
                // Show loader
                $('#loadingImage').show();
            },
            success: function(response) {
                $('#taskResults').html(response);
            },
            error: function() {
                $('#taskResults').html('<p class="text-danger">Error loading tasks. Please try again.</p>');
            },
            complete: function() {
                $('#loadingImage').hide();
            }
        });
    }

    $('#search').on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(fetchTasks, typingInterval);
    });

    $('#search').on('keydown', function() {
        clearTimeout(typingTimer);
    });

    $('#data').on('change', function() {
        fetchTasks();
    });

    $('#taskSearchForm').on('submit', function(e) {
        e.preventDefault(); // Prevent full page reload
        fetchTasks();
    });


    function showAddEditForm(id) {
        var html = '<form class="formclass" id="admin_add_form" name="admin_add_form" enctype="multipart/form-data">';
        html += $('.admin_add_modal').html();
        html += '</form>';

        showModal('html', html, 'Add Comment & Complete Task', 'modal', 'modal-md', function() {
            $('#admin_add_form #task_id').val(id);

            doAjax('usertask/status/' + id, 'GET', {}, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    $.each(record, function(key, value) {
                        $('#admin_add_form #' + key).val(value);
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        });
    }

    function saveTask() {
        var formData = $('#admin_add_form').serialize();
        var taskId = $('#admin_add_form').find('#task_id').val();
        var comment = $('#admin_add_form').find('#t_comment').val().trim();
        if (comment === '') {
            alert('Please enter a comment.');
            return;
        }

        doAjax('usertask/status/' + taskId, 'POST', formData, function(res) {
            if (res.status == 'pass') {
                showModal('ok', 'Task marked as completed successfully.', 'Success!', 'modal-success', 'modal-sm');
                setTimeout(() => {
                    window.location.href = window.location.href;
                }, 1000);
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
    }
</script>