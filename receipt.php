<?php
$pageTitle = 'Receipt';
require 'includes/header.php';
$s = $pdo->prepare("SELECT p.*, d.description, c.full_name, c.phone, u.full_name AS by_user,
                    (SELECT balance FROM v_debts v WHERE v.debt_id = p.debt_id) AS balance_left
                    FROM payments p JOIN debts d ON d.debt_id = p.debt_id JOIN customers c ON c.customer_id = d.customer_id
                    JOIN users u ON u.user_id = p.received_by WHERE p.receipt_no = ?");
$s->execute([$_GET['no'] ?? '']);
$r = $s->fetch() ?: exit('Receipt not found.');
?>
<div class="receipt">
<h2 style="text-align:center"><?= e(BUSINESS_NAME) ?></h2>
<p style="text-align:center">PAYMENT RECEIPT</p>
<p>Receipt no: <strong><?= e($r['receipt_no']) ?></strong><br>Date: <?= e($r['payment_date']) ?></p>
<p>Customer: <?= e($r['full_name']) ?> (<?= e($r['phone']) ?>)<br>For: <?= e($r['description']) ?></p>
<p>Amount paid: <strong><?= CURRENCY . ' ' . money($r['amount_paid']) ?></strong><br>
Method: <?= e($r['method']) ?><?= $r['reference'] ? ' (' . e($r['reference']) . ')' : '' ?><br>
Balance remaining on this debt: <?= CURRENCY . ' ' . money($r['balance_left']) ?></p>
<p>Received by: <?= e($r['by_user']) ?></p>
</div>
<p class="noprint" style="text-align:center"><button onclick="window.print()">Print receipt</button> <a class="btn alt" href="payment_form.php">New payment</a></p>
<?php require 'includes/footer.php'; ?>
