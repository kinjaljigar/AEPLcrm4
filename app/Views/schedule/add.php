<?php
$timeslots = $view_data['timeslots'];
$token = $view_data['token'];
?>

<h1>Add Schedule</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Schedule</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Add Schedule</h3>
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
                <form action="<?php echo site_url('schedule/addData/'); ?>" method="post">
                    <div class="form-group">
                        <label for="shedule_type">Schedule Type</label>
                        <select name="shedule_type" id="shedule_type" class="form-control" required>
                            <option value="Personal">
                                Personal
                            </option>
                            <option value="Official">
                                Official
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Schedule Title</label>
                        <input type="text" name="title" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Schedule Description</label>
                        <input type="text" name="description" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="" required>
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

                    <button type="submit" class="btn btn-primary">Add Schedule</button>
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
            if (selectedDate) {
                const apiUrl = `${cliBaseUrl}schedule/timeslots/${selectedDate}`;

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

                //                 console.log('Fetching available timeslots for Add schedule...');

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
                    url: '<?php echo site_url('ScheduleController/updateSchedule'); ?>', // Use CodeIgniter's site_url helper to generate the correct URL
                    type: 'POST',
                    data: {
                        date: selectedDate,
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

                $('#timeslot_id').html('<option value="">Select date first</option>');
            }
        }


        $('#date').change(fetchTimeslots);


        fetchTimeslots();
    });
</script>