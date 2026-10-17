<?php
require_once __DIR__ . '/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id=? AND status='active'");
$stmt->execute([$id ?: 0]); $job = $stmt->fetch();
if (!$job) { http_response_code(404); page_header('Không tìm thấy job'); echo '<div class="card"><h2>Job không tồn tại hoặc đã đóng.</h2><a href="index.php">Quay lại</a></div>'; page_footer(); exit; }
$user = current_user();
page_header($job['title']);
?>
<div class="card">
<span class="pill">Nhiệm vụ đang mở</span><h1 style="margin-top:12px"><?= e($job['title']) ?></h1>
<div class="reward"><?= money($job['reward']) ?> <small class="muted">nếu được duyệt</small></div>
<h3>Mô tả và yêu cầu</h3><p><?= nl2br(e($job['description'])) ?></p>
<p class="muted">Ngày đăng: <?= e($job['created_at']) ?></p>
<?php if ($user): ?>
<a class="btn" href="submit.php?job_id=<?= (int)$job['id'] ?>">Gửi minh chứng</a>
<?php else: ?>
<a class="btn" href="login.php">Đăng nhập để làm job</a>
<?php endif; ?>
</div>
<?php page_footer(); ?>
