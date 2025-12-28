<?php
require_once '../core/Session.php';
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Security.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

Security::initCSRF();
$db = Database::getInstance();
$success = '';
$error = '';

// Handle Settings Update (Admin Display Name etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_general'])) {
    Security::checkCSRF();
    $stmt = $db->prepare("UPDATE admins SET display_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$_POST['display_name'], $_POST['email'], $_SESSION['admin_id']]);
    $_SESSION['admin_username'] = $_POST['display_name'];
    $success = 'Genel ayarlar güncellendi!';
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    Security::checkCSRF();
    $stmt = $db->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (password_verify($_POST['current_password'], $admin['password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['admin_id']]);
            $success = 'Şifre başarıyla değiştirildi!';
        } else {
            $error = 'Yeni şifreler uyuşmuyor!';
        }
    } else {
        $error = 'Mevcut şifre yanlış!';
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get current admin data
$stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

$pageTitle = 'Ayarlar - Admin';
require_once 'includes/header.php';
?>

<h1>⚙️ Ayarlar</h1>
<p style="color: #94a3b8; margin-bottom: 24px;">Hesap ve sistem yapılandırması.</p>

<?php if ($success): ?>
    <div class="alert-success"
        style="background: rgba(16,185,129,0.1); color:#10b981; border:1px solid #10b981; padding:15px; border-radius:10px; margin-bottom:20px;">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div
        style="background: rgba(239,68,68,0.1); color:#ef4444; border:1px solid #ef4444; padding:15px; border-radius:10px; margin-bottom:20px;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div style="display: grid; gap: 20px;">
    <!-- Genel Profil Ayarları -->
    <div class="card">
        <h3>👤 Genel Bilgiler</h3>
        <form method="POST">
            <?php echo Security::csrfInput(); ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Görünen Ad</label>
                    <input type="text" name="display_name"
                        value="<?php echo Security::e($user['display_name'] ?? $user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label>E-posta Adresi</label>
                    <input type="email" name="email" value="<?php echo Security::e($user['email'] ?? ''); ?>" required>
                </div>
            </div>
            <button type="submit" name="update_general" class="btn btn-primary" style="width: 100%;">Değişiklikleri
                Kaydet</button>
        </form>
    </div>

    <!-- Güvenlik Ayarları -->
    <div class="card">
        <h3>🔒 Güvenlik & Şifre</h3>
        <form method="POST">
            <?php echo Security::csrfInput(); ?>
            <label>Mevcut Şifre</label>
            <input type="password" name="current_password" required>
            <div class="form-row">
                <div class="form-group">
                    <label>Yeni Şifre</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Yeni Şifre (Onay)</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary"
                style="width: 100%; background: #2563eb;">Şifreyi Güncelle</button>
        </form>
    </div>

    <!-- Sistem Durumu -->
    <div class="card" style="border-color: rgba(255,255,255,0.05); background: #0d1127;">
        <h3 style="color: #94a3b8;"><i class="fas fa-info-circle"></i> Sistem Bilgisi</h3>
        <div style="font-size: 13px; color: #64748b;">
            <p style="margin-bottom: 8px;">Son Giriş: <span
                    style="color: #e4e4e7;"><?php echo date('d.m.Y H:i'); ?></span></p>
            <p style="margin-bottom: 8px;">IP Adresi: <span
                    style="color: #e4e4e7;"><?php echo $_SERVER['REMOTE_ADDR']; ?></span></p>
            <p>Versiyon: <span style="color: #3b82f6;">v2.0 Stable Build</span></p>
        </div>
    </div>

    <!-- Çıkış -->
    <div style="text-align: center; margin-top: 10px;">
        <a href="?logout=1" class="btn btn-danger"
            style="width: 100%; text-decoration: none; justify-content: center; background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2);">
            <i class="fas fa-sign-out-alt"></i> Güvenli Çıkış Yap
        </a>
    </div>
</div>

<?php require_once 'includes/navbar.php';
require_once 'includes/footer.php'; ?>