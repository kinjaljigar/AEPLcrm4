<?php $tasks = $view_data['tasks']; ?>
<table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
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
                $db = \Config\Database::connect();
                $uid = $view_data['admin_session']['u_id'];
        ?>
                <ul>
                    <?php foreach ($tasks['data'] as $task): ?>
                        <?php
                        // check in aa_project_task_users if task is completed by this user
                        $task_user_row = $db->table('aa_project_task_users')
                            ->select('task_completed')
                            ->where('task_id', $task['id'])
                            ->where('u_id', $uid)
                            ->get()->getRowArray();

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