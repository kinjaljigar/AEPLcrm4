<?php
$token = $view_data['token'];
?>

<h1>Ticket Category</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Add Category</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Add Category</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php if (!empty($view_data['error_message'])): ?>
                    <div class="alert alert-danger"><?= $view_data['error_message'] ?></div>
                <?php endif; ?>
                <form action="<?php echo site_url('ticket/cat/addData/'); ?>" method="post">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Category Description</label>
                        <input type="text" name="description" class="form-control">
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
                                    foreach ($view_data['employees'] as $employee) {
                                    ?>
                                        <tr>
                                            <td>
                                                <label class="check_container">
                                                    <input type="checkbox" id="user_ids<?php echo $employee['u_id']; ?>"
                                                        name="user_ids[]" value="<?php echo $employee['u_id']; ?>">
                                                    <span class="checkmark assigns"></span>
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

                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
                <form id="redirectForm" action="<?= base_url('ticket-category'); ?>" method="post">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->
<script>
    function LoadData() {
        doAjax('api/employees', 'POST', {
            txt_search: $("#txt_search").val(),
            act: "list_task"
        }, function(res) {
            var record = res.data;
            $('#employee_list').empty();
            html = '';
            $.each(record, function(key, value) {
                console.log(value);
                Uid = value[0];
                UName = value[2];
                UType = value[6];
                ActPrj = res.active_projects[Uid] ?? 0;
                ActTask = res.active_tasks[Uid] ?? 0;
                Leaves = res.leaves[Uid] ?? '';
                html = html + '<tr>';
                html = html + '<td><label class="check_container"><input type="checkbox" id="u_ids_' + Uid +
                    '" name="u_id[]" value="' + Uid + '">';
                html = html + '<span class="checkmark assigns"></span></label></td>';
                html = html + '<td>' + UName + '</td>';
                html = html + '<td>' + UType + '</td>';
                html = html + '</tr>';

            });
            if (html != '') {
                $('#employee_list').html(html);
                //assign_users();
            } else {
                $('#employee_list').html(
                    "<tr><td colspan='3'>There is not any <b>Employees</b> added with your criteria.</td></tr>");
            }
        });
    }
</script>