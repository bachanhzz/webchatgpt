<?php
require_once __DIR__ . '/functions.php';
$user = require_login();
$stmt = $pdo->prepare("SELECT status, COUNT(*) total FROM submissions WHERE user_id=? GROUP BY status");
$stmt->execute([$user['id']]); $counts = ['pending'=>0,'approved'=>0,'rejected'=>0];
foreach ($stmt->fetchAll() as $r) $counts[$r['status']] = (int)$r['total'];
page_header('Tài khoản của tôi');
?>
<h1>Xin chào, <?= e($user['name']) ?>!</h1><p class="muted">Theo dõi nhiệm vụ và thu nhập của bạn tại đây.</p>
<div class="stats">
<div class="stat"><span class="muted">Số dư hiện tại</span><strong><?= money($user['balance']) ?></strong><a href="wallet.php">Quản lý ví →</a></div>
<div class="stat"><span class="muted">Bài chờ duyệt</span><strong><?= $counts['pending'] ?></strong></div>
<div class="stat"><span class="muted">Bài được duyệt</span><strong><?= $counts['approved'] ?></strong></div>
<div class="stat"><span class="muted">Bài bị từ chối</span><strong><?= $counts['rejected'] ?></strong></div>
</div>
<div class="grid">
<div class="card"><h3>Tìm nhiệm vụ</h3><p class="muted">Xem job đang mở và yêu cầu hoàn thành.</p><a class="btn" href="index.php">Xem job</a></div>
<div class="card"><h3>Bài đã gửi</h3><p class="muted">Kiểm tra trạng thái và phản hồi từ quản trị viên.</p><a class="btn secondary" href="my_submissions.php">Xem bài gửi</a></div>
<div class="card"><h3>Rút tiền</h3><p class="muted">Tạo yêu cầu rút tiền khi đủ số dư.</p><a class="btn secondary" href="wallet.php">Mở ví tiền</a></div>
</div>
<?php page_footer(); ?>
