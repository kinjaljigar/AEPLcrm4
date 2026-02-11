<?php $task = $view_data['task'];
$allusers = $view_data['allusers'];
?>

<h1>Edit Task</h1>
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
                        <h3 class="box-title">Edit Task</h3>
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
                <form action="<?php echo site_url('usertask/update/' . $task['id']); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Task Name</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $task['title']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Description</label>
                        <input type="text" name="description" class="form-control" value="<?php echo $task['description']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo $task['date']; ?>" required style="width: 40%;">
                    </div>
                    <div class="form-group">
                        <label for="date">Time</label>
                        <input type="text" name="time" id="time" class="form-control" value="<?php echo $task['time']; ?>" required step="1" style="width: 40%;">
                        <small class="form-text text-muted">Must be in HH:mm:ss ( 24 hrs )</small>
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
                                        <!-- Checkbox to mark for deletion -->
                                        <label style="color:red;">
                                            <input type="checkbox" name="delete_attachments[]" value="<?= htmlspecialchars($file) ?>">
                                            Delete
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <label>Upload More Attachments:</label>
                        <?php else: ?>
                            <label>Upload Attachments:</label>
                        <?php endif; ?>

                        <input type="file" name="attachments[]" multiple class="form-control">
                    </div>


                    <div class="form-group">
                        <label for="user_ids">Users</label>
                        <select name="user_ids[]" id="user_ids" style="height: 200px; width: 100%; font-size: 16px;" class="form-control" multiple required>
                            <?php $selected_ids = explode(',', $task['user_ids']); ?>
                            <?php foreach ($allusers as $user): ?>
                                <?php if ($user['u_status'] == 'Active') { ?>
                                    <?php if ($user['u_type'] == 'Associate User') {
                                        $companyname = "<b>" . $user['company_name'] . "</b> - ";
                                    } else
                                        $companyname = ''; ?>
                                    <?php $is_selected = in_array($user['u_id'], $selected_ids) ? 'selected' : ''; ?>
                                    <option value="<?= $user['u_id'] ?>" <?php echo $is_selected; ?>><?= $companyname . $user['u_name'] . " - " . $user['u_type']; ?></option>
                                <?php } ?>
                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Add other fields as needed -->

                    <button type="submit" class="btn btn-primary">Update Task</button>
                </form>
                <form id="redirectForm" action="<?= base_url('usertask'); ?>" method="post">
                    <input type="hidden" name="data" value="<?= isset($_GET['data']) ? $_GET['data'] : ''; ?>">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script>
    $(document).ready(function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date').setAttribute('min', today);
    });
    $('#time').timepicker({
        timeFormat: 'HH:mm:ss',
        interval: 15,
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });
</script>