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
                        <h3 class="box-title">Edit Conference</h3>
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
                <form action="<?php echo site_url('conference/update/' . $conference['id']); ?>" method="post">
                    <div class="form-group">
                        <label for="name">Conference Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $conference['title']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Conference Description</label>
                        <input type="text" name="description" class="form-control" value="<?php echo $conference['description']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo $conference['date']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Room</label>

                        <select name="room_id" id="room_id" class="form-control" required>
                            <option value="1" <?php echo ($conference['room_id'] == 1) ? 'selected' : ''; ?>>
                                1
                            </option>
                            <option value="2" <?php echo ($conference['room_id'] == 2) ? 'selected' : ''; ?>>
                                2
                            </option>
                        </select>

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
                            $selected_ids = explode(', ', $conference['time_slots_ids']);
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

                    <button type="submit" class="btn btn-primary">Update Conference</button>
                </form>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Base URL and token
        const cliBaseUrl = '<?php echo $view_data['cliBaseUrl']; ?>';
        const token = '<?php echo $view_data['token']; ?>';

        // Function to fetch and populate timeslots
        function fetchTimeslots() {
            const selectedDate = $('#date').val();
            const selectedRoom = $('#room_id').val();

            if (selectedDate && selectedRoom) {
                const apiUrl = `${cliBaseUrl}conference/timeslots/${selectedDate}/${selectedRoom}`;
                $.ajax({
                    url: '<?php echo site_url('conference/updateconference'); ?>',
                    type: 'POST',
                    data: {
                        date: selectedDate,
                        room_id: selectedRoom
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('API called successfully', response);
                        const preSelectedIds = <?php echo json_encode($conference['time_slots_ids']); ?>;
                        $('#timeslot_id').empty();
                        if (response.api_response && response.api_response.timeslots) {
                            $.each(response.api_response.timeslots, function(index, timeslot) {
                                const isSelected = preSelectedIds.includes(timeslot.id.toString()) ? 'selected' : '';
                                const isDisabled = timeslot.is_booked && !preSelectedIds.includes(timeslot.id.toString()) ? 'disabled' : '';
                                const option = `
        <option value="${timeslot.id}" ${isDisabled} ${isSelected}>
            ${timeslot.value} ${timeslot.is_booked ? '(Booked by: ' + (timeslot.booked_by.length ? timeslot.booked_by.join(', ') : 'N/A') + ')' : ''}
        </option>`;
                                $('#timeslot_id').append(option);
                            });
                        } else {
                            $('#timeslot_id').html('<option value="">No timeslots available</option>');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        console.error('Error in API call', error);
                    }
                });
            } else {

                $('#timeslot_id').html('<option value="">Select a room and date first</option>');
            }
        }


        $('#date, #room_id').change(fetchTimeslots);


        fetchTimeslots();
    });
</script>