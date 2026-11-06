<?php
$pageTitle = 'Record payment';
require 'includes/header.php';
$customers = $pdo->query('SELECT customer_id, full_name, phone FROM customers ORDER BY full_name')->fetchAll();
$cid = (int)($_GET['customer_id'] ?? $_POST['customer_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debt_id'])) {
    csrf_check();
    try {
        $rc = record_payment($pdo, (int)$_POST['debt_id'], (float)($_POST['amount'] ?? 0), $_POST['method'] ?? '', $_POST['reference'] ?? '', (int)$_SESSION['user_id']);
        flash('Payment recorded.');
        redirect('receipt.php?no=' . urlencode($rc));
    } catch (InvalidArgumentException $ex) { echo '<div class="alert error">' . e($ex->getMessage()) . '</div>'; }
}
$open = [];
if ($cid) {
    $s = $pdo->prepare("SELECT * FROM v_debts WHERE customer_id = ? AND balance > 0 ORDER BY due_date");
    $s->execute([$cid]);
    $open = $s->fetchAll();
}
?>
<h1>Record a payment</h1>
<form class="inline" method="get"><div><label>Customer</label><select name="customer_id" onchange="this.form.submit()">
<option value="">- select -</option>
<?php foreach ($customers as $c): ?><option value="<?= $c['customer_id'] ?>" <?= $c['customer_id'] == $cid ? 'selected' : '' ?>><?= e($c['full_name'] . ' (' . $c['phone'] . ')') ?></option><?php endforeach; ?></select></div>
<noscript><button type="submit">Show debts</button></noscript></form>
<?php if ($cid && !$open): ?><p>This customer has no open debts.</p><?php endif; ?>
<?php if ($open): ?>
<form class="box" method="post"><?= csrf_field() ?><input type="hidden" name="customer_id" value="<?= $cid ?>">
<label>Debt</label><select name="debt_id" required>
<?php foreach ($open as $d): ?><option value="<?= $d['debt_id'] ?>"><?= e($d['date_issued'] . ' - ' . $d['description'] . ' - balance ' . money($d['balance']) . ' (' . $d['status'] . ')') ?></option><?php endforeach; ?></select>
<label>Amount paid (<?= CURRENCY ?>)</label><input type="number" name="amount" min="1" step="1" required>
<label>Method</label><select name="method"><option>Cash</option><option>Mobile Money</option><option>Bank</option></select>
<label>Reference (mobile money / bank transaction ID)</label><input name="reference" maxlength="50">
<p><button type="submit">Save payment and show receipt</button></p>
</form>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
