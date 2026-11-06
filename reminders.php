<?php
$pageTitle = 'Reminders';
require 'includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $channel = in_array($_POST['channel'] ?? '', ['Call', 'SMS', 'WhatsApp', 'In person'], true) ? $_POST['channel'] : 'Call';
    $pdo->prepare('INSERT INTO reminders (debt_id, channel, message) VALUES (?,?,?)')
        ->execute([(int)$_POST['debt_id'], $channel, trim($_POST['message'] ?? '')]);
    flash('Reminder logged.');
    redirect('reminders.php');
}
$rows = $pdo->query("SELECT d.debt_id, d.due_date, d.balance, d.status, c.full_name, c.phone,
                     (SELECT MAX(sent_at) FROM reminders r WHERE r.debt_id = d.debt_id) AS last_reminder
                     FROM v_debts d JOIN customers c ON c.customer_id = d.customer_id
                     WHERE d.balance > 0 AND d.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                     ORDER BY d.due_date")->fetchAll();
$hist = $pdo->query("SELECT r.*, c.full_name FROM reminders r JOIN debts d ON d.debt_id = r.debt_id
                     JOIN customers c ON c.customer_id = d.customer_id ORDER BY r.sent_at DESC LIMIT 15")->fetchAll();
?>
<h1>Reminders</h1>
<p>Debts that are overdue or due within 3 days. Send the message by phone or SMS, then log it here.</p>
<div class="tablewrap"><table>
<tr><th>Customer</th><th>Due</th><th class="right">Balance</th><th>Status</th><th>Last reminder</th><th>Log reminder</th></tr>
<?php foreach ($rows as $r):
  $msg = "Hello {$r['full_name']}, this is a reminder from " . BUSINESS_NAME . " that " . CURRENCY . ' ' . money($r['balance']) . " is due on {$r['due_date']}. Thank you."; ?>
<tr><td><?= e($r['full_name']) ?><br><a href="sms:<?= e($r['phone']) ?>?body=<?= rawurlencode($msg) ?>"><?= e($r['phone']) ?> (send SMS)</a></td>
<td><?= e($r['due_date']) ?></td><td class="right"><?= money($r['balance']) ?></td><td><?= status_badge($r['status']) ?></td>
<td><?= e($r['last_reminder'] ?? 'never') ?></td>
<td><form method="post" class="inline" style="margin:0"><?= csrf_field() ?><input type="hidden" name="debt_id" value="<?= $r['debt_id'] ?>">
<input type="hidden" name="message" value="<?= e($msg) ?>">
<select name="channel"><option>Call</option><option>SMS</option><option>WhatsApp</option><option>In person</option></select><button type="submit">Log</button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6">Nothing to remind today.</td></tr><?php endif; ?>
</table></div>
<h2>Recent reminders</h2>
<div class="tablewrap"><table><tr><th>Date</th><th>Customer</th><th>Channel</th></tr>
<?php foreach ($hist as $h): ?><tr><td><?= e($h['sent_at']) ?></td><td><?= e($h['full_name']) ?></td><td><?= e($h['channel']) ?></td></tr>
<?php endforeach; if (!$hist): ?><tr><td colspan="3">None yet.</td></tr><?php endif; ?></table></div>
<?php require 'includes/footer.php'; ?>
