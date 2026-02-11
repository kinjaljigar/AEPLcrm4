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
                    <div class="col-md-5">
                        <a href="<?php echo site_url('ticket/add/'); ?>"
                            class="btn btn-primary pull-right">Add New Ticket</a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table id="datatable" class="table table-bordered table-hover responsive nowrap" width="100% ">
                    <tr>
                        <th>Ticket Number</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?= htmlspecialchars($ticket->ticket_number) ?></td>
                                <td><?= htmlspecialchars($ticket->subject) ?></td>
                                <td><?= htmlspecialchars($ticket->category_name) ?></td>
                                <td><?= htmlspecialchars($ticket->status) ?></td>
                                <td><?= htmlspecialchars($ticket->created_by_name) ?></td>
                                <td>
                                    <a href="<?= site_url('ticket/view/' . $ticket->id) ?>" class="btn btn-success btn-md"><i class="fa fa-edit"></i></a>
                                    <?php if ($ticket->status == 'open'): ?>
                                        <a href="<?= site_url('ticket/close/' . $ticket->id) ?>" onclick="return confirm('Are you sure you want to close this ticket?')">Close</a>
                                    <?php else: ?>
                                        Closed
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