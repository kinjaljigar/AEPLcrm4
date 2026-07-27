<div class="content-wrapper">
    <section class="content-header">
        <h1>My Team</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Team Members</h3>
            </div>
            <div class="box-body">
                <?php if (empty($view_data['team'])): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        No team members assigned to you yet.<br>
                        Ask <strong>Master Admin / Super Admin</strong> to open <strong>Employees</strong> page, edit each employee, and set <strong>Project Leader</strong> field to your name. Once assigned, they will appear here.
                    </div>
                <?php else: ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Department</th>
                            <th>APL Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr = 1; foreach ($view_data['team'] as $member): ?>
                        <tr id="row_<?= $member['u_id'] ?>">
                            <td><?= $sr++ ?></td>
                            <td><?= htmlspecialchars($member['u_name']) ?></td>
                            <td><?= htmlspecialchars($member['u_username']) ?></td>
                            <td><?= htmlspecialchars($member['u_email']) ?></td>
                            <td><?= htmlspecialchars($member['u_mobile']) ?></td>
                            <td><?= htmlspecialchars($member['u_department']) ?></td>
                            <td>
                                <?php if ($member['u_is_apl']): ?>
                                    <span class="label label-success">Assistant Project Leader</span>
                                <?php else: ?>
                                    <span class="label label-default">Employee</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($member['u_is_apl']): ?>
                                    <button type="button" class="btn btn-warning btn-sm"
                                        onclick="toggleAPL(<?= $member['u_id'] ?>, 0, this)">
                                        <i class="fa fa-times"></i> Remove APL
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-success btn-sm"
                                        onclick="toggleAPL(<?= $member['u_id'] ?>, 1, this)">
                                        <i class="fa fa-star"></i> Set as APL
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="text-muted" style="margin-top:10px;">
                    <i class="fa fa-info-circle"></i>
                    <strong>Assistant Project Leader (APL)</strong> can view all Mail Links assigned to you — read only, cannot reply.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
    function toggleAPL(uid, newVal, btn) {
        var msg = newVal == 1
            ? 'Set this employee as <b>Assistant Project Leader</b>?<br><small>They will be able to view all your Mail Links (read-only).</small>'
            : 'Remove <b>APL</b> role from this employee?<br><small>They will no longer have access to Mail Links.</small>';

        showModal('confirm', msg, 'Confirm', 'modal-default', 'modal-sm', function() {
            doAjax('api/employees', 'POST', { act: 'set_apl', target_uid: uid, is_apl: newVal }, function(res) {
                if (res.status == 'pass') {
                    showModal('ok', res.message, 'Success', 'modal-success', 'modal-sm', function(){}, function(){
                        location.reload();
                    });
                } else {
                    showModal('ok', res.message, 'Error', 'modal-danger', 'modal-sm');
                }
            });
        });
    }
</script>
