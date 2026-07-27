<?php
$token        = isset($view_data['token'])   ? $view_data['token']   : '';
$tickets      = isset($view_data['tickets']) ? $view_data['tickets'] : [];
$show_closed_cols = (isset($view_data['status']) && $view_data['status'] === 'closed');
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Assigned Tickets</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-7">
                        <h3 class="box-title">Tickets</h3>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <form method="post" action="<?= site_url('ticket/assigned') ?>" class="form-inline mb-3">
                            <div class="form-group mr-2">
                                <label for="created_by">Created By</label>
                                <input type="text" name="created_by" id="created_by" class="form-control" value="<?= htmlspecialchars($view_data['created_by']); ?>">
                            </div>

                            <div class="form-group mr-2">
                                <label for="desktop_number">Desktop No.</label>
                                <input type="text" name="desktop_number" id="desktop_number" class="form-control" value="<?= htmlspecialchars($view_data['desktop_number']); ?>">
                            </div>

                            <div class="form-group mr-2">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="" <?= $view_data['status'] === '' ? 'selected' : '' ?>>All</option>
                                    <option value="open" <?= $view_data['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="pending" <?= $view_data['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="closed" <?= $view_data['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </div>

                            <div class="form-group mr-2">
                                <label>From</label>
                                <input type="date" name="from_date" class="form-control" value="<?= $view_data['from_date']; ?>">
                            </div>

                            <div class="form-group mr-2">
                                <label>To</label>
                                <input type="date" name="to_date" class="form-control" value="<?= $view_data['to_date']; ?>">
                            </div>

                            <button type="submit" class="btn btn-success">Search</button>
                            <a href="<?= site_url('ticket/assigned') ?>" class="btn btn-secondary"><i class="fa fa-refresh"></i></a>
                            <a id="btn_export" href="#" class="btn btn-warning" style="margin-left:10px;"><i class="fa fa-file-excel-o"></i> Export with Conversations</a>
                        </form>

                    </div>
                </div><br />
                <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Date</th>
                            <th>Ticket Number</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Desktop</th>
                            <th>Created By</th>
                            <th>Status</th>
                            <?php if ($show_closed_cols): ?>
                            <th>Closed Date</th>
                            <th>Closed By</th>
                            <?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $serial = 1; foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?= $serial++ ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($ticket->created_at)) ?></td>
                                <td><?= htmlspecialchars($ticket->ticket_number) ?></td>
                                <td><?= htmlspecialchars($ticket->subject) ?></td>
                                <td><?= htmlspecialchars($ticket->category_name) ?></td>
                                <td><?= $ticket->desktop_number ?></td>
                                <td><?= $ticket->created_by_name ?></td>
                                <td><?= ucfirst(htmlspecialchars($ticket->status)) ?></td>
                                <?php if ($show_closed_cols): ?>
                                <td><?= !empty($ticket->closed_at) ? date('d M Y, h:i A', strtotime($ticket->closed_at)) : '' ?></td>
                                <td><?= htmlspecialchars($ticket->closed_by_name ?? '') ?></td>
                                <?php endif; ?>
                                <td>
                                    <a href="<?= site_url('ticket/view/' . $ticket->id . '?from=assign') ?>" class="btn btn-primary btn-md"><i class="fa fa-eye"></i></a>
                                    <?php if ($ticket->status == 'open' || $ticket->status == 'pending'): ?>
                                        <a href="<?= site_url('ticket/close/' . $ticket->id . '?from=assign') ?>" class="btn btn-success btn-md" onclick="return confirm('Are you sure you want to close this ticket?')"><i class="fa fa-close"></i></a>
                                    <?php else: ?>
                                        &nbsp;
                                        <a href="<?= site_url('ticket/deleteassign/' . $ticket->id) ?>" class="btn btn-danger btn-md" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

    </section>

</div><!-- /.content-wrapper -->

<script>
    <?php
    $u_type = isset($view_data['admin_session']['u_type']) ? $view_data['admin_session']['u_type'] : '';
    $show_export = in_array($u_type, ['Master Admin', 'Super Admin', 'Bim Head']);
    ?>
    function document_ready() {
        $('#datatable').DataTable({
            "paging": true,
            "searching": true,
            "pageLength": 100,
            "bSort": false,
            "language": {
                "emptyTable": "No tickets found."
            },
        });

        // Wire up Export with Conversations button using current filter values
        $('#btn_export').on('click', function(e) {
            e.preventDefault();
            var params = new URLSearchParams({
                created_by:     $('input[name="created_by"]').val()     || '',
                desktop_number: $('input[name="desktop_number"]').val() || '',
                status:         $('select[name="status"]').val()        || '',
                from_date:      $('input[name="from_date"]').val()      || '',
                to_date:        $('input[name="to_date"]').val()        || '',
            });
            window.location.href = '<?= site_url('ticket/export_assigned') ?>?' + params.toString();
        });
    }
</script>