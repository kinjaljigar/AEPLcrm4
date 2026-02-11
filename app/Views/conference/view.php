<?php $conferences = $view_data['conference'];
$conference = $conferences['data'];
$timeslots = $view_data['timeslots'];
?>

<h1>Conference</h1>
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
                        <h3 class="box-title">view Conference</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php if (session()->getFlashdata('error_message')): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars(session()->getFlashdata('error_message')) ?>
                    </div>
                    <?php session()->remove('error_message'); ?>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Conference Title</label><br />
                    <?php echo $conference['title']; ?>
                </div>
                <div class="form-group">
                    <label for="name">Conference Description</label><br />
                    <?php echo $conference['description']; ?>
                </div>
                <div class="form-group">
                    <label for="date">Date</label><br />
                    <td><?= date('d-m-Y', strtotime($conference['date'])) ?></td>
                </div>
                <div class="form-group">
                    <label for="location">Room</label><br />
                    <?php echo $conference['room_id']; ?>
                </div>
                <div class="form-group">
                    <label for="time_slot">Select Time Slots</label>
                    <select name="timeslot_id[]" id="timeslot_id" class="form-control" multiple required>
                        <?php
                        $selected_ids = explode(',', $conference['time_slots_ids']);
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
                <!-- <a href="<?= base_url('conference'); ?>" class="btn btn-primary">Back</a> -->
                <form id="redirectForm" action="<?= base_url('conference'); ?>" method="post">
                    <input type="hidden" name="data" value="<?= isset($_GET['data']) ? $_GET['data'] : ''; ?>">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>

            </div>
            <div class="box-footer"></div>


        </div>

    </section>
    <pre>
<?php echo 'Pre-selected IDs: ' . (isset($conference['time_slots_ids']) ? json_encode($conference['time_slots_ids']) : 'Not Set'); ?>
</pre>
</div><!-- /.content-wrapper -->