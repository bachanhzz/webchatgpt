<?php
require_once dirname(__DIR__) . '/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0); $action = $_POST['action'] ?? ''; $note = trim((string)($_POST['admin_note'] ?? ''));
    if (!in_array($action,['paid','rejected'],true)) { flash('error','Hành động không hợp lệ.'); redirect('withdrawals.php'); }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT * FROM withdrawals WHERE id=? FOR UPDATE'); $stmt->execute([$id]); $w = $stmt->fetch();
        if (!$w || $w['status'] !== 'pending') throw new RuntimeException('Yêu cầu không tồn tại hoặc đã được xử lý.');
        if ($action === 'paid') {
            // Chỉ đánh dấu đã trả sau khi admin đã chuyển tiền ngoài hệ thống.
            $up = $pdo->prepare("UPDATE withdrawals SET status='paid',admin_note=?,processed_by=?,processed_at=NOW() WHERE id=? AND status='pending'");
            $up->execute([$note,$_SESSION['user_id'],$id]);
            $ledger = $pdo->prepare("INSERT INTO transactions(user_id,type,amount,note,created_by) VALUES(?,'withdrawal',?,?,?)");
            $ledger->execute([$w['user_id'],-$w['amount'],'Rút tiền #'.$id,$_SESSION['user_id']]);
            // Balance là số dư chưa rút; trừ khi admin xác nhận đã thanh toán.
            $bal = $pdo->prepare('UPDATE users SET balance=balance-? WHERE id=? AND balance>=?');
            $bal->execute([$w['amount'],$w['user_id'],$w['amount']]);
            if ($bal->rowCount() !== 1) throw new RuntimeException('Số dư không đủ để hoàn tất rút tiền.');
            flash('success','Đã đánh dấu thanh toán và cập nhật số dư. Hãy chỉ dùng sau khi chuyển tiền thật.');
        } else {
            $up = $pdo->prepare("UPDATE withdrawals SET status='rejected',admin_note=?,processed_by=?,processed_at=NOW() WHERE id=? AND status='pending'");
            $up->execute([$note,$_SESSION['user_id'],$id]);
            flash('success','Đã từ chối yêu cầu rút tiền.');
        }
        $pdo->commit();
    } catch (RuntimeException $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack(); flash('error',$ex->getMessage());
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack(); flash('error','Không thể xử lý yêu cầu.');
    }
    redirect('withdrawals.php');
}
$rows = $pdo->query("SELECT w.*,u.name,u.email FROM withdrawals w JOIN users u ON u.id=w.user_id ORDER BY (w.status='pending') DESC,w.created_at DESC")->fetchAll();
$labels=['pending'=>'Chờ xử lý','paid'=>'Đã thanh toán','rejected'=>'Từ chối'];
page_header('Yêu cầu rút tiền');
?>
<h1>Yêu cầu rút tiền</h1><p class="muted">Xác minh thông tin. Chỉ bấm “Đã chuyển tiền” sau khi thực sự hoàn tất thanh toán bên ngoài website.</p>
<div class="table-wrap"><table><thead><tr><th>Người dùng</th><th>Số tiền</th><th>Thông tin nhận tiền</th><th>Trạng thái</th><th>Xử lý</th></tr></thead><tbody>
<?php foreach ($rows as $w): ?><tr><td><strong><?= e($w['name']) ?></strong><br><?= e($w['email']) ?><br><small><?= e($w['created_at']) ?></small></td><td><?= money($w['amount']) ?></td><td><?= $w['method']==='bank'?'Ngân hàng':'Ví điện tử' ?><br><?= nl2br(e($w['details'])) ?></td><td><?= e($labels[$w['status']] ?? $w['status']) ?><br><?= e($w['admin_note'] ?? '') ?></td><td>
<?php if ($w['status']==='pending'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$w['id'] ?>"><label>Ghi chú</label><input name="admin_note" maxlength="500"><div class="actions"><button class="btn" name="action" value="paid">Đã chuyển tiền</button><button class="btn danger" name="action" value="rejected">Từ chối</button></div></form><?php else: ?><?= e($w['processed_at'] ?? '') ?><?php endif; ?>
</td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5">Chưa có yêu cầu.</td></tr><?php endif; ?>
</tbody></table></div>
<?php page_footer(); ?>
