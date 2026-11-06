<?php
$pageTitle = 'New debt';
require 'includes/header.php';
$customers = $pdo->query('SELECT customer_id, full_name, phone FROM customers ORDER BY full_name')->fetchAll();
$in = ['customer_id' => (int)($_GET['customer_id'] ?? 0), 'description' => '', 'amount' => '', 'date_issued' => date('Y-m-d'), 'due_date' => date('Y-m-d', strtotime('+30 days'))];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $in = array_merge($in, $_POST);
    try {
        add_debt($pdo, $_POST, (int)$_SESSION['user_id'], is_admin() && !empty($_POST['override']));
        flash('Debt recorded.');
        redirect('customer_view.php?id=' . (int)$_POST['customer_id']);
    } catch (InvalidArgumentException $ex) { echo '<div class="alert error">' . e($ex->getMessage()) . '</div>'; }
}
?>
<h1>Record a debt (credit sale)</h1>
<form class="box" method="post"><?= csrf_field() ?>
<label>Customer</label><select name="customer_id" required><option value="">- select -</option>
<?php foreach ($customers as $c): ?><option value="<?= $c['customer_id'] ?>" <?= $c['customer_id'] == $in['customer_id'] ? 'selected' : '' ?>><?= e($c['full_name'] . ' (' . $c['phone'] . ')') ?></option><?php endforeach; ?></select>
<label>Description of goods</label><input name="description" value="<?= e($in['description']) ?>">
<label>Amount (<?= CURRENCY ?>)</label><input type="number" name="amount" min="1" step="1" value="<?= e((string)$in['amount']) ?>" required>
<label>Date issued</label><input type="date" name="date_issued" value="<?= e($in['date_issued']) ?>" required>
<label>Due date</label><input type="date" name="due_date" value="<?= e($in['due_date']) ?>" required>
<?php if (is_admin()): ?><label><input type="checkbox" name="override" value="1"> Approve even if above the credit limit</label><?php endif; ?>
<p><button type="submit">Save debt</button> <a class="btn alt" href="customers.php">Cancel</a></p>
</form>
<?php require 'includes/footer.php'; ?>
