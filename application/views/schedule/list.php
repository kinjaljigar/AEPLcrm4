<?php $schedules = $view_data['schedules'];
?>

<h1>Schedule List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Schedules</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Schedule List</h3>
                    </div>
                    <div class="col-md-5">
                        <a href="<?php echo site_url('schedule/add'); ?>"
                            class="btn btn-primary pull-right">Add New schedule</a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <form action="<?php echo site_url('schedule'); ?>" method="post">
                            Filter: <input class="form-control" name="search" id="search"
                                style="width:350px; display:inline" value="<?= $view_data['search'] ?>" placeholder="Search with Title Or Date Or Time" />
                            <select name="data" id="data" class="form-control" style="width:auto; display:inline">
                                <option value="upcoming" <?php echo ($view_data['dataURL'] == 'upcoming') ? 'selected' : ''; ?>>upcoming</option>
                                <option value="archived" <?php echo ($view_data['dataURL'] == 'archived') ? 'selected' : ''; ?>>archived</option>
                            </select>

                            <select name="type" id="type" class="form-control" style="width:auto; display:inline">
                                <option value="mydata" <?php echo ($view_data['type'] == 'mydata') ? 'selected' : ''; ?>>My Schedules</option>
                                <option value="all" <?php echo ($view_data['type'] == 'all') ? 'selected' : ''; ?>>All Schedules</option>
                            </select>
                            <!-- <button type=" button" id="main_add_button" onclick="LoadData();"
                                class="btn btn-primary margin">Show schedules</button> -->
                            <button type="submit" class="btn btn-primary">Show schedules</button>
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
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                                <?php if (!empty($schedules['data'])) {
                                    if ($schedules['status'] == 'error') {
                                        print $schedules['message'];
                                    } else { ?>
                                        <ul>
                                            <?php foreach ($schedules['data'] as $schedule): ?>
                                                <tr>
                                                    <td><?= $schedule['title'] ?></td>
                                                    <td><?= date('d-m-Y', strtotime($schedule['date'])) ?></td>
                                                    <td><?= $schedule['timeslot_values'] ?></td>
                                                    <td><?= $schedule['u_name'] ?></td>
                                                    <td>
                                                        <?php if ($view_data['dataURL'] != 'archived' && $schedule['edit'] == 1) { ?>
                                                            <a href="<?php echo site_url('schedule/edit/' . $schedule['id']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>
                                                                <?php
                                                            } else {
                                                                ?><a href="<?php echo site_url('schedule/view/' . $schedule['id']) . "?data=" . $view_data['dataURL'] . "&type=" . $view_data['type'] ?>" class="btn btn-success btn-md"><i class="fa fa-eye"></i><a><?php
                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                    if ($schedule['delete'] == 1) { ?>
                                                                            <a class="btn btn-danger btn-md" href="<?php echo site_url('schedule/delete/' . $schedule['id']); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a><?php } ?>
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