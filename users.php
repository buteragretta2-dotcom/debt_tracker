<?php
$pageTitle = 'Users';
require 'includes/header.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'] ?? '';
    try {
        if ($act === 'add') {
            $name = trim($_POST['full_name'] ?? ''); $user = trim($_POST['username'] ?? ''); $pass = $_POST['password'] ?? '';
            $role = ($_POST['role'] ?? '') === 'Administrator' ? 'Administrator' : 'Staff';
            if ($name === '' || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $user)) throw new InvalidArgumentException('Enter a name and a username of 3 to 50 letters or digits.');
            if (strlen($pass) < 8) throw new InvalidArgumentException('Password must have at least 8 characters.');
            $pdo->prepare('INSERT INTO users (full_name, username, password_hash, role) VALUES (?,?,?,?)')->execute([$name, $user, password_hash($pass, PASSWORD_DEFAULT), $role]);
            flash('User created.');
        } elseif ($act === 'toggle') {
            $id = (int)$_POST['user_id'];
            if ($id === (int)$_SESSION['user_id']) throw new InvalidArgumentException('You cannot deactivate your own account.');
            $pdo->prepare("UPDATE users SET status = IF(status='Active','Inactive','Active') WHERE user_id = ?")->execute([$id]);
            flash('User status changed.');
        } elseif ($act === 'password') {
            if (strlen($_POST['password'] ?? '') < 8) throw new InvalidArgumentException('Password must have at least 8 characters.');
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), (int)$_POST['user_id']]);
            flash('Password changed.');
        }
        redirect('users.php');
    } catch (PDOException $ex) { echo '<div class="alert error">That username already exists.</div>';
    } catch (InvalidArgumentException $ex) { echo '<div class="alert error">' . e($ex->getMessage()) . '</div>'; }
}
$users = $pdo->query('SELECT * FROM users ORDER BY full_name')->fetchAll();
?>
<h1>Users</h1>
<div class="tablewrap"><table><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($users as $u): ?>
<tr><td><?= e($u['full_name']) ?></td><td><?= e($u['username']) ?></td><td><?= e($u['role']) ?></td><td><?= e($u['status']) ?></td>
<td><form method="post" class="inline" style="margin:0"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
<button name="action" value="toggle" class="alt">Activate / deactivate</button>
<input type="password" name="password" placeholder="New password" style="width:150px"><button name="action" value="password">Set password</button></form></td></tr>
<?php endforeach; ?></table></div>
<h2>Add user</h2>
<form class="box" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="add">
<label>Full name</label><input name="full_name" required>
<label>Username</label><input name="username" required>
<label>Password (at least 8 characters)</label><input type="password" name="password" required>
<label>Role</label><select name="role"><option>Staff</option><option>Administrator</option></select>
<p><button type="submit">Create user</button></p></form>
<?php require 'includes/footer.php'; ?>
