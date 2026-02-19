<?php
$token = $view_data['token'];
$available_companies =   $view_data['available_companies'];
$available_projects =  $view_data['available_projects'];
?>

<h1>Company User</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Company User</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Company User</h3>
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
                <form action="<?php echo site_url('companyuser/addData/'); ?>" method="post">
                    <div class="form-group">
                        <label for="name">company Name</label>
                        <select name="company_id" id="company_id" class="form-control" required>
                            <option></option>
                            <?php foreach ($available_companies as $available_company): ?>
                                <?php if ($available_company['status'] == 'Active') { ?>
                                    <option value="<?php echo $available_company['id']; ?>">
                                        <?php echo $available_company['company_name']; ?> <!-- Replace 'value' with your field name if different -->
                                    </option>
                                <?php } ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="project_ids">Select Projects</label>
                        <select name="project_ids[]" id="project_ids" class="form-control" multiple>
                            <?php foreach ($available_projects as $project_id): ?>
                                <option value="<?php echo $project_id['p_id']; ?>">
                                    <?php echo $project_id['p_number'] . " - " . $project_id['p_name']; ?> <!-- Replace 'value' with your field name if different -->
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple options.</small>
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Email</label>
                        <input type="text" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Mobile</label>
                        <input type="text" name="mobile" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Name</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="name">User Password</label>
                        <input type="password" name="password" class="form-control" required>
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


                    <!-- Add other fields as needed -->

                    <button type="submit" class="btn btn-primary">Add Company User</button>
                </form>
                <form id="redirectForm" action="<?= base_url('companyuser'); ?>" method="post">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->