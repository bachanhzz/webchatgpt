<?php
require_once dirname(__DIR__) . '/functions.php';
$admin = require_admin();
$jobs = (int)$pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$pendingSubs = (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE status='pending'")->fetchColumn();
$pendingWithdrawals = (int)$pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn();
$users = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
page_header('Quản trị');
?>
<h1>Bảng điều khiển quản trị</h1><p class="muted">Xin chào <?= e($admin['name']) ?>. Hãy kiểm tra minh chứng trước khi duyệt thưởng.</p>
<div class="stats">
<div class="stat"><span class="muted">Tài khoản</span><strong><?= $users ?></strong></div><div class="stat"><span class="muted">Tổng job</span><strong><?= $jobs ?></strong></div>
<div class="stat"><span class="muted">Bài chờ duyệt</span><strong><?= $pendingSubs ?></strong></div><div class="stat"><span class="muted">Lệnh rút chờ xử lý</span><strong><?= $pendingWithdrawals ?></strong></div>
</div>
<div class="grid">
<div class="card"><h2>Quản lý job</h2><p>Đăng nhiệm vụ, đặt mức thưởng và đóng/mở job.</p><a class="btn" href="jobs.php">Quản lý job</a></div>
<div class="card"><h2>Duyệt minh chứng</h2><p>Xem văn bản, ảnh và duyệt hoặc từ chối bài gửi.</p><a class="btn" href="submissions.php">Duyệt bài</a></div>
<div class="card"><h2>Yêu cầu rút tiền</h2><p>Kiểm tra yêu cầu và đánh dấu đã thanh toán sau khi chuyển tiền thực tế.</p><a class="btn" href="withdrawals.php">Xử lý rút tiền</a></div>
</div>
<?php page_footer(); ?>
