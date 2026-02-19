<?php $category = $view_data['category'];
print_r($category);
?>

<h1>Category</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Ticket Category</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Edit Category</h3>
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
                <form action="<?php echo site_url('ticket/cat/update/' . $category['id']); ?>" method="post">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $category['name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Category Description</label>
                        <input type="text" name="description" class="form-control" value="<?php echo $category['description']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Status</label>

                        <select name="status" id="status" class="form-control" required>
                            <option value="Active">
                                Active
                            </option>
                            <option value="Deactive">
                                Deactive
                            </option>
                        </select>

                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <table id="user_table" class="table table-bordered table-hover responsive nowrap"
                                width="100% ">
                                <thead>
                                    <tr>
                                        <th width="30"> </th>
                                        <th>Employee Name</th>
                                        <th>Employee Type</th>

                                    </tr>
                                </thead>
                                <div class="col-xs-12">
                                    Filter: <input class="form-control" name="txt_search" id="txt_search"
                                        style="width:150px; display:inline" placeholder="Employee Name" />
                                    <button type="button" id="main_add_button" onclick="LoadData();"
                                        class="btn btn-primary margin">Show Employees</button>
                                </div>
                                <tbody class="admin_list" id="employee_list">
                                    <?php
                                    $selected_ids = explode(', ', $category['assigned_users_ids']);
                                    print_r($selected_ids);
                                    foreach ($view_data['employees'] as $employee) {
                                        $is_selected = in_array($employee['u_id'], $selected_ids) ? 'selected' : '';
                                    ?>
                                        <tr>
                                            <td>
                                                <label class="check_container">
                                                    <input type="checkbox" id="user_ids<?php echo $employee['u_id']; ?>"
                                                        name="user_ids[]" value="<?php echo $employee['u_id']; ?>" <?php echo $is_selected; ?>>
                                                    <!-- <span class="checkmark assigns"></span> -->
                                                </label>
                                            </td>
                                            <td><?php echo $employee['u_name']; ?></td>
                                            <td><?php echo $employee['u_type']; ?></td>

                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
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
                        // Handle success response
                        console.log('API called successfully', response);
                        const preSelectedIds = <?php echo json_encode($conference['time_slots_ids']); ?>;
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

                $('#timeslot_id').html('<option value="">Select a room and date first</option>');
            }
        }


        $('#date, #room_id').change(fetchTimeslots);


        fetchTimeslots();
    });
</script>