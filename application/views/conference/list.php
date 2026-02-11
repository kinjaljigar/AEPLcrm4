<?php $conferences = $view_data['conferences'];
?>

<h1>Conference List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Conferences</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Conference List</h3>
                    </div>
                    <div class="col-md-5">
                        <a href="<?php echo site_url('conference/add'); ?>"
                            class="btn btn-primary pull-right">Add New Conference</a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <form action="<?php echo site_url('conference'); ?>" method="post">
                            Filter: <input class="form-control" name="search" id="search"
                                style="width:350px; display:inline" value="<?= $view_data['search'] ?>" placeholder="Search with Title Or Date Or Time" />
                            <select name="data" id="data" class="form-control" style="width:auto; display:inline">
                                <option value="upcoming" <?php echo ($view_data['dataURL'] == 'upcoming') ? 'selected' : ''; ?>>upcoming</option>
                                <option value="archived" <?php echo ($view_data['dataURL'] == 'archived') ? 'selected' : ''; ?>>archived</option>
                            </select>
                            <!-- <button type=" button" id="main_add_button" onclick="LoadData();"
                                class="btn btn-primary margin">Show Conferences</button> -->
                            <button type="submit" class="btn btn-primary">Show Conferences</button>
                        </form>
                    </div>
                </div><br />
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Created By</th>
                                    <th>room_id</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                                <?php if (!empty($conferences['data'])) {
                                    if ($conferences['status'] == 'error') {
                                        print $conferences['message'];
                                    } else { ?>
                                        <ul>
                                            <?php foreach ($conferences['data'] as $conference): ?>
                                                <tr>
                                                    <td><?= $conference['title'] ?></td>
                                                    <td><?= date('d-m-Y', strtotime($conference['date'])) ?></td>
                                                    <td><?= $conference['timeslot_values'] ?></td>
                                                    <td><?= $conference['u_name'] ?></td>
                                                    <td><?= $conference['room_id'] ?></td>
                                                    <td>
                                                        <?php if ($view_data['dataURL'] != 'archived' && $conference['edit'] == 1) { ?>
                                                            <a href="<?php echo site_url('conference/edit/' . $conference['id']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>
                                                                <?php
                                                            } else {
                                                                ?><a href="<?php echo site_url('conference/view/' . $conference['id']) . "?data=" . $view_data['dataURL'] ?>" class="btn btn-success btn-md"><i class="fa fa-eye"></i><a><?php
                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                        if ($conference['delete'] == 1) { ?>
                                                                            <a class="btn btn-danger btn-md" href="<?php echo site_url('conference/delete/' . $conference['id']); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a><?php } ?>
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