<style>
    tr.bg-warning {
        background-color: #fff3cd !important;
    }

    .content-column {
        max-width: 250px;
    }

    .short-text {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
    }

    .short-text.open {
        white-space: normal;
        overflow: visible;
    }
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Dashboard</h2>
    </section>

    <?php if ($this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['TaskCoordinator'])) { ?>
        <!-- COORDINATOR VIEW: CRM DATA + PROJECT DATA IN SAME SECTION -->
        <section class="content">
            <h2>CRM Data</h2>
            <div class="row">
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Total Projects</div>
                        <div class="card-body"><b id="total_projects">0</b></div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Active Projects</div>
                        <div class="card-body"><b id="active_projects">0</b></div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Completed Projects</div>
                        <div class="card-body"><b id="completed_projects">0</b></div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Total Employees</div>
                        <div class="card-body"><b id="total_employee">0</b></div>
                    </div>
                </div>
            </div>

            <!-- PROJECT DATA (DEPENDENCIES ONLY) - SAME SECTION -->
            <h2>Project Data</h2>
            <div class="box box-sbpink">
                <div class="box-header">
                    <div class="row">
                        <div class="col-sm-9">
                            <h3 class="box-title">Latest Dependencies</h3>
                        </div>
                        <div class="col-sm-3">
                            <a href="<?php echo base_url("home/dependencies"); ?>" class="btn btn-primary pull-right">More</a>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-hover responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Created Date</th>
                                <th>Project</th>
                                <th>Dependency</th>
                                <th>Created By</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Targated Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($view_data['dependencies'])): ?>
                                <?php foreach ($view_data['dependencies'] as $i => $dep): ?>
                                    <?php
                                    $logged_id = $view_data['admin_session']['u_id'];
                                    $dep_leader_ids = explode(',', $dep['dep_leader_ids'] ?? '');
                                    $isAssignedToMe = in_array($logged_id, $dep_leader_ids);
                                    $isCreatedByMe = ($dep['created_by_id'] == $logged_id);
                                    $highlightClass = ($isAssignedToMe && !$isCreatedByMe) ? 'bg-warning text-dark' : '';
                                    ?>
                                    <tr class="<?= $highlightClass; ?>">
                                        <td><?= $i + 1; ?></td>
                                        <td>
                                            <?= !empty($dep['created_date']) && $dep['created_date'] !== '0000-00-00 00:00:00'
                                                ? date('d-m-Y', strtotime($dep['created_date']))
                                                : '' ?>
                                        </td>
                                        <td><?= htmlspecialchars($dep['project_name']); ?></td>
                                        <td class="content-column" title="<?= htmlspecialchars($dep['dependency_text']); ?>">
                                            <span class="short-text">
                                                <?= htmlspecialchars($dep['dependency_text']); ?>
                                            </span>
                                        </td>

                                        <td><?= htmlspecialchars($dep['created_by']); ?></td>
                                        <td><?= htmlspecialchars($dep['assigned_to']); ?></td>
                                        <!-- <td><?= htmlspecialchars($dep['dependency_type']); ?></td>
                                            <td><?= htmlspecialchars($dep['priority']); ?></td> -->
                                        <td>
                                            <span class="label <?= $dep['status'] == 'Completed' ? 'label-success' : ($dep['status'] == 'In Progress' ? 'label-warning' : 'label-danger'); ?>">
                                                <?= htmlspecialchars($dep['status']); ?>
                                            </span>
                                        </td>


                                        <td>
                                            <?= !empty($dep['target_date']) && $dep['target_date'] !== '0000-00-00'
                                                ? date('d-m-Y', strtotime($dep['target_date']))
                                                : '' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">No dependencies found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php } ?>

    <?php if ($this->authorization->is_bim_head_or_higher($view_data['admin_session'])) { ?>
        <section class="content" style="min-height: 100px!important;">
            <h2>Project Data</h2>
            <div class="box box-sbpink">
                <div class="box-header">
                    <div class="row">
                        <div class="col-sm-9">
                            <h3 class="box-title">Latest Dependencies</h3>
                        </div>
                        <div class="col-sm-3">
                            <a href="<?php echo base_url("home/dependencies"); ?>" class="btn btn-primary pull-right">More</a>
                        </div>
                    </div>
                </div>

                <div class="box-body">
                    <table class="table table-bordered table-hover responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Created Date</th>
                                <th>Project</th>
                                <th>Dependency</th>
                                <th>Created By</th>
                                <th>Assigned To</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Targated Date</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($view_data['dependencies'])): ?>
                                <?php foreach ($view_data['dependencies'] as $i => $dep): ?>
                                    <?php
                                    $logged_id = $view_data['admin_session']['u_id'];
                                    $dep_leader_ids = explode(',', $dep['dep_leader_ids'] ?? '');
                                    $isAssignedToMe = in_array($logged_id, $dep_leader_ids);
                                    $isCreatedByMe = ($dep['created_by_id'] == $logged_id);
                                    $highlightClass = ($isAssignedToMe && !$isCreatedByMe) ? 'bg-warning text-dark' : '';
                                    ?>
                                    <tr class="<?= $highlightClass; ?>">
                                        <td><?= $i + 1; ?></td>
                                        <td>
                                            <?= !empty($dep['created_date']) && $dep['created_date'] !== '0000-00-00 00:00:00'
                                                ? date('d-m-Y', strtotime($dep['created_date']))
                                                : '' ?>
                                        </td>

                                        <td><?= htmlspecialchars($dep['project_name']); ?></td>
                                        <td class="content-column" title="<?= htmlspecialchars($dep['dependency_text']); ?>">
                                            <span class="short-text">
                                                <?= htmlspecialchars($dep['dependency_text']); ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($dep['created_by']); ?></td>
                                        <td><?= htmlspecialchars($dep['assigned_to']); ?></td>
                                        <td><?= htmlspecialchars($dep['dependency_type']); ?></td>
                                        <td><?= htmlspecialchars($dep['priority']); ?></td>
                                        <td>
                                            <span class="label <?= $dep['status'] == 'Completed' ? 'label-success' : ($dep['status'] == 'In Progress' ? 'label-warning' : 'label-danger'); ?>">
                                                <?= htmlspecialchars($dep['status']); ?>
                                            </span>
                                        </td>


                                        <td>
                                            <?= !empty($dep['target_date']) && $dep['target_date'] !== '0000-00-00'
                                                ? date('d-m-Y', strtotime($dep['target_date']))
                                                : '' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">No dependencies found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($this->authorization->is_bim_head_or_higher($view_data['admin_session'])) { ?>
                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="col-sm-12">
                            <a href="<?php echo base_url("home/projectData"); ?>" class="btn btn-primary pull-right">More</a>
                        </div><br /><br />
                        <div class="row">
                            <div class="col-sm-6">
                                <h3 class="box-title">Weekly Work (All Leaders)</h3>
                            </div>
                            <div class="col-sm-6 text-right">
                                <form method="get" class="form-inline" style="display:inline-block;">
                                    <select name="leader_id" class="form-control" style="width:200px;display:inline-block;">
                                        <option value="">-- Select Leader --</option>
                                        <?php foreach ($view_data['leaders'] as $leader): ?>
                                            <option value="<?= $leader['u_id']; ?>" <?=
                                                                                    (isset($_GET['leader_id']) && $_GET['leader_id'] == $leader['u_id']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($leader['u_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="<?= base_url('home'); ?>" class="btn btn-default">Reset</a>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>

                                    <th>Leader</th>
                                    <th>Team Assigned</th>
                                    <th>No. Of Person</th>
                                    <th>Assigned Users</th>
                                    <th>No Of Projects</th>
                                    <th>Project</th>
                                    <th>Week</th>
                                    <th>Work Summary</th>
                                    <th>Submission Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($view_data['weekly_works'])): ?>
                                    <?php foreach ($view_data['weekly_works'] as $work): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($work['leader_name']) ?></td>
                                            <td><?= (int)$work['team_assigned'] ?></td>
                                            <td><?= htmlspecialchars($work['no_of_persons']) ?></td>
                                            <td><?= htmlspecialchars($work['assigned_users'] ?? '-') ?></td>
                                            <td><?= (int)$work['no_of_projects'] ?></td>
                                            <td><?= htmlspecialchars($work['project_name']) ?></td>
                                            <td><?= htmlspecialchars($work['week_from']) . " To " . htmlspecialchars($work['week_to']) ?></td>
                                            <td><?= htmlspecialchars($work['task_name']) ?></td>
                                            <td>
                                                <?= !empty($work['submission_date']) && $work['submission_date'] !== '0000-00-00'
                                                    ? date('d-m-Y', strtotime($work['submission_date']))
                                                    : '' ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($work['status']) ?>
                                                <br>
                                                <?php if ($work['incomplete_deps'] > 0): ?>
                                                    <a href="javascript:void(0);" class="btn btn-warning btn-xs view-dep-btn"
                                                        data-wid="<?= $work['w_id']; ?>" data-type="incomplete">
                                                        View Incomplete (<?= $work['incomplete_deps']; ?>)
                                                    </a><br>
                                                <?php endif; ?>

                                                <a href="javascript:void(0);" class="btn btn-info btn-xs view-dep-btn"
                                                    data-wid="<?= $work['w_id']; ?>" data-type="all">
                                                    View All
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9">No data found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>

        </section>
    <?php } ?>

    <?php if ($this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['Master Admin'])) {
    ?> <section class="content" style="min-height: 100px!important;">
            <h2>Application Data</h1>
                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="box-title">Upcoming Conferences</h3>
                            </div>
                            <div class="col-sm-3"><a href="<?php echo base_url("conference"); ?>"
                                    class="btn btn-primary pull-right">More</a></div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="ConferenceTable" class="table table-bordered table-hover responsive nowrap" width="100% ">
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
                                <?php
                                $conferences = $view_data['conferences'];
                                if (!empty($conferences['data'])) {
                                    if ($conferences['status'] == 'error') {
                                        print $conferences['message'];
                                    } else { ?>
                                        <ul>
                                            <?php foreach ($conferences['data'] as $conference): ?>
                                                <tr>
                                                    <td><?= $conference['title'] ?></td>
                                                    <td><?= $conference['date'] ?></td>
                                                    <td><?= $conference['timeslot_values'] ?></td>
                                                    <td><?= $conference['u_name'] ?></td>
                                                    <td><?= $conference['room_id'] ?></td>
                                                    <td>
                                                        <?php if ($view_data['dataURL'] != 'archived' && $conference['edit'] == 1) { ?>
                                                            <a href="<?php echo site_url('conference/edit/' . $conference['id']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>
                                                                <?php
                                                            } else {
                                                                ?><a href="<?php echo site_url('conference/view/' . $conference['id']) . "?data=upcoming" ?>" class="btn btn-success btn-md"><i class="fa fa-eye"></i><a><?php
                                                                                                                                                                                                                        }
                                                                                                                                                                                                                        if ($conference['delete'] == 1) { ?>
                                                                            <a class="btn btn-danger btn-md" href="<?php echo site_url('conference/delete/' . $conference['id'] . "?data=upcoming"); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a><?php } ?>
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


                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="box-title">Upcoming Schedules</h3>
                            </div>
                            <div class="col-sm-3"><a href="<?php echo base_url("schedule"); ?>"
                                    class="btn btn-primary pull-right">More</a></div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="ScheduleTable" class="table table-bordered table-hover responsive nowrap" width="100% ">
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
                                <?php
                                $schedules = $view_data['schedules'];
                                if (!empty($schedules['data'])) {
                                    if ($schedules['status'] == 'error') {
                                        print $schedules['message'];
                                    } else { ?>
                                        <ul>
                                            <?php foreach ($schedules['data'] as $schedule): ?>
                                                <tr>
                                                    <td><?= $schedule['title'] ?></td>
                                                    <td><?= $schedule['date'] ?></td>
                                                    <td><?= $schedule['timeslot_values'] ?></td>
                                                    <td><?= $schedule['u_name'] ?></td>
                                                    <td>
                                                        <?php if ($view_data['dataURL'] != 'archived' && $schedule['edit'] == 1) { ?>
                                                            <a href="<?php echo site_url('schedule/edit/' . $schedule['id']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>
                                                                <?php
                                                            } else {
                                                                ?><a href="<?php echo site_url('schedule/view/' . $schedule['id']) . "?data=upcoming&type=mydata" ?>" class="btn btn-success btn-md"><i class="fa fa-eye"></i><a><?php
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                if ($schedule['delete'] == 1) { ?>
                                                                            <a class="btn btn-danger btn-md" href="<?php echo site_url('schedule/delete/' . $schedule['id'] . "?data=upcoming&type=mydata"); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a><?php } ?>
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

                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="box-title">Upcoming Task Reminders</h3>
                            </div>
                            <div class="col-sm-3"><a href="<?php echo base_url("usertask"); ?>"
                                    class="btn btn-primary pull-right">More</a></div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="UserTaskTable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>date</th>
                                    <th>time</th>
                                    <th>Created By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">

                                <?php
                                $tasks = $view_data['tasks'];
                                if ($tasks['status'] == 'error') {
                                    print $tasks['message'];
                                } else { ?>
                                    <ul>
                                        <?php foreach ($tasks['data'] as $task): ?>
                                            <tr>
                                                <td><?= $task['title'] ?></td>
                                                <td><?= $task['date'] ?></td>
                                                <td><?= $task['time'] ?></td>
                                                <td><?= $task['creator_name'] ?></td>
                                                <td>
                                                    <?php if ($view_data['dataURL'] != 'archived' && $task['edit'] == 1) { ?>
                                                        <a href="<?php echo site_url('usertask/edit/' . $task['id']); ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i><a>
                                                            <?php
                                                        } else {
                                                            ?><a href="<?php echo site_url('usertask/view/' . $task['id'] . "?data=" . $view_data['dataURL']); ?>" class="btn btn-success btn-md"><i class="fa fa-eye"></i><a><?php
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                            if ($task['delete'] == 1) { ?>
                                                                        <a class="btn btn-danger btn-md" href="<?php echo site_url('usertask/delete/' . $task['id']); ?>" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i><a><?php } ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </ul> <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        </section>
    <?php
    }
    ?>

    <?php if (!$this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['TaskCoordinator'])) { ?>
        <section class="content">
            <h2>CRM Data</h2>



            <div class="row">
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Total Projects</div>
                        <div class="card-body"><b id="total_projects">0</b></div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Active Projects</div>
                        <div class="card-body"><b id="active_projects">0</b></div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Completed Projects</div>
                        <div class="card-body"><b id="completed_projects">0</b></div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-header">Total Employees</div>
                        <div class="card-body"><b id="total_employee">0</b></div>
                    </div>
                </div>
            </div>
            <?php if ($this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['Project Leader'])) { ?>


                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="box-title">Todays Mail Links</h3>
                            </div>
                            <div class="col-sm-3">
                                <a href="<?php echo base_url("home/messages"); ?>" class="btn btn-primary pull-right">More</a>
                            </div>
                        </div>
                    </div>

                    <div class="box-body">
                        <table class="table table-bordered table-hover responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Project</th>
                                    <th>Message</th>
                                    <th>Discipline</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (!empty($view_data['todaysmessages'])): ?>
                                    <?php foreach ($view_data['todaysmessages'] as $i => $dep): ?>
                                        <tr>
                                            <td><?= $i + 1; ?></td>
                                            <td>
                                                <?= date("M d, Y", strtotime($dep['pm_datetime'])); ?>
                                            </td>
                                            <td><?= (!empty($dep['p_name']) ? $dep['p_name'] : 'General'); ?></td>
                                            <td class="content-column" title="<?= htmlspecialchars($dep['dependency_text']); ?>">
                                                <span class="short-text">
                                                    <?= (!empty($dep['pm_text']) ? htmlspecialchars(mb_strimwidth($dep['pm_text'], 0, 200, '...')) : ''); ?>
                                                </span>
                                            </td>

                                            <td><?= !empty($dep['pm_descipline']) ? $dep['pm_descipline'] : ''; ?></td>


                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9">No Mail Links For Today found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="box-title">Latest Dependencies</h3>
                            </div>
                            <div class="col-sm-3">
                                <a href="<?php echo base_url("home/dependencies"); ?>" class="btn btn-primary pull-right">More</a>
                            </div>
                        </div>
                    </div>

                    <div class="box-body">
                        <table class="table table-bordered table-hover responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Created Date</th>
                                    <th>Project</th>
                                    <th>Dependency</th>
                                    <th>Created By</th>
                                    <th>Assigned To</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Targated Date</th>

                                </tr>
                            </thead>
                            <tbody>

                                <?php if (!empty($view_data['dependencies'])): ?>
                                    <?php foreach ($view_data['dependencies'] as $i => $dep): ?>
                                        <?php
                                        $logged_id = $view_data['admin_session']['u_id'];
                                        $dep_leader_ids = explode(',', $dep['dep_leader_ids'] ?? '');
                                        $isAssignedToMe = in_array($logged_id, $dep_leader_ids);
                                        $isCreatedByMe = ($dep['created_by_id'] == $logged_id);
                                        $highlightClass = ($isAssignedToMe && !$isCreatedByMe) ? 'bg-warning text-dark' : '';
                                        ?>
                                        <tr class="<?= $highlightClass; ?>">
                                            <td><?= $i + 1; ?></td>
                                            <td>
                                                <?= !empty($dep['created_date']) && $dep['created_date'] !== '0000-00-00 00:00:00'
                                                    ? date('d-m-Y', strtotime($dep['created_date']))
                                                    : '' ?>
                                            </td>
                                            <td><?= htmlspecialchars($dep['project_name']); ?></td>
                                            <td class="content-column" title="<?= htmlspecialchars($dep['dependency_text']); ?>">
                                                <span class="short-text">
                                                    <?= htmlspecialchars($dep['dependency_text']); ?>
                                                </span>
                                            </td>

                                            <td><?= htmlspecialchars($dep['created_by']); ?></td>
                                            <td><?= htmlspecialchars($dep['assigned_to']); ?></td>
                                            <td><?= htmlspecialchars($dep['dependency_type']); ?></td>
                                            <td><?= htmlspecialchars($dep['priority']); ?></td>
                                            <td>
                                                <span class="label <?= $dep['status'] == 'Completed' ? 'label-success' : ($dep['status'] == 'In Progress' ? 'label-warning' : 'label-danger'); ?>">
                                                    <?= htmlspecialchars($dep['status']); ?>
                                                </span>
                                            </td>


                                            <td>
                                                <?= !empty($dep['target_date']) && $dep['target_date'] !== '0000-00-00'
                                                    ? date('d-m-Y', strtotime($dep['target_date']))
                                                    : '' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9">No dependencies found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>

            <?php if (!$this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['TaskCoordinator'])) { ?>
                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="box-title">Today Employees on leave</h3>
                            </div>
                            <!-- <div class="col-sm-3"><a href="<?php echo base_url("home/leaves"); ?>"
                            class="btn btn-primary pull-right">Take Action</a></div> -->
                        </div>
                    </div>

                    <div class="box-body">
                        <table id="dataTableLeavestoday" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Is Half day ?</th>
                                    <th>Hourly Leave ?</th>
                                    <th>Leave Approve By</th>
                                    <!-- <th>Leave Start Date</th>
                            <th>Leave End Date</th>
                            <th># of Days</th> -->
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box-sbpink">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="box-title">Leave Request</h3>
                            </div>
                            <div class="col-sm-3"><a href="<?php echo base_url("home/leaves"); ?>" class="btn btn-primary pull-right">Take Action</a></div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="dataTableLeaves" class="table table-bordered table-hover responsive nowrap" width="100% ">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Application Date</th>
                                    <th>Leave Start Date</th>
                                    <th>Leave End Date</th>
                                    <th>Hourly / Half Day Leave</th>
                                    <th># of Days</th>
                                    <th width="120">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <?php if ($this->authorization->is_bim_head_or_higher($view_data['admin_session'])) { ?>
                <div class="box box-sbpink">
                    <div class="box-header">

                        <div class="col-sm-9">
                            <h3 class="box-title">Employee Present Departmentwise</h3>
                        </div>
                        <div class="col-sm-3">
                            <a href="<?php echo base_url("home/presentlist"); ?>" class="btn btn-primary pull-right">More</a>
                        </div>
                    </div>
                    <div class="box-body">

                        <table id="dataTableDepartments" class="table table-bordered responsive nowrap" width="100%" style="display:none">
                        </table>

                        <table id="dataTablePresent" class="table table-bordered responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>EMail</th>
                                    <th>Mobile</th>
                                    <th>Type</th>
                                    <th>Department</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <?php if ($this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['Master Admin'])) { ?>
                <div class="box box-sbpink">
                    <div class="box-header">
                        <h3 class="box-title">Projects under watch</h3>
                    </div>
                    <div class="box-body">
                        <table id="dataTableWatch" class="table table-bordered responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Start Date</th>
                                    <th>Total Cost</th>
                                    <th>Expenses</th>
                                    <th>Profit/Loss</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="admin_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>

            <div class="leave_form" style="display: none;">
                <div class="box-body">
                    <input type="hidden" name="l_id" id="l_id" value="">
                    <h4 id="u_name"><b>Name</b></h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <b>Mobile:</b> <span id="u_mobile">Mobile</span><br />
                            <b>Email:</b> <span id="u_email">Email</span><br />
                        </div>
                        <div class="col-sm-3">
                            <b>Active Projects:</b> <span id="u_active">Mobile</span><br />
                            <b>Onhand Tasks:</b> <span id="u_tasks">Email</span><br />
                        </div>
                        <div class="col-sm-3">
                            <img src="" class="img_logo" id="img_url" />
                        </div>
                    </div>
                    <div>
                        <h4><b>Leave Request:</b></h4>
                        <span id="l_message"></span>
                    </div>
                    <hr />
                    <div class="form-group">
                        <label for="l_reply">Reply</label>
                        <textarea type="text" class="form-control" id="l_reply" name="l_reply"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="check_container inline">Apporve
                            <input type="radio" id="l_status_a" name="l_status" value="Approve">
                            <span class="checkmark"></span>
                        </label>
                        <label class="check_container inline">Decline
                            <input type="radio" id="l_status_d" name="l_status" value="Decline">
                            <span class="checkmark"></span>
                        </label> <br />
                        <label id="l_status-error" class="has-error" for="l_status" style="display:none;">This field is
                            required.</label>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="button" class="btn btn-danger margin pull-right" data-dismiss="modal">Cancel</button>
                    <button type="button" id="main_add_button" onclick="saveLeave();" class="btn btn-primary margin pull-right">Save</button>
                </div>
            </div>

        </section>
    <?php } ?>
</div>
<script>
    var STYPE = '';
    var dataTableLeaves = null;
    var dataTableLeavestoday = null;
    var dataTableWatch = null;
    var dataTablePresent = null;


    function formatDateDMY(dateStr) {
        if (!dateStr || dateStr === '0000-00-00' || dateStr === '0000-00-00 00:00:00') {
            return '-';
        }

        const date = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(date)) return '-';

        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();

        return `${d}-${m}-${y}`;
    }

    function document_ready() {
        leaderid = null;
        <?php if ($this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['Project Leader'])) { ?>
            leaderid = '<?php print $view_data['admin_session']['u_id'] ?>';
        <?php } ?>
        doAjax('api/dashboard', 'POST', {
            type: "basic"
        }, function(res) {
            if (res.status == 'pass') {
                var box = res.data.box;
                $.each(box, function(key, value) {
                    $('#' + key).html(value);
                });
                $("#dataTableDepartments").html(res.data.rows);
            } else {
                showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
            }
        });
        LoadPresent();
        LoadLeaves(leaderid);
        LoadLeavesToday(leaderid);
        <?php if ($this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['Master Admin'])) { ?>LoadWatch();
    <?php } ?>

    $(document).on('click', '.short-text', function() {
        $(this).toggleClass('open');
    });
    $(document).on('click', '.view-dep-btn', function() {
        const w_id = $(this).data('wid');
        const type = $(this).data('type');
        const modalTitle = type === 'incomplete' ? 'Incomplete Dependencies' : 'All Dependencies';

        $.ajax({
            url: '<?= base_url("api/weeklywork"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                act: "dependencies",
                w_id: w_id,
                type: type
            },
            success: function(res) {
                if (res.status === 'pass') {
                    if (!res.data || res.data.length === 0) {
                        showModal('ok', 'No dependencies found.', 'Info', 'modal-success', 'modal-sm');
                        return;
                    }

                    let html = `
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                    <th>#</th>
                                    <th>Created Date</th>
                                    <th>Project</th>
                                    <th>Dependency</th>
                                    <th>Created By</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Targated Date</th>
                                </tr>
                        </thead>
                        <tbody>
                `;
                    console.log(d);
                    res.data.forEach((d, i) => {
                        html += `
                    <tr>
                        <td>${i + 1}</td><td>${formatDateDMY(d.created_date)}</td>
                        <td>${d.dependency_text || '-'}</td>
                        <td>${d.created_by || '-'}</td>
                        
                        <td>${d.assigned_to || '-'}</td>
                        
                        <td>
                            <span class="label ${d.status === 'Completed' ? 'label-success' : (d.status === 'In Progress' ? 'label-warning' : 'label-danger')}">
                                ${d.status || '-'}
                            </span>
                        </td>
                        <td>${formatDateDMY(d.target_date)}</td>
   
                    </tr>
                `;
                    });

                    html += '</tbody></table></div>';

                    const modalClass = type === 'incomplete' ? '' : '';
                    showModal('html', html, modalTitle, 'modal', 'modal-lg ' + modalClass);
                } else {
                    showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                }
            },
            error: function() {
                showModal('ok', 'Error fetching dependencies.', 'Error', 'modal-danger', 'modal-sm');
            }
        });
    });
    }
    <?php if ($this->authorization->is_role_allowed($view_data['admin_session']['u_type'], ['Master Admin'])) { ?>

        function LoadWatch() {
            var dtConf = {
                ajax: {
                    url: "<?php echo base_url('api/dashboard'); ?>",
                    method: "post",
                    data: {
                        "type": "under_watch",
                    }
                },
                bPaginate: false,
                bInfo: false,
                pageLength: -1,
                stripeClasses: ['r0', 'r1'],
                bSort: false,
                columnDefs: [{
                    "targets": [0, 1],
                    "searchable": false,
                    'bSortable': false,
                    "orderable": false,
                }],
                oLanguage: {
                    "sEmptyTable": "There is not any <b>Projects under watch</b>.",
                },
            };
            if (dataTableWatch != null) {
                dataTableWatch.destroy();
            }
            dataTableWatch = loadDataTable('#dataTableWatch', dtConf);
        }
    <?php } ?>

    function LoadLeaves(leaderid) {
        var dtConf = {
            ajax: {
                url: "<?php echo base_url('api/dashboard'); ?>",
                method: "post",
                data: {
                    "type": "leaves",
                    leaderid: leaderid,
                }
            },
            bPaginate: false,
            bInfo: false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            columnDefs: [{
                "targets": [0, 1],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }],
            oLanguage: {
                "sEmptyTable": "There is not any <b>Leaves Requests</b>.",
            },
        };
        if (dataTableLeaves != null) {
            dataTableLeaves.destroy();
        }
        dataTableLeaves = loadDataTable('#dataTableLeaves', dtConf);
    }

    function LoadLeavesToday(leaderid) {
        var dtConf = {
            ajax: {
                url: "<?php echo base_url('api/dashboard'); ?>",
                method: "post",
                data: {
                    "type": "leavestoday",
                    leaderid: leaderid,
                }
            },
            bPaginate: false,
            bInfo: false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            columnDefs: [{
                "targets": [0, 1],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }],
            oLanguage: {
                "sEmptyTable": "There is not any <b>Leaves Requests</b>.",
            },
        };
        if (dataTableLeavestoday != null) {
            dataTableLeavestoday.destroy();
        }
        dataTableLeavestoday = loadDataTable('#dataTableLeavestoday', dtConf);
    }

    function LoadPresent() {
        var dtConf = {
            ajax: {
                url: "<?php echo base_url('api/dashboard'); ?>",
                method: "post",
                data: {
                    "type": "present_list_limit",
                }
            },
            bPaginate: false,
            bInfo: false,
            pageLength: -1,
            stripeClasses: ['r0', 'r1'],
            bSort: false,
            columnDefs: [{
                "targets": [0, 1],
                "searchable": false,
                'bSortable': false,
                "orderable": false,
            }],
            oLanguage: {
                "sEmptyTable": "There is not any <b>Absent Today</b>.",
            },
        };
        if (dataTablePresent != null) {
            dataTablePresent.destroy();
        }
        dataTablePresent = loadDataTable('#dataTablePresent', dtConf);
    }

    function Approve(id, act) {
        var id = id == 'undefined' ? 0 : id;
        var html = '<form class="formclass" id="leave_form" name="leave_form" enctype="multipart/form-data">';
        html += $('.leave_form').html();
        html += '</form>';
        if (parseInt(id) > 0) {
            doAjax('api/leaves', 'POST', {
                l_id: id,
                act: "loadinfo"
            }, function(res) {
                if (res.status == 'pass') {
                    var record = res.data;
                    showModal('html', html, 'Manage Leave', 'modal', 'modal-md', function() {
                        $('#leave_form').find('#l_id').val(record.l_id);
                        $.each(record, function(key, value) {
                            $('#leave_form').find('#' + key).html(value);
                        });
                        $('#leave_form').find('#img_url').attr("src", res.img_url);
                    });
                } else {
                    showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                }
            });
        }
    }

    function saveLeave() {
        var rules = {
            l_reply: {
                required: true
            },
            l_status: {
                required: true
            },

        };
        var form = setValidation('#leave_form', rules);
        var isValid = form.valid();
        if (isValid == true) {
            var formData = form.serializeArray();
            formData.push({
                name: "act",
                value: "Approve"
            });
            doAjax('api/leaves', 'POST', formData, function(res) {
                if (res.status == 'pass') {
                    showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function() {},
                        function() {
                            dataTable.ajax.reload();
                        });
                } else {
                    if (res.type != 'undefined' && res.type == 'popup') {
                        showMessage(res.message, 'leave_form', 'error_message', 'danger', true);
                        $('#sbModel').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else {
                        showModal('ok', res.message, 'Error!', 'modal-danger', 'modal-sm');
                    }
                }
            });
        }
    }
</script>