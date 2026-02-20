<?php $companies = $view_data['company'];
$company = $companies['meeting'];
?>

<h1>Company List</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Edit Company</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Edit Company</h3>
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
                <form action="<?php echo site_url('company/update/' . $company['id']); ?>" method="post">
                    <div class="form-group">
                        <label for="name">company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?php echo $company['company_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Company Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo $company['address']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Status</label>

                        <select name="status" id="status" class="form-control" required>
                            <option value="Active" <?php echo ($company['status'] == 'Active') ? 'selected' : ''; ?>>
                                Active
                            </option>
                            <option value="Deactive" <?php echo ($company['status'] == 'Deactive') ? 'selected' : ''; ?>>
                                Deactive
                            </option>
                        </select>

                    </div>


                    <!-- Add other fields as needed -->

                    <button type="submit" class="btn btn-primary">Update Company</button>
                </form>
                <form id="redirectForm" action="<?= base_url('company'); ?>" method="post">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>
            <div class="box-footer"></div>


        </div>

    </section>

</div><!-- /.content-wrapper -->