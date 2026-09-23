<div class="content-wrapper">
    <section class="content-header">
        <h1>APL Pending Approvals</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">APL Approval Requests</h3>
            </div>
            <div class="box-body">
                <form method="GET" action="<?= base_url('home/approvals') ?>" style="margin-bottom:15px;">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="pl_id" class="form-control">
                                <option value="">-- All Project Leaders --</option>
                                <?php foreach ($view_data['pls'] as $pl): ?>
                                    <option value="<?= $pl['u_id'] ?>" <?= ($view_data['filter_pl'] == $pl['u_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pl['u_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                            <a href="<?= base_url('home/approvals') ?>" class="btn btn-default" style="margin-left:5px;"><i class="fa fa-times"></i> Reset</a>
                        </div>
                    </div>
                </form>
                <!-- Table 1: Request History -->
                <h4 style="margin-top:0; margin-bottom:10px;"><i class="fa fa-history"></i> Request History</h4>
                <?php if (empty($view_data['requests'])): ?>
                    <div class="alert alert-info"><i class="fa fa-check-circle"></i> No approval requests found.</div>
                <?php else: ?>
                <table class="table table-bordered table-hover" style="margin-bottom:30px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Requested by (PL)</th>
                            <th>Action</th>
                            <th>Requested On</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th>Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr = 1; foreach ($view_data['requests'] as $req): ?>
                        <tr id="apr_row_<?= $req['apr_id'] ?>">
                            <td><?= $sr++ ?></td>
                            <td><?= htmlspecialchars($req['emp_name'] ?? '-') ?><br>
                                <small class="text-muted"><?= htmlspecialchars($req['emp_email'] ?? '') ?></small></td>
                            <td><?= htmlspecialchars($req['u_department'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($req['pl_name'] ?? '-') ?></td>
                            <td>
                                <?php if (($req['apr_action'] ?? 'add') === 'add'): ?>
                                    <span class="label label-success"><i class="fa fa-plus"></i> Add APL</span>
                                <?php else: ?>
                                    <span class="label label-danger"><i class="fa fa-minus"></i> Remove APL</span>
                                <?php endif; ?>
                            </td>
                            <td><?= !empty($req['apr_created_at']) ? date('d-m-Y H:i', strtotime($req['apr_created_at'])) : '-' ?></td>
                            <td>
                                <?php if ($req['apr_status'] === 'Pending'): ?>
                                    <span class="label label-warning">Pending</span>
                                <?php elseif ($req['apr_status'] === 'Approved'): ?>
                                    <span class="label label-success">Approved</span>
                                <?php else: ?>
                                    <span class="label label-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($req['reviewed_by_name'] ?? '-') ?>
                                <?php if (!empty($req['apr_reviewed_at'])): ?>
                                    <br><small class="text-muted"><?= date('d-m-Y H:i', strtotime($req['apr_reviewed_at'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($req['apr_status'] === 'Pending'): ?>
                                    <button class="btn btn-success btn-sm" onclick="processRequest(<?= $req['apr_id'] ?>, 'approve')">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" style="margin-left:4px;" onclick="processRequest(<?= $req['apr_id'] ?>, 'reject')">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <!-- Table 2: PL → APL Summary -->
                <hr>
                <h4 style="margin-top:15px; margin-bottom:10px;"><i class="fa fa-users"></i> Project Leaders &amp; Their APLs</h4>
                <table id="aplSummaryTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project Leader</th>
                            <th>Department</th>
                            <th>Assistant Project Leaders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr2 = 1; foreach ($view_data['apl_summary'] as $plId => $pl): ?>
                        <tr>
                            <td><?= $sr2++ ?></td>
                            <td><?= htmlspecialchars($pl['pl_name']) ?></td>
                            <td><?= htmlspecialchars($pl['pl_department'] ?? '-') ?></td>
                            <?php
                                $aplPlainText = empty($pl['apls']) ? 'No APL assigned' : implode(', ', array_column($pl['apls'], 'u_name'));
                            ?>
                            <td data-export="<?= htmlspecialchars($aplPlainText) ?>">
                                <?php if (empty($pl['apls'])): ?>
                                    <span class="text-muted">No APL assigned</span>
                                <?php else: ?>
                                    <?php foreach ($pl['apls'] as $apl): ?>
                                        <span class="label label-success" style="margin-right:4px; display:inline-block; margin-bottom:3px;">
                                            <i class="fa fa-star"></i> <?= htmlspecialchars($apl['u_name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($view_data['apl_summary'])): ?>
                        <tr><td colspan="4" class="text-center text-muted">No project leaders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
    function document_ready() {
        var exportOpts = {
            format: {
                body: function(data, row, col, node) {
                    var exportVal = $(node).attr('data-export');
                    return exportVal !== undefined ? exportVal : $(node).text().trim();
                }
            }
        };
        $('#aplSummaryTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i> Excel', className: 'btn btn-success btn-sm', exportOptions: exportOpts },
                { extend: 'csvHtml5',   text: '<i class="fa fa-file-text-o"></i> CSV',   className: 'btn btn-default btn-sm', exportOptions: exportOpts },
                { extend: 'pdfHtml5',   text: '<i class="fa fa-file-pdf-o"></i> PDF',   className: 'btn btn-danger btn-sm',  exportOptions: exportOpts, orientation: 'landscape' },
                { extend: 'print',      text: '<i class="fa fa-print"></i> Print',       className: 'btn btn-info btn-sm',    exportOptions: exportOpts }
            ],
            paging: false,
            searching: false,
            info: false,
            ordering: true,
            order: [[1, 'asc']]
        });
    }

    function processRequest(apr_id, action) {
        var msg = action === 'approve'
            ? 'Approve this APL request?'
            : 'Reject this APL request?';
        showModal('confirm', msg, 'Confirm', 'modal-default', 'modal-sm', function() {
            doAjax('api/employees', 'POST', { act: 'apl_request', sub: action, apr_id: apr_id }, function(res) {
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
