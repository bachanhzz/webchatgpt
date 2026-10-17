<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function take_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Phiên làm việc không hợp lệ. Hãy tải lại trang và thử lại.');
    }
}
function current_user(): ?array {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare('SELECT id, name, email, role, balance, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}
function require_login(): array {
    $user = current_user();
    if (!$user) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('login.php');
    }
    return $user;
}
function require_admin(): array {
    $user = require_login();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Bạn không có quyền truy cập trang này.');
    }
    return $user;
}
function money($amount): string {
    return number_format((float)$amount, 0, ',', '.') . ' đ';
}
function page_header(string $title): void {
    $user = current_user();
    $flash = take_flash();
    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' · JobViet</title><link rel="stylesheet" href="assets/style.css"></head><body>';
    echo '<header class="topbar"><a class="brand" href="index.php">Job<span>Viet</span></a><nav>';
    echo '<a href="index.php">Danh sách job</a>';
    if ($user) {
        echo '<a href="dashboard.php">Tài khoản</a><a href="my_submissions.php">Bài đã gửi</a><a href="wallet.php">Ví tiền</a>';
        if ($user['role'] === 'admin') echo '<a href="admin/index.php">Quản trị</a>';
        echo '<a href="logout.php">Đăng xuất</a>';
    } else {
        echo '<a href="login.php">Đăng nhập</a><a class="nav-cta" href="register.php">Tạo tài khoản</a>';
    }
    echo '</nav></header><main class="container">';
    if ($flash) echo '<div class="alert ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}
function page_footer(): void {
    echo '</main><footer>JobViet · Nền tảng nhiệm vụ trực tuyến demo. Giao dịch và rút tiền cần được quản trị viên xác minh.</footer></body></html>';
}
function upload_proof(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Tải ảnh lên thất bại.');
    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) throw new RuntimeException('Ảnh tối đa 5 MB.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) throw new RuntimeException('Không thể tạo thư mục uploads.');
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) throw new RuntimeException('Không lưu được ảnh.');
    return 'uploads/' . $name;
}
