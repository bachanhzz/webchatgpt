<?php
require_once __DIR__ . '/functions.php';
if (current_user()) redirect('dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        flash('error', 'Hãy nhập tên hợp lệ, email hợp lệ và mật khẩu ít nhất 8 ký tự.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            flash('success', 'Tạo tài khoản thành công. Chào mừng bạn!');
            redirect('dashboard.php');
        } catch (PDOException $ex) {
            flash('error', 'Email này đã được sử dụng hoặc không thể tạo tài khoản.');
        }
    }
}
page_header('Tạo tài khoản');
?>
<form class="form-card" method="post">
<h1>Tạo tài khoản</h1><p class="muted">Đăng ký để nhận nhiệm vụ và quản lý số dư.</p>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Họ và tên</label><input name="name" required maxlength="100" autocomplete="name">
<label>Email</label><input type="email" name="email" required maxlength="190" autocomplete="email">
<label>Mật khẩu (ít nhất 8 ký tự)</label><input type="password" name="password" required minlength="8" autocomplete="new-password">
<button class="btn" type="submit">Đăng ký</button><p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</form>
<?php page_footer(); ?>
