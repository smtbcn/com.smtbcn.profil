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

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_app'])) {
    Security::checkCSRF();

    if (isset($_POST['id']) && $_POST['id']) {
        $stmt = $db->prepare("UPDATE applications SET app_key = ?, name_tr = ?, name_en = ?, desc_tr = ?, desc_en = ?, icon = ?, color = ?, url = ? WHERE id = ?");
        $stmt->execute([
            $_POST['app_key'],
            $_POST['name_tr'],
            $_POST['name_en'],
            $_POST['desc_tr'],
            $_POST['desc_en'],
            $_POST['icon'],
            $_POST['color'],
            $_POST['url'],
            $_POST['id']
        ]);
    } else {
        $stmt = $db->prepare("INSERT INTO applications (app_key, name_tr, name_en, desc_tr, desc_en, icon, color, url, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $maxOrder = $db->query("SELECT MAX(sort_order) as max FROM applications")->fetch()['max'] ?? 0;
        $stmt->execute([
            $_POST['app_key'],
            $_POST['name_tr'],
            $_POST['name_en'],
            $_POST['desc_tr'],
            $_POST['desc_en'],
            $_POST['icon'],
            $_POST['color'],
            $_POST['url'],
            $maxOrder + 1
        ]);
    }
    $success = 'Uygulama başarıyla kaydedildi!';
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: apps.php');
    exit;
}

$apps = $db->query("SELECT * FROM applications ORDER BY sort_order ASC")->fetchAll();
$pageTitle = 'Uygulamalar - Admin';
require_once 'includes/header.php';
?>

<h1>📱 Uygulamalar</h1>
<p style="color: #94a3b8; margin-bottom: 24px;">Uzak sunuculardan (Store) çekilen resimlerle yönetin.</p>

<?php if ($success): ?>
    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<button onclick="openModal()" class="btn btn-success" style="margin-bottom: 20px;">
    <i class="fas fa-plus"></i> Yeni Uygulama
</button>

<div style="display: grid; gap: 16px;">
    <?php foreach ($apps as $app): ?>
        <div class="card" style="padding: 16px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div
                    style="width: 60px; height: 60px; background: <?php echo $app['color'] ?: '#1a1f3a'; ?>; border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <img src="<?php echo Security::e($app['icon']); ?>"
                        style="width: 100%; height: 100%; object-fit: cover;"
                        onerror="this.src='https://via.placeholder.com/60?text=App'">
                </div>
                <div style="flex: 1;">
                    <h3 style="margin: 0;"><?php echo Security::e($app['name_tr']); ?></h3>
                    <p
                        style="color: #94a3b8; margin: 4px 0; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 400px;">
                        <?php echo Security::e($app['icon']); ?></p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="editApp(<?php echo htmlspecialchars(json_encode($app)); ?>)"
                        class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                    <a href="?delete=<?php echo $app['id']; ?>" onclick="return confirm('Silinsin mi?')"
                        class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="appModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Uygulama Düzenle</h3><button onclick="closeModal()" class="close-modal">&times;</button>
        </div>
        <form method="POST">
            <?php echo Security::csrfInput(); ?><input type="hidden" name="id" id="app_id">
            <label>Platform & Renk</label>
            <div class="form-row">
                <select name="app_key" id="app_key" style="flex: 2;">
                    <option value="android">Android</option>
                    <option value="apple">iOS</option>
                </select>
                <input type="color" name="color" id="color" style="flex: 1; height: 45px; padding: 2px;">
            </div>
            <label>Resim URL (Store Icon Link)</label>
            <input type="url" name="icon" id="icon" required placeholder="https://is1-ssl.mzstatic.com/...">
            <label>Uygulama Adı (TR)</label>
            <input type="text" name="name_tr" id="name_tr" required>
            <label>Mağaza URL</label>
            <input type="url" name="url" id="url" required>
            <button type="submit" name="save_app" class="btn btn-primary"
                style="width: 100%; margin-top: 10px;">Kaydet</button>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('appModal').classList.add('show'); }
    function closeModal() { document.getElementById('appModal').classList.remove('show'); }
    function editApp(app) {
        document.getElementById('app_id').value = app.id;
        document.getElementById('app_key').value = app.app_key;
        document.getElementById('icon').value = app.icon;
        document.getElementById('name_tr').value = app.name_tr;
        document.getElementById('color').value = app.color;
        document.getElementById('url').value = app.url;
        openModal();
    }
</script>
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: #151932;
        border-radius: 16px;
        padding: 24px;
        width: 90%;
        max-width: 500px;
    }
</style>

<?php require_once 'includes/navbar.php';
require_once 'includes/footer.php'; ?>