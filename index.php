<?php
require_once __DIR__ . '/functions.php';
$stmt = $pdo->query("SELECT id, title, description, reward, slots, created_at FROM jobs WHERE status='active' ORDER BY created_at DESC");
$jobs = $stmt->fetchAll();
page_header('Việc làm trực tuyến');
?>
<section class="hero">
  <h1>Làm nhiệm vụ. Gửi minh chứng.<br>Nhận thưởng minh bạch.</h1>
  <p>Tìm các nhiệm vụ đang mở, gửi bài để quản trị viên xét duyệt và theo dõi số dư ngay trong tài khoản.</p>
  <?php if (!current_user()): ?><p><a class="btn" href="register.php">Bắt đầu ngay</a></p><?php endif; ?>
</section>
<div class="section-head"><div><h1>Job đang mở</h1><div class="muted">Đọc kỹ yêu cầu trước khi gửi bài.</div></div><span class="pill"><?= count($jobs) ?> nhiệm vụ</span></div>
<?php if (!$jobs): ?>
  <div class="card"><h3>Chưa có job nào</h3><p class="muted">Quản trị viên sẽ đăng nhiệm vụ tại đây khi có công việc mới.</p></div>
<?php else: ?>
<div class="grid">
<?php foreach ($jobs as $job): ?>
  <article class="card">
    <span class="pill">Đang nhận bài</span><h2 style="margin-top:12px"><?= e($job['title']) ?></h2>
    <p class="muted"><?= e(mb_strimwidth($job['description'], 0, 145, '…')) ?></p>
    <div class="reward"><?= money($job['reward']) ?> <small class="muted">/ bài được duyệt</small></div>
    <p class="muted">Số lượt tối đa: <?= (int)$job['slots'] ?></p>
    <a class="btn" href="job.php?id=<?= (int)$job['id'] ?>">Xem nhiệm vụ</a>
  </article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php page_footer(); ?>
