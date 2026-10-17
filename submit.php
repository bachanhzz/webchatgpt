<?php
require_once __DIR__ . '/functions.php';
$user = require_login();
$jobId = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 0);
$stmt = $pdo->prepare("SELECT id,title,reward FROM jobs WHERE id=? AND status='active'");
$stmt->execute([$jobId]); $job = $stmt->fetch();
if (!$job) { flash('error', 'Job không tồn tại hoặc đã đóng.'); redirect('index.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $text = trim((string)($_POST['proof_text'] ?? ''));
    try {
        if ($text === '' && empty($_FILES['proof_image']['name'])) throw new RuntimeException('Hãy gửi văn bản hoặc tải lên ảnh minh chứng.');
        if (mb_strlen($text) > 10000) throw new RuntimeException('Văn bản tối đa 10.000 ký tự.');
        $image = upload_proof($_FILES['proof_image'] ?? []);
        $stmt = $pdo->prepare("INSERT INTO submissions(user_id,job_id,proof_text,proof_image,status) VALUES(?,?,?,?, 'pending')");
        $stmt->execute([$user['id'], $job['id'], $text ?: null, $image]);
        flash('success', 'Đã gửi minh chứng. Số dư chỉ được cộng sau khi admin duyệt.');
        redirect('my_submissions.php');
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    } catch (PDOException $ex) {
        flash('error', 'Không thể gửi bài. Có thể bạn đã gửi nhiệm vụ này trước đó.');
    }
}
page_header('Gửi minh chứng');
?>
<form class="form-card" method="post" enctype="multipart/form-data">
<h1>Gửi minh chứng</h1><p><strong><?= e($job['title']) ?></strong></p><p class="reward"><?= money($job['reward']) ?></p>
<p class="muted">Gửi văn bản, ảnh, hoặc cả hai. Chỉ tải lên minh chứng liên quan đến nhiệm vụ và không chứa thông tin nhạy cảm.</p>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>">
<label>Văn bản / đường dẫn minh chứng (không bắt buộc nếu có ảnh)</label><textarea name="proof_text" maxlength="10000" placeholder="Mô tả bạn đã hoàn thành nhiệm vụ như thế nào..."></textarea>
<label>Ảnh minh chứng (JPG, PNG, WEBP; tối đa 5 MB)</label><input type="file" name="proof_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
<button class="btn" type="submit">Gửi để duyệt</button><a class="btn secondary" href="job.php?id=<?= (int)$job['id'] ?>">Quay lại</a>
</form>
<?php page_footer(); ?>
