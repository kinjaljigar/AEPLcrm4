<?php
$from = $this->input->get('from');
$backUrl = base_url('ticket/my');
if ($from === 'assign') {
    $backUrl = base_url('ticket/assigned');
}
$is_creator = $view_data['is_ticket_creator'];
$is_assigned = $view_data['is_assigned_user'];
$ticket = $view_data['ticket'];
$attachments = $view_data['attachments'] ?? [];
$u_id = $view_data['admin_session']['u_id'];
$hasOtherUsers = false;
if (!empty($ticket->assigned_users)) {
    foreach ($ticket->assigned_users as $user) {
        if ($u_id != $user->u_id) {
            $hasOtherUsers = true;
            break;
        }
    }
}
?><h1>Tickets</h1>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>View Ticket</h1>
    </section>
    <section class="content">
        <div class="box box-sbpink">
            <div class="box-header">

            </div>
            <div class="box-body">
                <h2>Ticket #<?= htmlspecialchars($ticket->ticket_number) ?></h2>
                <p>(<?= date('d M Y, h:i A', strtotime($ticket->created_at)) ?>)</p><br />
                <p>Desktop Number: <?= htmlspecialchars($ticket->desktop_number) ?></p>
                <p>Subject: <?= htmlspecialchars($ticket->subject) ?></p>
                <p>Description: <?= htmlspecialchars($ticket->description) ?></p>
                <p>Category: <?= htmlspecialchars($ticket->category_name) ?></p>
                <?php if ($u_id != $ticket->u_id): ?>
                    <p>Created by: <?= htmlspecialchars($ticket->created_by_name) ?></p>
                <?php endif; ?>
                <?php if ($hasOtherUsers): ?>
                    <h3>Responsible Users:</h3>
                    <ul>
                        <?php if (!empty($ticket->assigned_users)): ?>
                            <?php foreach ($ticket->assigned_users as $user): ?>
                                <?php if ($u_id != $user->u_id): ?>
                                    <li><?= htmlspecialchars($user->u_name) ?> (<?= htmlspecialchars($user->u_email) ?>)</li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No assigned users</li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($attachments)): ?>
                    <h3>Attachments:</h3>
                    <ul>
                        <?php foreach ($attachments as $file): ?>
                            <?php
                            $file_url = base_url('assets/tickets/' . $ticket->id . '/' . $file->file_name);
                            ?>
                            <li>
                                <a href="<?= $file_url ?>" target="_blank">
                                    <?= htmlspecialchars($file->file_name) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No attachments uploaded.</p>
                <?php endif; ?>

                <h3>Conversation</h3>

                <?php if (!empty($view_data['messages'])): ?>
                    <div style="border: 1px solid #ccc; padding: 10px;">
                        <?php foreach ($view_data['messages'] as $message): ?>
                            <div style="margin-bottom: 15px; padding: 8px; border-bottom: 1px solid #eee;">
                                <strong><?= htmlspecialchars($message->sender_name ?? 'User') ?></strong>
                                <span style="color: #888; font-size: 12px;">
                                    (<?= date('d M Y, h:i A', strtotime($message->created_at)) ?>)
                                </span>
                                <p><?= nl2br(htmlspecialchars($message->message)) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No messages yet.</p>
                <?php endif; ?>



                <?php if ($ticket->status !== 'closed' && ($is_assigned || ($is_creator && $ticket->status == 'pending'))): ?>
                    <h4>Reply:</h4>
                    <form method="post" action="<?= site_url('ticket/add_message/' . $ticket->id) ?>">
                        <textarea name="message" class="form-control" required rows="4" cols="50"></textarea><br /><br />
                        <input type="hidden" name="from" id="from" value="<?php echo $from; ?>" />
                        <button type="submit" class="btn btn-success btn-md">Submit</button>
                    </form>
                <?php endif; ?>

                <?php if ($is_assigned && $ticket->status !== 'closed'): ?>
                    <br />
                    <a href="<?= site_url('ticket/close/' . $ticket->id . (!empty($from) ? '?from=' . $from : '')) ?>" onclick="return confirm('Close this ticket?')" class="btn btn-success btn-md">Close Ticket</a>
                <?php endif; ?>
                <br /><br />
                <?php if ($ticket->status === 'closed'): ?>
                    <p><strong>This ticket is closed. You cannot reply anymore.</strong></p>
                <?php endif; ?>

                <form id="redirectForm" action="<?= $backUrl ?>" method="post">
                    <br /><button type="submit" class="btn btn-primary">Back</button>
                </form>
            </div>

    </section>

</div>