<?php
require_once 'config.php';
require_once 'includes/functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $s = $pdo->prepare("SELECT user_id, full_name, password_hash, role FROM users WHERE username = ? AND status = 'Active'");
    $s->execute([trim($_POST['username'] ?? '')]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['full_name'] = $u['full_name'];
        $_SESSION['role'] = $u['role'];
        redirect('index.php');
    }
    $error = 'Invalid username or password.';
}
?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Debt & Payment Tracker</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="login"><form class="box" method="post">
<h1>Debt &amp; Payment Tracker</h1>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?= csrf_field() ?>
<label>Username</label><input name="username" required autofocus>
<label>Password</label><input type="password" name="password" required>
<p><button type="submit">Login</button></p>
</form></div></body></html>
