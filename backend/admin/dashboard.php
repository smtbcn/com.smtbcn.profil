<?php
require_once '../core/Session.php';
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Security.php';

// Check login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

Security::initCSRF();
$db = Database::getInstance();
$success = '';

// Get current data
$stmt = $db->query("SELECT * FROM profile_status LIMIT 1");
$current = $stmt->fetch() ?: [
    'status_key' => 'offline',
    'activity_tr' => '',
    'activity_en' => '',
    'about_tr' => '',
    'about_en' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    Security::checkCSRF();

    $stmt = $db->prepare("UPDATE profile_status SET status_key = ?, activity_tr = ?, activity_en = ?, about_tr = ?, about_en = ? WHERE id = 1");
    $stmt->execute([
        $_POST['status_key'],
        $_POST['activity_tr'],
        $_POST['activity_en'],
        $_POST['about_tr'],
        $_POST['about_en']
    ]);

    $success = 'Profil başarıyla güncellendi!';
    header('Location: dashboard.php?success=1');
    exit;
}

if (isset($_GET['success'])) {
    $success = 'Profil başarıyla güncellendi!';
}

$pageTitle = 'Ana Sayfa - Admin';
require_once 'includes/header.php';
?>

<h1>👋 Merhaba, Admin</h1>
<p style="color: #94a3b8; margin-bottom: 24px;">Profilinizi buradan yönetebilirsiniz.</p>

<?php if ($success): ?>
    <div class="alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<form method="POST">
    <?php echo Security::csrfInput(); ?>

    <div class="card">
        <h3>📡 Canlı Durum</h3>

        <label>Durum</label>
        <select name="status_key">
            <option value="online" <?php echo $current['status_key'] == 'online' ? 'selected' : ''; ?>>🟢 Online</option>
            <option value="busy" <?php echo $current['status_key'] == 'busy' ? 'selected' : ''; ?>>🟠 Meşgul</option>
            <option value="coding" <?php echo $current['status_key'] == 'coding' ? 'selected' : ''; ?>>🔵 Kod Yazıyor
            </option>
            <option value="offline" <?php echo $current['status_key'] == 'offline' ? 'selected' : ''; ?>>⚫ Çevrimdışı
            </option>
        </select>

        <label>Aktivite (TR)</label>
        <input type="text" name="activity_tr" value="<?php echo Security::e($current['activity_tr']); ?>"
            placeholder="Örn: Kod yazıyor...">

        <label>Aktivite (EN)</label>
        <input type="text" name="activity_en" value="<?php echo Security::e($current['activity_en']); ?>"
            placeholder="Ex: Coding...">
    </div>

    <div class="card">
        <h3>📝 Hakkımda (TR)</h3>
        <textarea name="about_tr" rows="5"><?php echo Security::e($current['about_tr']); ?></textarea>
    </div>

    <div class="card">
        <h3>📝 Hakkımda (EN)</h3>
        <textarea name="about_en" rows="5"><?php echo Security::e($current['about_en']); ?></textarea>
    </div>

    <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">
        <i class="fas fa-save"></i> Kaydet
    </button>
</form>

<?php
require_once 'includes/navbar.php';
require_once 'includes/footer.php';
?>