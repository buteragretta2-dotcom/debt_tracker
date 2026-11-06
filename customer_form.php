<?php
$pageTitle = 'Customer';
require 'includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$c = ['full_name' => '', 'phone' => '', 'address' => '', 'credit_limit' => '0'];
if ($id) {
    $s = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
    $s->execute([$id]);
    $c = $s->fetch() ?: exit('Customer not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $c = array_merge($c, $_POST);
    try {
        if ($id) { update_customer($pdo, $id, $_POST); flash('Customer updated.'); redirect('customer_view.php?id=' . $id); }
        $new = add_customer($pdo, $_POST); flash('Customer registered.'); redirect('customer_view.php?id=' . $new);
    } catch (InvalidArgumentException $ex) { echo '<div class="alert error">' . e($ex->getMessage()) . '</div>'; }
}
?>
<h1><?= $id ? 'Edit customer' : 'Add customer' ?></h1>
<form class="box" method="post"><?= csrf_field() ?>
<label>Full name</label><input name="full_name" value="<?= e($c['full_name']) ?>" required>
<label>Phone number</label><input name="phone" value="<?= e($c['phone']) ?>" required>
<label>Address</label><input name="address" value="<?= e($c['address']) ?>">
<label>Credit limit (<?= CURRENCY ?>)</label><input type="number" name="credit_limit" min="0" step="1" value="<?= e((string)$c['credit_limit']) ?>">
<p><button type="submit">Save</button> <a class="btn alt" href="customers.php">Cancel</a></p>
</form>
<?php require 'includes/footer.php'; ?>
