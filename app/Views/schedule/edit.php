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
                        <h3 class="box-title">Edit schedule</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php $flashError = session()->getFlashdata('error_message'); ?>
                <?php if ($flashError): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars(is_array($flashError) ? implode(', ', array_values($flashError)) : $flashError) ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo site_url('schedule/update/' . $schedule['id']); ?>" method="post">
                    <div class="form-group">
                        <label for="location">Schedule Type</label>

                        <select name="shedule_type" id="shedule_type" class="form-control" required>
                            <option value="Personal" <?php echo ($schedule['shedule_type'] == 'Personal') ? 'selected' : ''; ?>>
                                Personal
                            </option>
                            <option value="Official" <?php echo ($schedule['shedule_type'] == 'Official') ? 'selected' : ''; ?>>
                                Official
                            </option>
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="name">Schedule Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $schedule['title']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Schedule Description</label>
                        <input type="text" name="description" class="form-control" value="<?php echo $schedule['description']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo $schedule['date']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="timeslot">Timeslot</label>
                        <select name="timeslot_id[]" id="timeslot_id" class="form-control" multiple required>
                            <option value="">Select a timeslot</option>

                        </select>
                    </div>
                    <!-- <div class="form-group">
                        <label for="time_slot">Select Time Slots</label>
                        <select name="timeslot_id[]" id="timeslot_id" class="form-control" multiple required>
                            <?php
                            $selected_ids = explode(', ', $schedule['time_slots_ids']);
                            foreach ($timeslots as $timeslot):
                                $is_selected = in_array($timeslot['id'], $selected_ids) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $timeslot['id']; ?>" <?php echo $is_selected; ?>>
                                    <?php echo $timeslot['value']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple options.</small>
                    </div> -->

                    <!-- Add other fields as needed -->

                    <button type="submit" class="btn btn-primary">Update Schedule</button>
                </form>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Base URL and token
        const cliBaseUrl = '<?php echo $view_data['cliBaseUrl']; ?>';
        const token = '<?php echo $view_data['token']; ?>';

        // Function to fetch and populate timeslots
        function fetchTimeslots() {
            const selectedDate = $('#date').val();


            if (selectedDate) {
                const apiUrl = `${cliBaseUrl}schedule/timeslots/${selectedDate}`;
                $.ajax({
                    url: '<?php echo site_url('schedule/updateschedule'); ?>',
                    type: 'POST',
                    data: {
                        date: selectedDate,
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Handle success response
                        console.log('API called successfully', response);
                        const preSelectedIds = <?php echo json_encode($schedule['time_slots_ids']); ?>;
                        // Assuming the response contains the available timeslots
                        if (response.api_response && response.api_response.timeslots) {
                            var timeslotOptions = '';
                            $.each(response.api_response.timeslots, function(index, timeslot) {
                                const isSelected = preSelectedIds.includes(timeslot.id.toString()) ? 'selected' : '';
                                const isDisabled = timeslot.is_booked && !preSelectedIds.includes(timeslot.id.toString()) ? 'disabled' : '';
                                //timeslotOptions += '<option value="' + timeslot.id + '" ${isDisabled} ${isSelected}>' + timeslot.value + '</option>';
                                const option = `
        <option value="${timeslot.id}" ${isDisabled} ${isSelected}>
            ${timeslot.value} ${timeslot.is_booked ? '(Booked by: ' + (timeslot.booked_by.length ? timeslot.booked_by.join(', ') : 'N/A') + ')' : ''}
        </option>`;
                                $('#timeslot_id').append(option);
                            });
                            //$('#timeslot_id').html(timeslotOptions); // Update the timeslot dropdown
                        } else {
                            console.log('No timeslots available');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        console.error('Error in API call', error);
                    }
                });
            } else {

                $('#timeslot_id').html('<option value="">Select date first</option>');
            }
        }


        $('#date, #room_id').change(fetchTimeslots);


        fetchTimeslots();
    });
</script>