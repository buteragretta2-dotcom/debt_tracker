<?php
$pageTitle = 'Reports';
require 'includes/header.php';
$type = $_GET['type'] ?? 'outstanding';
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$types = ['outstanding' => 'Outstanding debts', 'overdue' => 'Overdue debts', 'collections' => 'Payments received', 'top' => 'Top debtors'];
if (!isset($types[$type])) $type = 'outstanding';
?>
<h1>Reports</h1>
<form class="inline noprint" method="get">
<div><label>Report</label><select name="type"><?php foreach ($types as $k => $v): ?><option value="<?= $k ?>" <?= $k === $type ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
<div><label>From</label><input type="date" name="from" value="<?= e($from) ?>"></div>
<div><label>To</label><input type="date" name="to" value="<?= e($to) ?>"></div>
<button type="submit">Show</button> <button type="button" class="alt" onclick="window.print()">Print</button></form>
<h2><?= e($types[$type]) ?><?= $type === 'collections' ? ' (' . e($from) . ' to ' . e($to) . ')' : '' ?> - <?= e(BUSINESS_NAME) ?>, <?= date('Y-m-d') ?></h2>
<div class="tablewrap"><table>
<?php
if ($type === 'outstanding') {
    $rows = $pdo->query("SELECT c.full_name, c.phone, SUM(d.amount) AS total, SUM(d.paid) AS paid, SUM(d.balance) AS balance, MIN(CASE WHEN d.balance > 0 THEN d.due_date END) AS oldest_due
                         FROM v_debts d JOIN customers c ON c.customer_id = d.customer_id GROUP BY c.customer_id HAVING balance > 0 ORDER BY balance DESC")->fetchAll();
    echo '<tr><th>Customer</th><th>Phone</th><th class="right">Total debt</th><th class="right">Paid</th><th class="right">Balance</th><th>Oldest due date</th></tr>';
    $sum = 0;
    foreach ($rows as $r) { $sum += $r['balance'];
        echo '<tr><td>' . e($r['full_name']) . '</td><td>' . e($r['phone']) . '</td><td class="right">' . money($r['total']) . '</td><td class="right">' . money($r['paid']) . '</td><td class="right">' . money($r['balance']) . '</td><td>' . e($r['oldest_due']) . '</td></tr>'; }
    echo '<tr><th colspan="4">Total outstanding</th><th class="right">' . money($sum) . '</th><th></th></tr>';
} elseif ($type === 'overdue') {
    $rows = $pdo->query("SELECT c.full_name, c.phone, d.description, d.due_date, d.balance, DATEDIFF(CURDATE(), d.due_date) AS days_late
                         FROM v_debts d JOIN customers c ON c.customer_id = d.customer_id WHERE d.status = 'Overdue' ORDER BY d.due_date")->fetchAll();
    echo '<tr><th>Customer</th><th>Phone</th><th>Description</th><th>Due date</th><th>Days overdue</th><th class="right">Balance</th></tr>';
    $sum = 0;
    foreach ($rows as $r) { $sum += $r['balance'];
        echo '<tr><td>' . e($r['full_name']) . '</td><td>' . e($r['phone']) . '</td><td>' . e($r['description']) . '</td><td>' . e($r['due_date']) . '</td><td>' . (int)$r['days_late'] . '</td><td class="right">' . money($r['balance']) . '</td></tr>'; }
    echo '<tr><th colspan="5">Total overdue</th><th class="right">' . money($sum) . '</th></tr>';
} elseif ($type === 'collections') {
    $s = $pdo->prepare("SELECT p.payment_date, p.receipt_no, c.full_name, p.method, p.amount_paid, u.full_name AS by_user
                        FROM payments p JOIN debts d ON d.debt_id = p.debt_id JOIN customers c ON c.customer_id = d.customer_id
                        JOIN users u ON u.user_id = p.received_by WHERE DATE(p.payment_date) BETWEEN ? AND ? ORDER BY p.payment_date");
    $s->execute([$from, $to]);
    $rows = $s->fetchAll();
    echo '<tr><th>Date</th><th>Receipt</th><th>Customer</th><th>Method</th><th>Received by</th><th class="right">Amount</th></tr>';
    $sum = 0; $by = [];
    foreach ($rows as $r) { $sum += $r['amount_paid']; $by[$r['method']] = ($by[$r['method']] ?? 0) + $r['amount_paid'];
        echo '<tr><td>' . e($r['payment_date']) . '</td><td>' . e($r['receipt_no']) . '</td><td>' . e($r['full_name']) . '</td><td>' . e($r['method']) . '</td><td>' . e($r['by_user']) . '</td><td class="right">' . money($r['amount_paid']) . '</td></tr>'; }
    foreach ($by as $m => $v) echo '<tr><td colspan="5">Total ' . e($m) . '</td><td class="right">' . money($v) . '</td></tr>';
    echo '<tr><th colspan="5">Total received</th><th class="right">' . money($sum) . '</th></tr>';
} else {
    $rows = $pdo->query("SELECT c.full_name, c.phone, SUM(d.balance) AS balance FROM v_debts d JOIN customers c ON c.customer_id = d.customer_id
                         GROUP BY c.customer_id HAVING balance > 0 ORDER BY balance DESC LIMIT 10")->fetchAll();
    echo '<tr><th>#</th><th>Customer</th><th>Phone</th><th class="right">Balance</th></tr>';
    foreach ($rows as $i => $r) echo '<tr><td>' . ($i + 1) . '</td><td>' . e($r['full_name']) . '</td><td>' . e($r['phone']) . '</td><td class="right">' . money($r['balance']) . '</td></tr>';
}
if (!$rows) echo '<tr><td colspan="6">No data for this report.</td></tr>';
?>
</table></div>
<?php require 'includes/footer.php'; ?>
