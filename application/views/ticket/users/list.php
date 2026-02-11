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
                        <a href="<?php echo site_url('ticket/cat/add'); ?>"
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
                                    <th>Title</th>
                                    <th>status</th>
                                    <th>Users</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                                <?php if (!empty($categories)) {
                                    if ($categories['status'] == 'error') {
                                        print $categories['message'];
                                    } else { ?>
                                        <ul>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?= $category['name'] ?></td>
                                                    <td><?= $category['status'] ?></td>
                                                    <td><?= !empty($category['assigned_users']) ? $category['assigned_users'] : 'No Users Assigned'; ?></td>
                                                    <td><a href="<?php echo site_url('ticket/cat/edit/' . $category['id']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>
                                                                <a class="btn btn-danger btn-md" href="<?php echo site_url('ticket/cat/delete/' . $category['id']); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a>
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