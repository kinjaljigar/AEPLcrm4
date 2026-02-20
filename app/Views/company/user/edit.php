<?php $CompanyUser = $view_data['companyUser'];
$available_companies =   $view_data['available_companies'];
$available_projects =  $view_data['available_projects'];
?>

<h1>Company User List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>User</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">User List</h3>
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
                <form action="<?php echo site_url('companyuser/update/' . $CompanyUser['u_id']); ?>" method="post">
                    <div class="form-group">
                        <label for="name">company Name</label>
                        <select name="company_id" id="company_id" class="form-control" required>
                            <option></option>
                            <?php foreach ($available_companies as $available_company): ?>
                                <?php if ($available_company['status'] == 'Active') { ?>
                                    <option value="<?php echo $available_company['id']; ?>" <?php echo ($available_company['id'] == $CompanyUser['company_id']) ? 'selected' : ''; ?>>
                                        <?php echo $available_company['company_name']; ?> <!-- Replace 'value' with your field name if different -->
                                    </option>
                                <?php } ?>
                            <?php endforeach; ?>
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="project_ids">Select Projects</label>
                        <select name="project_ids[]" id="project_ids" class="form-control" multiple>
                            <?php
                            $selected_ids = explode(',', $CompanyUser['project_id']);
                            foreach ($available_projects as $project_id):
                                $is_selected = in_array($project_id['p_id'], $selected_ids) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $project_id['p_id']; ?>" <?php echo $is_selected; ?>>
                                    <?php echo $project_id['p_number'] . " - " . $project_id['p_name']; ?> <!-- Replace 'value' with your field name if different -->
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple options.</small>
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $CompanyUser['u_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Email</label>
                        <input type="text" name="email" class="form-control" value="<?php echo $CompanyUser['u_email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="<?php echo $CompanyUser['u_mobile']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Name</label>
                        <input type="text" name="username" class="form-control" value="<?php echo $CompanyUser['u_username']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="location">Status</label>

                        <select name="status" id="status" class="form-control" required>
                            <option value="Active" <?php echo ($CompanyUser['u_status'] == 'Active') ? 'selected' : ''; ?>>
                                Active
                            </option>
                            <option value="Deactive" <?php echo ($CompanyUser['u_status'] == 'Deactive') ? 'selected' : ''; ?>>
                                Deactive
                            </option>
                        </select>

                    </div>


                    <!-- Add other fields as needed -->

                    <button type="submit" class="btn btn-primary">Update Company User</button>
                </form>
                <form id="redirectForm" action="<?= base_url('companyuser'); ?>" method="post">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->