<?php $Companies = $view_data['companyUsers'];
$usertype = $view_data['admin_session']['u_type'];
?>
<h1>company User List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>company Users</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Company User List</h3>
                    </div>
                    <div class="col-md-5">
                        <?php if ($usertype != 'Associate User') { ?>
                            <a href="<?php echo site_url('companyuser/add'); ?>"
                                class="btn btn-primary pull-right">Add New Company User</a><?php } ?>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <form action="<?php echo site_url('companyuser'); ?>" method="post">
                            Filter: <input class="form-control" name="search" id="search"
                                style="width:350px; display:inline" value="<?= $view_data['search'] ?>" placeholder="Search with Name Or Mobile Or Email Or Status Or Company Name" />
                            <!-- <button type="button" id="main_add_button" onclick="LoadData();"
                                class="btn btn-primary margin">Show Conferences</button> -->
                            <button type="submit" class="btn btn-primary">Show Comapny Users</button>
                        </form>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>User Mobile</th>
                                    <th>User Email</th>
                                    <th>User status</th>
                                    <th>Company Name</th>
                                    <th>Company Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                                <?php if (!empty($Companies['data'])) {
                                    if ($Companies['status'] == 'error') {
                                        print $Companies['message'];
                                    } else { ?>
                                        <ul>

                                            <?php foreach ($Companies['data'] as $company): ?>
                                                <?php foreach ($company['users'] as $user): ?>
                                                    <?php if ($user['u_type'] == 'Associate User') { ?>
                                                        <tr>
                                                            <td><?= $user['u_name'] ?></td>
                                                            <td><?= $user['u_mobile'] ?></td>
                                                            <td><?= $user['u_email'] ?></td>
                                                            <td><?= $user['u_status'] ?></td>
                                                            <td><?= $company['company']['company_name'] ?></td>
                                                            <td><?= $company['company']['status'] ?></td>
                                                            <td>
                                                                <?php if ($usertype != 'Associate User') { ?>
                                                                    <a href="<?php echo site_url('companyuser/edit/' . $user['u_id']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>
                                                                            <a class="btn btn-danger btn-md" href="<?php echo site_url('companyuser/delete/' . $user['u_id']); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a>
                                                                                <?php } ?>
                                                            </td>
                                                        </tr><?php } ?><?php endforeach; ?>
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