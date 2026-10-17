# JobViet — website nhiệm vụ trực tuyến (PHP + MySQL)

## Tính năng
- Đăng ký, đăng nhập và đăng xuất.
- Danh sách job công khai; admin tạo job, đặt thưởng và đóng/mở job.
- Người dùng gửi minh chứng bằng văn bản và/hoặc ảnh JPG, PNG, WEBP (tối đa 5 MB).
- Admin duyệt/từ chối bài. Khi duyệt, hệ thống cộng tiền đúng một lần và ghi sổ giao dịch.
- Người dùng xem bài đã gửi, số dư và tạo yêu cầu rút tiền.
- Admin xử lý yêu cầu rút tiền. Trạng thái “Đã thanh toán” chỉ nên chọn sau khi đã chuyển tiền thực tế.

## Yêu cầu
- PHP 8.1+ (cần PDO MySQL, Fileinfo).
- MySQL 5.7+ hoặc MariaDB tương thích.
- Apache/Nginx hoặc môi trường PHP local.

## Cài đặt local bằng XAMPP
1. Giải nén thư mục `job_platform` vào `htdocs`.
2. Mở phpMyAdmin, import file `database.sql`.
3. Sửa `config.php` cho đúng host, tên database, username và password MySQL.
4. Đảm bảo PHP được phép tải file lên; thư mục `uploads` sẽ tự tạo khi gửi ảnh.
5. Mở `http://localhost/job_platform/`.
6. Tạo tài khoản người dùng đầu tiên qua `register.php`.
7. Trong phpMyAdmin, chạy SQL sau để cấp quyền admin (đổi email thành email vừa đăng ký):
   `UPDATE users SET role='admin' WHERE email='email-cua-ban@example.com';`
8. Đăng xuất rồi đăng nhập lại. Truy cập mục **Quản trị** để tạo job và duyệt bài.

## Bảo mật trước khi đưa lên hosting
- Đổi thông tin database; dùng HTTPS; đặt `display_errors=Off`.
- Hạn chế quyền ghi thư mục uploads, cấu hình web server không thực thi PHP trong uploads.
- Dùng mật khẩu admin mạnh, bật sao lưu database và giám sát hoạt động.
- Cân nhắc thêm giới hạn tần suất đăng nhập, xác minh email, nhật ký quản trị và chính sách quyền riêng tư.
- Không yêu cầu người dùng cung cấp mật khẩu, OTP hoặc thông tin đăng nhập của dịch vụ bên thứ ba.
- Đây là bản khởi đầu/demo, chưa tích hợp chuyển tiền tự động, KYC, chống gian lận hay xử lý tranh chấp. Cần kiểm thử bảo mật và pháp lý trước khi vận hành thật.

## Ghi chú logic
- Mỗi tài khoản chỉ gửi một bài cho mỗi job trong bản hiện tại.
- Số tiền thưởng được xác định từ job tại thời điểm admin duyệt.
- Yêu cầu rút đang chờ được tính là số dư đã giữ chỗ để tránh tạo yêu cầu vượt số dư.
- Dòng giao dịch là nhật ký tham khảo; mọi thay đổi số dư cần được kiểm toán.
# tundz
