<?php
$token = isset($view_data['token']) ? $view_data['token'] : '';
$tickets = isset($view_data['tickets']) ? $view_data['tickets'] : [];

?>

<h1>Tickets</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Add Ticket</h1>
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
                                <label for="ticket_number">Ticket #</label>
                                <input type="text" name="ticket_number" id="ticket_number" class="form-control" value="<?= $view_data['ticket_number']; ?>">
                            </div>

                            <div class="form-group mr-2">
                                <label for="subject">Title</label>
                                <input type="text" name="subject" id="subject" class="form-control" value="<?= $view_data['subject']; ?>">
                            </div>

                            <div class="form-group mr-2">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="open" <?= $view_data['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="pending" <?= $view_data['status'] == 'pending' ? 'selected' : '' ?>>pending</option>
                                    <option value="closed" <?= $view_data['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                                    <option value="">All</option>

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
                        </form>

                    </div>
                </div><br />
                <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <tr>
                        <th>Sr No</th>
                        <th>Date</th>
                        <th>Ticket Number</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Desktop</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php if (!empty($tickets)): ?> <?php $serial = 1; ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?= $serial++ ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($ticket->created_at)) ?></td>
                                <td><?= htmlspecialchars($ticket->ticket_number) ?></td>
                                <td><?= htmlspecialchars($ticket->subject) ?></td>
                                <td><?= htmlspecialchars($ticket->category_name) ?></td>
                                <td><?= $ticket->desktop_number ?></td>
                                <td><?= $ticket->created_by_name ?></td>
                                <td><?= htmlspecialchars($ticket->status) ?></td>

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
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No tickets found.</td>
                        </tr>
                    <?php endif; ?>

                </table>

            </div>

    </section>

</div><!-- /.content-wrapper -->