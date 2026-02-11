<?php
$categories  = $view_data['data']['categories'];
$users = $view_data['data']['users'];
$category = $view_data['data']['category'];
$assigned_users = $view_data['data']['assigned_users'];
?>

<h1>Ticket Category</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Add Category</h1>
    </section>
    <h1>Edit Ticket Category</h1>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Edit Category</h1>
        </section>
        <section class="content">
            <div class="box box-sbpink">
                <div class="box-header">
                    <div class="row">
                        <div class="col-md-7">
                            <h3 class="box-title">Edit Category</h3>
                        </div>
                    </div>
                </div>
                <div class="box-body">

                    <form method="post" action="<?= base_url('ticket-category/update/' . $category->id) ?>">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?= $category->name ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control"><?= $category->description ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Parent Category:</label>
                            <select name="parent_id" class="form-control">
                                <option value="">None</option>
                                <?php foreach ($categories as $parent): ?>
                                    <?php if ($parent->id != $category->id): ?>
                                        <option value="<?= $parent->id ?>"
                                            <?php echo ($category->parent_id == $parent->id) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($parent->name) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?php echo ($category->status == 'Active') ? 'selected' : ' '; ?>>Active</option>
                                <option value="Deactive" <?php echo ($category->status == 'Deactive') ? 'selected' : ''; ?>>Deactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Assign Users</label> <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
                            <div class="row" id="userCheckboxList">
                                <?php foreach (array_chunk($users, ceil(count($users) / 3)) as $user_chunk): ?>
                                    <div class="col-md-4">
                                        <?php foreach ($user_chunk as $user): ?>
                                            <div class="checkbox user-checkbox">
                                                <label>
                                                    <input type="checkbox" name="assigned_users[]" value="<?= $user['u_id'] ?>"
                                                        <?= in_array($user['u_id'], $assigned_users) ? 'checked' : '' ?>>
                                                    <span class="user-text"><?= htmlspecialchars($user['u_name']) ?> (<?= $user['u_type'] ?>)</span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>


                        <input type="submit" value="Update" class="btn btn-primary">
                    </form>
                    <form action=" <?= base_url('ticket-category') ?>" method="get">
                        <br><button type="submit" class="btn btn-primary">Back</button>
                    </form>
                </div>
                <div class="box-footer"></div>
            </div>
        </section>
    </div>


</div><!-- /.content-wrapper -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('.select2').select2({
        placeholder: "Select Users",
        allowClear: true
    });
    $('#userSearch').on('keyup', function() {
        var keyword = $(this).val().toLowerCase();

        $('.user-checkbox').each(function() {
            var userText = $(this).find('.user-text').text().toLowerCase();

            if (userText.includes(keyword)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
</script>