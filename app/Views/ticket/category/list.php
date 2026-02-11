<?php
$categories = $view_data['categories'];
?>

<h1>Category List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Ticket Categories</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Category List</h3>
                    </div>
                    <div class="col-md-5">
                        <a href="<?php echo site_url('ticket-category/add/'); ?>"
                            class="btn btn-primary pull-right">Add New Category</a>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <form action="<?php echo site_url('ticket-category'); ?>" method="post">
                            Filter: <input class="form-control" name="search" id="search"
                                style="width:350px; display:inline" value="<?= $view_data['search'] ?>" placeholder="Search with Title Or Address Or Status Or Creator Name" />
                            <!-- <button type="button" id="main_add_button" onclick="LoadData();"
                                class="btn btn-primary margin">Show Conferences</button> -->
                            <button type="submit" class="btn btn-primary">Show Categories</button>
                        </form>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Parent Category</th>
                                    <th>Status</th>
                                    <th>Assign Users</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['name']) ?></td>
                                            <td>
                                                <?php
                                                if ($category['parent_id']) {
                                                    foreach ($categories as $parent) {
                                                        if ($parent['id'] == $category['parent_id']) {
                                                            echo htmlspecialchars($parent['name']);
                                                            break;
                                                        }
                                                    }
                                                } else {
                                                    echo 'None';
                                                }
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($category['status']) ?></td>
                                            <td>
                                                <?= !empty($category['assigned_users']) ? htmlspecialchars($category['assigned_users']) : 'None'; ?>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('ticket-category/edit/' . $category['id']) ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i></a>
                                                <a href="<?= site_url('ticket-category/delete/' . $category['id']) ?>" class="btn btn-danger btn-md" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;">No Ticket Categories found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->