<?php
$categories  = $view_data['data']['categories'];
$users = $view_data['data']['users'];
?>

<h1>Ticket Category</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Add Category</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Add Category</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <form method="post" action="<?= base_url('ticket-category/store') ?>">

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Parent Category</label>
                        <select name="parent_id" class="form-control">
                            <option value="">None</option>
                            <?php foreach ($categories as $parent): ?>
                                <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" id="userSearch" class="form-control" placeholder="Search users..."><br />
                        </select>
                        <div class="row" id="userCheckboxList">
                            <?php foreach (array_chunk($users, ceil(count($users) / 3)) as $user_chunk): ?>
                                <div class="col-md-4">
                                    <?php foreach ($user_chunk as $user): ?>
                                        <div class="checkbox user-checkbox">
                                            <label>
                                                <input type="checkbox" name="assigned_users[]" value="<?= $user['u_id'] ?>">
                                                <span class="user-text"><?= htmlspecialchars($user['u_name']) ?> - <?= htmlspecialchars($user['u_type']) ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <input type="submit" value="Save" class="btn btn-primary">
                    </form>
                    <form id="redirectForm" action="<?= base_url('ticket-category'); ?>" method="post">
                        <br /><button type="submit" class="btn btn-primary">Back</button>
                    </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

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