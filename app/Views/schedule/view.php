<?php $schedules = $view_data['schedule'];
$schedule = $schedules['data'];
$timeslots = $view_data['timeslots'];
?>

<h1>schedule</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>schedules</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">view schedule</h3>
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
                <div class="form-group">
                    <label for="location">Schedule Type</label><br />
                    <?php echo $schedule['shedule_type']; ?>
                </div>
                <div class="form-group">
                    <label for="name">Schedule Title</label><br />
                    <?php echo $schedule['title']; ?>
                </div>
                <div class="form-group">
                    <label for="name">Schedule Description</label><br />
                    <?php echo $schedule['description']; ?>
                </div>
                <div class="form-group">
                    <label for="date">Date</label><br />
                    <td><?= date('d-m-Y', strtotime($schedule['date'])) ?></td>
                </div>
                <div class="form-group">
                    <label for="time_slot">Select Time Slots</label>
                    <select name="timeslot_id[]" id="timeslot_id" class="form-control" multiple required>
                        <?php
                        $selected_ids = explode(',', $schedule['time_slots_ids']);
                        foreach ($timeslots as $timeslot):
                            $is_selected = in_array($timeslot['id'], $selected_ids) ? 'selected' : '';
                            if (in_array($timeslot['id'], $selected_ids)) {
                        ?>
                                <option value="<?php echo $timeslot['id']; ?>" <?php echo $is_selected; ?>>
                                    <?php echo $timeslot['value']; ?>
                                </option>
                        <?php  }
                        endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple options.</small>
                </div>

                <!-- Add other fields as needed -->
                <!-- <a href="<?= base_url('schedule'); ?>" class="btn btn-primary">Back</a> -->
                <form id="redirectForm" action="<?= base_url('schedule'); ?>" method="post">
                    <input type="hidden" name="data" value="<?= isset($_GET['data']) ? $_GET['data'] : ''; ?>">
                    <input type="hidden" name="type" value="<?= isset($_GET['type']) ? $_GET['type'] : ''; ?>">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>

            </div>
            <div class="box-footer"></div>


        </div>

    </section>
    <pre>
<?php echo 'Pre-selected IDs: ' . (isset($schedule['time_slots_ids']) ? json_encode($schedule['time_slots_ids']) : 'Not Set'); ?>
</pre>
</div><!-- /.content-wrapper -->