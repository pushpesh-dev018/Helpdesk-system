<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_admin();

$success = $error = '';

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role  = $_POST['role'];
    $dept  = trim($_POST['department']);

    $chk = $conn->prepare("SELECT id FROM users WHERE email=?");
    $chk->bind_param("s", $email);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $error = "Email already exists.";
    } else {
        $ins = $conn->prepare("INSERT INTO users (name,email,password,role,department) VALUES (?,?,?,?,?)");
        $ins->bind_param("sssss", $name, $email, $pass, $role, $dept);
        $ins->execute() ? $success = "User added." : $error = "Failed to add user.";
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($del_id != current_user_id()) {
        $conn->query("DELETE FROM users WHERE id=$del_id");
        $success = "User deleted.";
    } else {
        $error = "Cannot delete yourself.";
    }
}

$users = $conn->query("SELECT u.*, COUNT(t.id) as ticket_count FROM users u LEFT JOIN tickets t ON t.user_id=u.id GROUP BY u.id ORDER BY u.created_at DESC");
?>
<h1 class="page-title">Manage Users</h1>

<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem">
    <div class="card">
        <h3 style="margin-bottom:1rem">All Users</h3>
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Dept</th><th>Tickets</th><th>Action</th></tr></thead>
            <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge <?= $u['role']==='admin'?'badge-critical':'badge-low' ?>"><?= $u['role'] ?></span></td>
                    <td><?= htmlspecialchars($u['department'] ?? '—') ?></td>
                    <td><?= $u['ticket_count'] ?></td>
                    <td><a href="?delete=<?= $u['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-bottom:1rem">Add New User</h3>
        <form method="POST">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <div class="form-group"><label>Role</label>
                <select name="role"><option value="user">User</option><option value="admin">Admin</option></select>
            </div>
            <div class="form-group"><label>Department</label><input type="text" name="department"></div>
            <button type="submit" name="add_user" class="btn-primary">Add User</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
