<?php
require_once dirname(__DIR__) . '/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0); $action = $_POST['action'] ?? ''; $note = trim((string)($_POST['admin_note'] ?? ''));
    if (!in_array($action, ['approve','reject'], true)) { flash('error','Hành động không hợp lệ.'); redirect('submissions.php'); }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT s.*, j.reward FROM submissions s JOIN jobs j ON j.id=s.job_id WHERE s.id=? FOR UPDATE");
        $stmt->execute([$id]); $sub = $stmt->fetch();
        if (!$sub || $sub['status'] !== 'pending') throw new RuntimeException('Bài không tồn tại hoặc đã được xử lý.');
        if ($action === 'approve') {
            $up = $pdo->prepare("UPDATE submissions SET status='approved',admin_note=?,reviewed_by=?,reviewed_at=NOW() WHERE id=? AND status='pending'");
            $up->execute([$note,$_SESSION['user_id'],$id]);
            $credit = $pdo->prepare('UPDATE users SET balance=balance+? WHERE id=?');
            $credit->execute([$sub['reward'],$sub['user_id']]);
            $ledger = $pdo->prepare("INSERT INTO transactions(user_id,submission_id,type,amount,note,created_by) VALUES(?,?,'credit',?,?,?)");
            $ledger->execute([$sub['user_id'],$id,$sub['reward'],'Thưởng job #'.$sub['job_id'],$_SESSION['user_id']]);
            flash('success','Đã duyệt bài và cộng thưởng vào số dư.');
        } else {
            $up = $pdo->prepare("UPDATE submissions SET status='rejected',admin_note=?,reviewed_by=?,reviewed_at=NOW() WHERE id=? AND status='pending'");
            $up->execute([$note,$_SESSION['user_id'],$id]);
            flash('success','Đã từ chối bài gửi.');
        }
        $pdo->commit();
    } catch (RuntimeException $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack(); flash('error',$ex->getMessage());
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack(); flash('error','Không thể xử lý bài gửi.');
    }
    redirect('submissions.php');
}
$rows = $pdo->query("SELECT s.*,u.name,u.email,j.title,j.reward FROM submissions s JOIN users u ON u.id=s.user_id JOIN jobs j ON j.id=s.job_id ORDER BY (s.status='pending') DESC,s.created_at DESC")->fetchAll();
$labels = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
page_header('Duyệt minh chứng');
?>
<h1>Duyệt minh chứng</h1><p class="muted">Khi duyệt, hệ thống cộng thưởng và ghi lịch sử giao dịch trong cùng một giao dịch cơ sở dữ liệu.</p>
<?php foreach ($rows as $r): ?>
<div class="card" style="margin-bottom:16px">
<div class="section-head"><div><h2><?= e($r['title']) ?></h2><span class="muted"><?= e($r['name']) ?> · <?= e($r['email']) ?></span></div><div><span class="pill"><?= e($labels[$r['status']] ?? $r['status']) ?></span><div class="reward"><?= money($r['reward']) ?></div></div></div>
<?php if ($r['proof_text']): ?><p><?= nl2br(e($r['proof_text'])) ?></p><?php endif; ?>
<?php if ($r['proof_image']): ?><a href="../<?= e($r['proof_image']) ?>" target="_blank" rel="noopener"><img class="proof" src="../<?= e($r['proof_image']) ?>" alt="Minh chứng"></a><?php endif; ?>
<?php if ($r['status']==='pending'): ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<label>Ghi chú cho người dùng (không bắt buộc)</label><input name="admin_note" maxlength="500" value="">
<div class="actions"><button class="btn" name="action" value="approve" type="submit">Duyệt và cộng tiền</button><button class="btn danger" name="action" value="reject" type="submit">Từ chối</button></div></form>
<?php else: ?><p class="muted">Phản hồi: <?= e($r['admin_note'] ?? '') ?> · <?= e($r['reviewed_at'] ?? '') ?></p><?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (!$rows): ?><div class="card">Chưa có bài gửi nào.</div><?php endif; ?>
<?php page_footer(); ?>
