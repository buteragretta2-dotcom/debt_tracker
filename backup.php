<?php
require_once 'config.php';
require_once 'includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="debt_tracker_backup_' . date('Ymd_His') . '.sql"');
    echo "-- Debt Tracker backup " . date('c') . "\nSET FOREIGN_KEY_CHECKS=0;\n";
    foreach (['users', 'customers', 'debts', 'payments', 'reminders'] as $t) {
        echo "\nDELETE FROM `$t`;\n";
        foreach ($pdo->query("SELECT * FROM `$t`") as $row) {
            $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), array_values($row));
            echo "INSERT INTO `$t` (`" . implode('`,`', array_keys($row)) . "`) VALUES (" . implode(',', $vals) . ");\n";
        }
    }
    echo "\nSET FOREIGN_KEY_CHECKS=1;\n";
    exit;
}
$pageTitle = 'Backup';
require 'includes/header.php';
?>
<h1>Backup</h1>
<p>Download a copy of all data as an SQL file. Keep it on a flash disk or cloud storage, not only on this computer. To restore, import the file in phpMyAdmin after creating the tables from <code>schema.sql</code>.</p>
<form method="post"><?= csrf_field() ?><button type="submit">Download backup</button></form>
<?php require 'includes/footer.php'; ?>
