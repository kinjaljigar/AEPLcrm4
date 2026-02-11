<?php
$timeslots = $view_data['timeslots'];
$token = $view_data['token'];
?>

<h1>Add Conference</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Conference</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Add Conference</h3>
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
                <form action="<?php echo site_url('conference/addData/'); ?>" method="post">
                    <div class="form-group">
                        <label for="name">Conference Title</label>
                        <input type="text" name="title" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Conference Description</label>
                        <input type="text" name="description" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Room</label>
                        <select name="room_id" id="room_id" class="form-control" required>
                            <option value="1">
                                1
                            </option>
                            <option value="2">
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
                            <?php foreach ($timeslots as $timeslot): ?>
                                <option value="<?php echo $timeslot['id']; ?>">
                                    <?php echo $timeslot['value']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple options.</small>
                    </div> -->
                    <!-- Add other fields as needed -->

                    <button type="submit" class="btn btn-primary">Add Conference</button>
                </form>
                <form id="redirectForm" action="<?= base_url('conference'); ?>" method="post">
                    <input type="hidden" name="data" value="<?= isset($_GET['data']) ? $_GET['data'] : ''; ?>">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date').setAttribute('min', today);

        // Base URL and token
        const cliBaseUrl = '<?php echo $view_data['cliBaseUrl']; ?>';
        const token = '<?php echo $view_data['token']; ?>';

        // Function to fetch and populate timeslots
        function fetchTimeslots() {
            const selectedDate = $('#date').val();
            const selectedRoom = $('#room_id').val();

            if (selectedDate && selectedRoom) {
                const apiUrl = `${cliBaseUrl}conference/timeslots/${selectedDate}/${selectedRoom}`;

                //         $.ajax({
                //             url: apiUrl,
                //             method: "GET",
                //             headers: {
                //                 "Authorization": "Bearer " + token,
                //             },
                //             beforeSend: function() {
                //                 // Optional: Show a loader while fetching
                //                 $('#timeslot_id').html('<option>Loading timeslots...</option>');
                //             },
                //             success: function(response) {
                //                 // Clear existing options
                //                 $('#timeslot_id').empty();

                //                 console.log('Fetching available timeslots for Add Conference...');

                //                 // Check if there are available slots
                //                 if (response.availableslots && Array.isArray(response.availableslots) && response.availableslots.length > 0) {
                //                     $('#timeslot_id').append('<option value="" disabled>Select a timeslot</option>');

                //                     response.availableslots.forEach(function(slot) {
                //                         const isDisabled = slot.is_booked ? 'disabled' : '';
                //                         const option = `
                // <option value="${slot.id}" ${isDisabled}>
                //     ${slot.value} ${slot.is_booked ? '(Booked by: ' + (slot.booked_by.length ? slot.booked_by.join(', ') : 'N/A') + ')' : ''}
                // </option>`;
                //                         $('#timeslot_id').append(option);
                //                     });
                //                 } else {
                //                     $('#timeslot_id').append('<option value="" disabled>No available timeslots</option>');
                //                 }
                //             },
                //             error: function(xhr) {
                //                 alert('Failed to fetch timeslots. Please try again.');
                //                 console.error(xhr.responseText);

                //             }
                //         });
                $.ajax({
                    url: '<?php echo site_url('ConferenceController/updateConference'); ?>', // Use CodeIgniter's site_url helper to generate the correct URL
                    type: 'POST',
                    data: {
                        date: selectedDate,
                        room_id: selectedRoom
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Handle success response
                        console.log('API called successfully', response);

                        // Assuming the response contains the available timeslots
                        if (response.api_response && response.api_response.timeslots) {
                            var timeslotOptions = '';
                            // $.each(response.api_response.timeslots, function(index, timeslot) {
                            //     timeslotOptions += '<option value="' + timeslot.id + '">' + timeslot.value + '</option>';
                            // });
                            // $('#timeslot_id').html(timeslotOptions); // Update the timeslot dropdown
                            $.each(response.api_response.timeslots, function(index, timeslot) {
                                const isDisabled = timeslot.is_booked ? 'disabled' : '';
                                //timeslotOptions += '<option value="' + timeslot.id + '" ${isDisabled} ${isSelected}>' + timeslot.value + '</option>';
                                const option = `
        <option value="${timeslot.id}" ${isDisabled}>
            ${timeslot.value} ${timeslot.is_booked ? '(Booked by: ' + (timeslot.booked_by.length ? timeslot.booked_by.join(', ') : 'N/A') + ')' : ''}
        </option>`;
                                $('#timeslot_id').append(option);
                            });
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

                $('#timeslot_id').html('<option value="">Select a room and date first</option>');
            }
        }


        $('#date, #room_id').change(fetchTimeslots);


        fetchTimeslots();
    });
</script>