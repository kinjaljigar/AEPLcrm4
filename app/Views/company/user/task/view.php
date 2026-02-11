<?php $task = $view_data['task'];
//$allusers = $view_data['allusers'];
?>

<h1>View Task</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Task</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">View Task</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php if (session()->getFlashdata('error_message')): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars(session()->getFlashdata('error_message')) ?>
                    </div>
                    <?php session()->remove('error_message'); ?>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Task Name</label><br />
                    <?php echo $task['title']; ?>
                </div>
                <div class="form-group">
                    <label for="name">Description</label><br />
                    <?php echo $task['description']; ?>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <?= date('d-m-Y', strtotime($task['date'])) ?>
                </div>
                <div class="form-group">
                    <label for="date">Time</label><br />
                    <?php echo $task['time']; ?><br />
                    <!-- <small class="form-text text-muted">Must be in HH::MM::SS</small> -->
                </div>
                <div class="form-group">
                    <label>Attachments</label><br>

                    <?php if (!empty($task['attachments'])) : ?>
                        <label>Existing Attachments:</label>
                        <ul>
                            <?php foreach ($task['attachments'] as $index => $file): ?>
                                <li>
                                    <a href="<?= $file ?>" target="_blank"><?= htmlspecialchars(basename($file)) ?></a>
                                    &nbsp;

                                </li>
                            <?php endforeach; ?>
                        </ul>

                    <?php else: ?>

                    <?php endif; ?>

                </div>
                <?php
                $user_ids = explode(',', $task['user_ids']);
                $user_names = explode(',', $task['user_names']);
                $user_types = explode(',', $task['user_types']);
                $completedtasks = explode(',', $task['completedtasks']);
                $completed_reasons = explode(',', $task['completed_reasons'] ?? '');
                $completed_reasons = array_pad($completed_reasons, count($user_ids), '');
                $completed_at = explode(',', $task['completed_at'] ?? '');
                $completed_at = array_pad($completed_at, count($user_ids), '');

                ?>

                <div class="form-group">
                    <label>Task Assigned Users</label>
                    <div style="border: 1px solid #ccc; border-radius: 5px; padding: 15px;">
                        <?php for ($i = 0; $i < count($user_ids); $i++): ?>
                            <div style="margin-bottom: 15px;">
                                <strong><?= trim($user_names[$i] ?? '') ?></strong> → <?= trim($user_types[$i] ?? '') ?><br />

                                <?php if (isset($completedtasks[$i]) && trim($completedtasks[$i]) == '1'): ?>
                                    <span class="badge badge-success">Completed</span>
                                    <?php if (!empty($completed_at[$i])): ?>
                                        <strong> At </strong> <?= date('d-m-Y H:i:s', strtotime($completed_at[$i])); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($completed_reasons[$i])): ?>
                                        <br /><strong>Reason:</strong> <?= nl2br(htmlspecialchars($completed_reasons[$i])) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>


                <!-- Add other fields as needed -->
                <!-- <a href="<?= base_url('usertask'); ?>" class="btn btn-primary">Back</a> -->
                <form id="redirectForm" action="<?= base_url('usertask'); ?>" method="post">
                    <input type="hidden" name="data" value="<?= isset($_GET['data']) ? $_GET['data'] : ''; ?>">
                    <button type="submit" class="btn btn-primary">Back</button>
                </form>

            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->