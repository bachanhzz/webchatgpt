<?php
require_once __DIR__ . '/functions.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_INT);
    $method = trim((string)($_POST['method'] ?? ''));
    $details = trim((string)($_POST['details'] ?? ''));
    if (!$amount || $amount < 10000 || $amount > 50000000) {
        flash('error', 'Số tiền rút tối thiểu 10.000 đ và tối đa 50.000.000 đ mỗi yêu cầu.');
    } elseif (!in_array($method, ['bank','ewallet'], true) || mb_strlen($details) < 5 || mb_strlen($details) > 500) {
        flash('error', 'Hãy chọn phương thức và nhập thông tin nhận tiền hợp lệ.');
    } else {
        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT balance FROM users WHERE id=? FOR UPDATE');
            $lock->execute([$user['id']]); $balance = (float)$lock->fetchColumn();
            $pending = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM withdrawals WHERE user_id=? AND status='pending'");
            $pending->execute([$user['id']]); $reserved = (float)$pending->fetchColumn();
            if ($amount > $balance - $reserved) throw new RuntimeException('Số dư khả dụng không đủ cho yêu cầu này.');
            $ins = $pdo->prepare("INSERT INTO withdrawals(user_id,amount,method,details,status) VALUES(?,?,?,?, 'pending')");
            $ins->execute([$user['id'], $amount, $method, $details]);
            $pdo->commit();
            flash('success', 'Đã tạo yêu cầu rút tiền. Admin sẽ kiểm tra trước khi xử lý.');
            redirect('wallet.php');
        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', $ex->getMessage());
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', 'Không thể tạo yêu cầu rút tiền lúc này.');
        }
    }
}
$user = current_user();
$stmt = $pdo->prepare('SELECT * FROM withdrawals WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$user['id']]); $withdrawals = $stmt->fetchAll();
$labels = ['pending'=>'Chờ xử lý','paid'=>'Đã thanh toán','rejected'=>'Từ chối'];
page_header('Ví tiền');
?>
<h1>Ví tiền</h1><div class="stats"><div class="stat"><span class="muted">Số dư</span><strong><?= money($user['balance']) ?></strong></div></div>
<div class="grid">
<form class="card" method="post">
<h2>Tạo yêu cầu rút tiền</h2><p class="muted">Yêu cầu rút tiền sẽ chờ admin xử lý. Không nhập mật khẩu, mã OTP hoặc thông tin thẻ.</p>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Số tiền (VNĐ)</label><input type="number" name="amount" min="10000" max="50000000" step="1000" required>
<label>Phương thức nhận</label><select name="method" required><option value="bank">Chuyển khoản ngân hàng</option><option value="ewallet">Ví điện tử</option></select>
<label>Thông tin nhận tiền</label><textarea name="details" required maxlength="500" placeholder="Tên chủ tài khoản, ngân hàng, số tài khoản hoặc thông tin ví..."></textarea>
<button class="btn" type="submit">Gửi yêu cầu</button>
</form>
<div class="card"><h2>Lưu ý</h2><ul><li>Yêu cầu sẽ được admin kiểm tra thủ công.</li><li>Thông tin nhận tiền chỉ cung cấp ở mức cần thiết.</li><li>Website demo chưa tích hợp cổng thanh toán tự động.</li></ul></div>
</div>
<div class="section-head"><h2>Lịch sử rút tiền</h2></div>
<div class="table-wrap"><table><thead><tr><th>Số tiền</th><th>Phương thức</th><th>Trạng thái</th><th>Ghi chú</th><th>Ngày tạo</th></tr></thead><tbody>
<?php foreach ($withdrawals as $w): ?><tr><td><?= money($w['amount']) ?></td><td><?= $w['method']==='bank'?'Ngân hàng':'Ví điện tử' ?></td><td><?= e($labels[$w['status']] ?? $w['status']) ?></td><td><?= e($w['admin_note'] ?? '') ?></td><td><?= e($w['created_at']) ?></td></tr><?php endforeach; ?>
<?php if (!$withdrawals): ?><tr><td colspan="5">Chưa có yêu cầu rút tiền.</td></tr><?php endif; ?>
</tbody></table></div>
<?php page_footer(); ?>
