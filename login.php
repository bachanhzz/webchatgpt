<?php
require_once __DIR__ . '/functions.php';
if (current_user()) redirect('dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id,password_hash FROM users WHERE email=?');
    $stmt->execute([$email]); $row = $stmt->fetch();
    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$row['id'];
        redirect('dashboard.php');
    }
    flash('error', 'Email hoặc mật khẩu không chính xác.');
}
page_header('Đăng nhập');
?>
<form class="form-card" method="post">
<h1>Đăng nhập</h1><p class="muted">Tiếp tục công việc của bạn.</p>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Email</label><input type="email" name="email" required autocomplete="email">
<label>Mật khẩu</label><input type="password" name="password" required autocomplete="current-password">
<button class="btn" type="submit">Đăng nhập</button><p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
</form>
<?php page_footer(); ?>
