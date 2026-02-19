<?php
$token = $view_data['token'];
?>

<h1>Company List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Add Company</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Add Company</h3>
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
                <form action="<?php echo site_url('company/addData/'); ?>" method="post">
                    <div class="form-group">
                        <label for="name">company Name</label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Company Address</label>
                        <input type="text" name="address" class="form-control" required>
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
                    <button type="submit" class="btn btn-primary">Add Company</button>
                </form>
                <form id="redirectForm" action="<?= base_url('company'); ?>" method="post">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->