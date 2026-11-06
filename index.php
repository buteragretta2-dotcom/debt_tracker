<?php
$pageTitle = 'Dashboard';
require 'includes/header.php';
$owed    = $pdo->query('SELECT IFNULL(SUM(balance),0) FROM v_debts')->fetchColumn();
$overdue = $pdo->query("SELECT IFNULL(SUM(balance),0) FROM v_debts WHERE status='Overdue'")->fetchColumn();
$month   = $pdo->query("SELECT IFNULL(SUM(amount_paid),0) FROM payments WHERE DATE_FORMAT(payment_date,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();
$list = $pdo->query("SELECT d.debt_id, c.customer_id, c.full_name, c.phone, d.due_date, d.balance, DATEDIFF(CURDATE(), d.due_date) AS days_late
                     FROM v_debts d JOIN customers c ON c.customer_id = d.customer_id
                     WHERE d.status='Overdue' ORDER BY d.due_date LIMIT 10")->fetchAll();
?>
<h1>Dashboard</h1>
<div class="cards">
  <div class="card">Total owed<div class="n"><?= CURRENCY . ' ' . money($owed) ?></div></div>
  <div class="card">Overdue<div class="n"><?= CURRENCY . ' ' . money($overdue) ?></div></div>
  <div class="card">Paid this month<div class="n"><?= CURRENCY . ' ' . money($month) ?></div></div>
</div>
<h2>Overdue debts (oldest first)</h2>
<div class="tablewrap"><table>
<tr><th>Customer</th><th>Phone</th><th>Due date</th><th>Days late</th><th class="right">Balance</th><th></th></tr>
<?php foreach ($list as $r): ?>
<tr><td><a href="customer_view.php?id=<?= $r['customer_id'] ?>"><?= e($r['full_name']) ?></a></td><td><?= e($r['phone']) ?></td>
<td><?= e($r['due_date']) ?></td><td><?= (int)$r['days_late'] ?></td><td class="right"><?= money($r['balance']) ?></td>
<td><a href="payment_form.php?customer_id=<?= $r['customer_id'] ?>">Record payment</a></td></tr>
<?php endforeach; if (!$list): ?><tr><td colspan="6">No overdue debts.</td></tr><?php endif; ?>
</table></div>
<?php require 'includes/footer.php'; ?>
