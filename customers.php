<?php
$pageTitle = 'Customers';
require 'includes/header.php';
$q = trim($_GET['q'] ?? '');
$s = $pdo->prepare("SELECT c.*, IFNULL((SELECT SUM(balance) FROM v_debts v WHERE v.customer_id = c.customer_id),0) AS owed
                    FROM customers c WHERE c.full_name LIKE ? OR c.phone LIKE ? ORDER BY c.full_name");
$s->execute(["%$q%", "%$q%"]);
$rows = $s->fetchAll();
?>
<h1>Customers</h1>
<form class="inline" method="get"><div><label>Search name or phone</label><input name="q" value="<?= e($q) ?>"></div>
<button type="submit">Search</button> <a class="btn alt" href="customer_form.php">Add customer</a></form>
<div class="tablewrap"><table>
<tr><th>Name</th><th>Phone</th><th class="right">Credit limit</th><th class="right">Owed</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><a href="customer_view.php?id=<?= $r['customer_id'] ?>"><?= e($r['full_name']) ?></a></td><td><?= e($r['phone']) ?></td>
<td class="right"><?= money($r['credit_limit']) ?></td><td class="right"><?= money($r['owed']) ?></td>
<td><a href="customer_form.php?id=<?= $r['customer_id'] ?>">Edit</a></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5">No customers found.</td></tr><?php endif; ?>
</table></div>
<?php require 'includes/footer.php'; ?>
