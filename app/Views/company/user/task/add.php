<?php
$token = $view_data['token'];
$allusers = $view_data['allusers'];
?>

<h1>Add Task</h1>
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
                        <h3 class="box-title">Add Task</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php
                $flashError = session()->getFlashdata('error_message');
                if (is_array($flashError)) {
                    $_msgs = [];
                    array_walk_recursive($flashError, function($v) use (&$_msgs) { $_msgs[] = $v; });
                    $flashError = implode(', ', $_msgs);
                }
                ?>
                <?php if ($flashError): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($flashError) ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo site_url('usertask/addData/'); ?>" id="Form1" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Task Name</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Description</label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" required style="width: 40%;">
                    </div>
                    <div class="form-group">
                        <label for="date">Time</label>
                        <input type="text" name="time" id="time" class="form-control" required step="1" style="width: 40%;">
                        <small class="form-text text-muted">Must be in HH:mm:ss</small>
                    </div>
                    <div class="form-group">
                        <label for="attachments">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                        <small class="form-text text-muted">Allowed types: jpg, png, pdf. Max size: 5MB each</small>
                    </div>
                    <div class="form-group">
                        <label for="user_ids">Users</label>
                        <select name="user_ids[]" id="user_ids" style="height: 200px; width: 100%; font-size: 16px;" class="form-control" multiple required>
                            <?php $selected_ids = !empty($task['user_ids']) ? explode(',', $task['user_ids']) : []; ?>
                            <?php if (!empty($allusers)): ?>
                                <?php foreach ($allusers as $user): ?>
                                    <?php if ($user['u_status'] == 'Active') { ?>
                                        <?php $is_selected = in_array($user['u_id'], $selected_ids) ? 'selected' : ''; ?>
                                        <option value="<?= $user['u_id'] ?>" <?= $is_selected; ?>>
                                            <?= $user['u_name'] . " - " . $user['u_type'] ?>
                                        </option>
                                    <?php } ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </select>

                    </div>



                    <button type="submit" class="btn btn-primary">Add Task</button>
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

        // $('#Form1').on('submit', function(e) {
        //     e.preventDefault();

        //     const formData = new FormData(this);

        //     $.ajax({
        //         url: '<?php echo site_url('usertask/addData/'); ?>',
        //         method: 'POST',
        //         data: formData,
        //         contentType: false,
        //         processData: false,
        //         success: function(res) {
        //             console.log(res);
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Upload failed:', xhr.responseText);
        //         }
        //     });
        // });
    });
    $('#time').timepicker({
        timeFormat: 'HH:mm:ss',
        interval: 15,
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });
</script>