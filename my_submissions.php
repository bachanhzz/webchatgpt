<?php
require_once __DIR__ . '/functions.php';
$user = require_login();
$stmt = $pdo->prepare("SELECT s.*, j.title, j.reward FROM submissions s JOIN jobs j ON j.id=s.job_id WHERE s.user_id=? ORDER BY s.created_at DESC");
$stmt->execute([$user['id']]); $rows = $stmt->fetchAll();
$labels = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
page_header('Bài đã gửi');
?>
<h1>Bài đã gửi</h1><p class="muted">Tiền thưởng chỉ được cộng một lần khi bài được duyệt.</p>
<div class="table-wrap"><table><thead><tr><th>Nhiệm vụ</th><th>Minh chứng</th><th>Trạng thái</th><th>Phản hồi</th><th>Ngày gửi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr>
<td><strong><?= e($r['title']) ?></strong><br><span class="reward"><?= money($r['reward']) ?></span></td>
<td><?= $r['proof_text'] ? nl2br(e($r['proof_text'])) : '' ?><?php if ($r['proof_image']): ?><a href="<?= e($r['proof_image']) ?>" target="_blank" rel="noopener"><img class="proof" src="<?= e($r['proof_image']) ?>" alt="Ảnh minh chứng"></a><?php endif; ?></td>
<td><span class="pill"><?= e($labels[$r['status']] ?? $r['status']) ?></span></td><td><?= e($r['admin_note'] ?? '') ?></td><td><?= e($r['created_at']) ?></td>
</tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5">Bạn chưa gửi bài nào.</td></tr><?php endif; ?>
</tbody></table></div>
<?php page_footer(); ?>
