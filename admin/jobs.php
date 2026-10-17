<?php
require_once dirname(__DIR__) . '/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $reward = filter_var($_POST['reward'] ?? null, FILTER_VALIDATE_INT);
        $slots = filter_var($_POST['slots'] ?? null, FILTER_VALIDATE_INT);
        if (mb_strlen($title) < 3 || mb_strlen($title) > 180 || mb_strlen($description) < 10 || !$reward || $reward < 0 || $reward > 100000000 || !$slots || $slots < 1 || $slots > 1000000) {
            flash('error', 'Hãy kiểm tra tiêu đề, mô tả, tiền thưởng và số lượt.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO jobs(title,description,reward,slots,status,created_by) VALUES(?,?,?,?, 'active',?)");
            $stmt->execute([$title,$description,$reward,$slots,$_SESSION['user_id']]);
            flash('success', 'Đã đăng job mới.');
        }
        redirect('jobs.php');
    }
    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE jobs SET status=IF(status='active','closed','active') WHERE id=?");
        $stmt->execute([$id]); flash('success', 'Đã cập nhật trạng thái job.'); redirect('jobs.php');
    }
}
$jobs = $pdo->query("SELECT j.*, u.name AS admin_name FROM jobs j LEFT JOIN users u ON u.id=j.created_by ORDER BY j.created_at DESC")->fetchAll();
page_header('Quản lý job');
?>
<h1>Quản lý job</h1>
<form class="card" method="post">
<h2>Đăng nhiệm vụ mới</h2><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create">
<label>Tiêu đề</label><input name="title" maxlength="180" required>
<label>Mô tả / hướng dẫn chi tiết</label><textarea name="description" minlength="10" required></textarea>
<div class="grid"><div><label>Thưởng mỗi bài được duyệt (VNĐ)</label><input type="number" name="reward" min="0" max="100000000" step="1" required></div><div><label>Số lượt tối đa (ghi chú)</label><input type="number" name="slots" min="1" max="1000000" value="100" required></div></div>
<button class="btn" type="submit">Đăng job</button>
</form>
<div class="section-head"><h2>Job đã tạo</h2></div>
<div class="table-wrap"><table><thead><tr><th>Job</th><th>Thưởng</th><th>Trạng thái</th><th>Ngày tạo</th><th>Hành động</th></tr></thead><tbody>
<?php foreach ($jobs as $j): ?><tr><td><strong><?= e($j['title']) ?></strong><br><?= e(mb_strimwidth($j['description'],0,100,'…')) ?></td><td><?= money($j['reward']) ?></td><td><?= $j['status']==='active'?'Đang mở':'Đã đóng' ?></td><td><?= e($j['created_at']) ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$j['id'] ?>"><button class="btn secondary" type="submit"><?= $j['status']==='active'?'Đóng job':'Mở lại' ?></button></form></td></tr><?php endforeach; ?>
<?php if (!$jobs): ?><tr><td colspan="5">Chưa có job.</td></tr><?php endif; ?>
</tbody></table></div>
<?php page_footer(); ?>
