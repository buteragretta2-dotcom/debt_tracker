<?php
$pageTitle = 'Customer statement';
require 'includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
$s->execute([$id]);
$c = $s->fetch() ?: exit('Customer not found.');
$d = $pdo->prepare('SELECT * FROM v_debts WHERE customer_id = ? ORDER BY date_issued DESC, debt_id DESC');
$d->execute([$id]);
$debts = $d->fetchAll();
$p = $pdo->prepare('SELECT p.*, u.full_name AS by_user FROM payments p JOIN debts d ON d.debt_id = p.debt_id
                    JOIN users u ON u.user_id = p.received_by WHERE d.customer_id = ? ORDER BY p.payment_date DESC');
$p->execute([$id]);
$pays = $p->fetchAll();
$owed = array_sum(array_column($debts, 'balance'));
?>
<h1><?= e($c['full_name']) ?></h1>
<p>Phone: <?= e($c['phone']) ?> &nbsp;|&nbsp; Address: <?= e($c['address']) ?> &nbsp;|&nbsp; Credit limit: <?= CURRENCY . ' ' . money($c['credit_limit']) ?><br>
<strong>Total owed: <?= CURRENCY . ' ' . money($owed) ?></strong></p>
<p class="noprint"><a class="btn" href="debt_form.php?customer_id=<?= $id ?>">New debt</a>
<a class="btn" href="payment_form.php?customer_id=<?= $id ?>">Record payment</a>
<a class="btn alt" href="customer_form.php?id=<?= $id ?>">Edit</a>
<button class="alt" onclick="window.print()">Print statement</button></p>
<h2>Debts</h2>
<div class="tablewrap"><table>
<tr><th>Issued</th><th>Description</th><th>Due</th><th class="right">Amount</th><th class="right">Paid</th><th class="right">Balance</th><th>Status</th></tr>
<?php foreach ($debts as $r): ?>
<tr><td><?= e($r['date_issued']) ?></td><td><?= e($r['description']) ?></td><td><?= e($r['due_date']) ?></td>
<td class="right"><?= money($r['amount']) ?></td><td class="right"><?= money($r['paid']) ?></td><td class="right"><?= money($r['balance']) ?></td>
<td><?= status_badge($r['status']) ?></td></tr>
<?php endforeach; if (!$debts): ?><tr><td colspan="7">No debts recorded.</td></tr><?php endif; ?>
</table></div>
<h2>Payments</h2>
<div class="tablewrap"><table>
<tr><th>Date</th><th>Receipt</th><th>Method</th><th>Reference</th><th class="right">Amount</th><th>Received by</th></tr>
<?php foreach ($pays as $r): ?>
<tr><td><?= e($r['payment_date']) ?></td><td><a href="receipt.php?no=<?= e($r['receipt_no']) ?>"><?= e($r['receipt_no']) ?></a></td>
<td><?= e($r['method']) ?></td><td><?= e($r['reference']) ?></td><td class="right"><?= money($r['amount_paid']) ?></td><td><?= e($r['by_user']) ?></td></tr>
<?php endforeach; if (!$pays): ?><tr><td colspan="6">No payments yet.</td></tr><?php endif; ?>
</table></div>
<?php require 'includes/footer.php'; ?>
